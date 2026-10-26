<?php
session_start();
header("Content-Type: application/json");

function checkMaxLength(string $value, int $max, string $label): void
{
    if ($value !== '' && mb_strlen($value) > $max) {
        throw new Exception("$label must not be more than $max characters.");
    }
}

// Paths used to undo file changes if something fails
$savedPath = null;   // insert: the new file
$tempPath = null;    // update: new file under a temporary name
$finalPath = null;   // update: final path of the new file
$oldPath = null;     // update: path of the old file
$delPath = null;     // update: DEL_ path of the old file
$finalMoved = false;
$oldRenamed = false;

$cleanup = function () use (&$savedPath, &$tempPath, &$finalPath, &$oldPath, &$delPath, &$finalMoved, &$oldRenamed) {
    if ($savedPath && file_exists($savedPath)) unlink($savedPath);
    if ($tempPath && file_exists($tempPath)) unlink($tempPath);
    if ($finalMoved && $finalPath && file_exists($finalPath)) unlink($finalPath);
    if ($oldRenamed && $delPath && $oldPath && file_exists($delPath)) rename($delPath, $oldPath);
};

try {
    require_once __DIR__ . '/../../include/conn.php';

    // 1. Only logged-in admins
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
        throw new Exception("You are not allowed to do this.");
    }

    // 2. Only POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // 3. Read the values
    $id        = (int)($_POST['id'] ?? 0);   // 0 = new document, above 0 = update
    $isEdit    = $id > 0;
    $fileId    = (int)($_POST['file_id'] ?? 0);
    $docTypeId = (int)($_POST['doc_type_id'] ?? 0);
    $ruleName  = trim($_POST['rule_name'] ?? '');
    $docDate   = trim($_POST['doc_date'] ?? '');
    $remarks   = trim($_POST['remarks'] ?? '');
    $pdf       = $_FILES['doc_pdf'] ?? null;
    $hasNewPdf = $pdf && $pdf['error'] !== UPLOAD_ERR_NO_FILE;

    // Required checks (the file is not sent in edit mode: it cannot be changed)
    if (!$isEdit && $fileId <= 0) throw new Exception("Please choose a file.");
    if ($docTypeId <= 0)  throw new Exception("Please choose a document type.");
    if ($ruleName === '') throw new Exception("Rule name is required.");
    if ($docDate === '')  throw new Exception("Document date is required.");
    if ($remarks === '')  throw new Exception("Remarks is required.");

    // Length checks
    checkMaxLength($ruleName, 200, "Rule name");
    checkMaxLength($remarks, 500, "Remarks");

    // Date must be a real date in Y-m-d format
    $d = DateTime::createFromFormat('Y-m-d', $docDate);
    if (!$d || $d->format('Y-m-d') !== $docDate) {
        throw new Exception("Document date is not valid.");
    }
    if ($docDate > date('Y-m-d')) {
        throw new Exception("Future dates are not allowed.");
    }

    // The chosen file (insert only) and document type must exist
    if (!$isEdit) {
        $stmt = $conn->prepare("SELECT 1 FROM file_master WHERE id = :id");
        $stmt->execute([':id' => $fileId]);
        if (!$stmt->fetch()) throw new Exception("Selected file does not exist.");
    }

    $stmt = $conn->prepare("SELECT name FROM doc_type WHERE id = :id");
    $stmt->execute([':id' => $docTypeId]);
    $docTypeName = $stmt->fetchColumn();
    if ($docTypeName === false) throw new Exception("Selected document type does not exist.");

    // Update: the document must exist, and we need its current PDF
    $oldDbPath = '';
    if ($isEdit) {
        $stmt = $conn->prepare(
            "SELECT doc_pdf FROM file_document WHERE id = :id AND is_deleted = FALSE"
        );
        $stmt->execute([':id' => $id]);
        $oldDbPath = $stmt->fetchColumn();
        if ($oldDbPath === false) throw new Exception("Document not found.");
    }

    // PDF checks: required for a new document, optional when updating
    if (!$isEdit && !$hasNewPdf) {
        throw new Exception("Please upload a PDF file.");
    }
    if ($hasNewPdf) {
        if ($pdf['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a PDF file.");
        }
        if ($pdf['size'] > 5 * 1024 * 1024) {
            throw new Exception("PDF must not be larger than 5 MB.");
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($pdf['tmp_name']) !== 'application/pdf') {
            throw new Exception("Only PDF files are allowed.");
        }
        if (strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            throw new Exception("File must have a .pdf extension.");
        }
    }

    $uploadDir = __DIR__ . '/../../uploads/documents/';
    if ($hasNewPdf && (!is_dir($uploadDir) || !is_writable($uploadDir))) {
        throw new Exception("Upload folder is not available.");
    }

    $safeType = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $docTypeName), '_');

    $conn->beginTransaction();

    if (!$isEdit) {
        // ---------- INSERT ----------
        $stmt = $conn->prepare(
            "INSERT INTO file_document (file_id, doc_type_id, rule_name, doc_date, doc_pdf, remarks, created_at)
             VALUES (:file_id, :doc_type_id, :rule_name, :doc_date, '', :remarks, :created_at)
             RETURNING id"
        );
        $stmt->execute([
            ':file_id'     => $fileId,
            ':doc_type_id' => $docTypeId,
            ':rule_name'   => $ruleName,
            ':doc_date'    => $docDate,
            ':remarks'     => $remarks,
            ':created_at'  => date('Y-m-d H:i:s'),
        ]);
        $newId = $stmt->fetchColumn();

        $newName   = strtolower($safeType) . '_' . str_pad($newId, 5, '0', STR_PAD_LEFT) . '.pdf';
        $dbPath    = 'uploads/documents/' . $newName;
        $savedPath = $uploadDir . $newName;

        if (!move_uploaded_file($pdf['tmp_name'], $savedPath)) {
            throw new Exception("Could not save the PDF file.");
        }

        $stmt = $conn->prepare("UPDATE file_document SET doc_pdf = :pdf WHERE id = :id");
        $stmt->execute([':pdf' => $dbPath, ':id' => $newId]);

        $conn->commit();
        echo json_encode(["status" => "Success", "message" => "Document added successfully.", "mode" => "insert"]);
    } else {
        // ---------- UPDATE ----------
        $stmt = $conn->prepare(
            "UPDATE file_document
             SET doc_type_id = :doc_type_id, rule_name = :rule_name, doc_date = :doc_date,
                 remarks = :remarks, modified_at = :modified_at
             WHERE id = :id AND is_deleted = FALSE"
        );
        $stmt->execute([
            ':doc_type_id' => $docTypeId,
            ':rule_name'   => $ruleName,
            ':doc_date'    => $docDate,
            ':remarks'     => $remarks,
            ':modified_at' => date('Y-m-d H:i:s'),
            ':id'          => $id,
        ]);

        if ($hasNewPdf) {
            $newName   = strtolower($safeType) . '_' . str_pad($id, 5, '0', STR_PAD_LEFT) . '.pdf';
            $dbPath    = 'uploads/documents/' . $newName;
            $finalPath = $uploadDir . $newName;
            $tempPath  = $uploadDir . 'tmp_' . uniqid() . '.pdf';

            // 1. Save the new PDF under a temporary name
            if (!move_uploaded_file($pdf['tmp_name'], $tempPath)) {
                throw new Exception("Could not save the PDF file.");
            }

            // 2. Mark the old PDF: DEL_rules_00001_20261016_153012.pdf
            if ($oldDbPath !== '') {
                $oldPath = $uploadDir . basename($oldDbPath);
                if (is_file($oldPath)) {
                    $delPath = $uploadDir . 'DEL_' . pathinfo($oldPath, PATHINFO_FILENAME)
                        . '_' . date('Ymd_His') . '.pdf';
                    if (!rename($oldPath, $delPath)) {
                        throw new Exception("Could not replace the old PDF file.");
                    }
                    $oldRenamed = true;
                }
            }

            // 3. Give the new PDF its final name
            if (!rename($tempPath, $finalPath)) {
                throw new Exception("Could not save the PDF file.");
            }
            $finalMoved = true;
            $tempPath = null;

            // 4. Save the new path in the database
            $stmt = $conn->prepare("UPDATE file_document SET doc_pdf = :pdf WHERE id = :id");
            $stmt->execute([':pdf' => $dbPath, ':id' => $id]);
        }

        $conn->commit();
        echo json_encode(["status" => "Success", "message" => "Document updated successfully.", "mode" => "update"]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    $cleanup();
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    $cleanup();
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}