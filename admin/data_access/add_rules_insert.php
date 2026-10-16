<?php
session_start();
header("Content-Type: application/json");
function checkMaxLength(string $value, int $max, string $label): void
{
    if ($value !== '' && mb_strlen($value) > $max) {
        throw new Exception("$label must not be more than $max characters.");
    }
}

$savedPath = null;
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
    $fileId    = (int)($_POST['file_id'] ?? 0);
    $docTypeId = (int)($_POST['doc_type_id'] ?? 0);
    $ruleName  = trim($_POST['rule_name'] ?? '');
    $docDate  = trim($_POST['doc_date'] ?? '');
    $remarks   = trim($_POST['remarks'] ?? '');
    $pdf       = $_FILES['rule_pdf'] ?? null;

    // Required checks
    if ($fileId <= 0)    throw new Exception("Please choose a file.");
    if ($docTypeId <= 0) throw new Exception("Please choose a document type.");
    if ($ruleName === '') throw new Exception("Rule name is required.");
    if ($docDate === '') throw new Exception("Document date is required.");
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

    // The chosen file and document type must exist in the database
    $stmt = $conn->prepare("SELECT 1 FROM file_master WHERE id = :id");
    $stmt->execute([':id' => $fileId]);
    if (!$stmt->fetch()) throw new Exception("Selected file does not exist.");

    $stmt = $conn->prepare("SELECT name FROM doc_type WHERE id = :id");
    $stmt->execute([':id' => $docTypeId]);
    $docTypeName = $stmt->fetchColumn();
    if ($docTypeName === false) throw new Exception("Selected document type does not exist.");



    if (!$pdf || $pdf['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Please upload a PDF file.");
    }

    if ($pdf['size'] > 5 * 1024 * 1024) {
        throw new Exception("PDF must not be larger than 5 MB.");
    }

    // Real type, read from the file content
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($pdf['tmp_name']) !== 'application/pdf') {
        throw new Exception("Only PDF files are allowed.");
    }

    if (strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        throw new Exception("File must have a .pdf extension.");
    }

    $uploadDir = __DIR__ . '/../../uploads/documents/';
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        throw new Exception("Upload folder is not available.");
    }

    $conn->beginTransaction();

    // Insert with an empty pdf name for now, and get the new id back
    $stmt = $conn->prepare(
        "INSERT INTO file_document (file_id, doc_type_id, rule_name, doc_date, rule_pdf, remarks, created_at)
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

    // Build the name: Notification_001.pdf
    $safeType = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $docTypeName), '_');
    // $newName  = strtolower($safeType) . '_00' . $newId . '.pdf';
    $newName  = strtolower($safeType) . '_' . str_pad($newId, 5, '0', STR_PAD_LEFT) . '.pdf';
    $dbPath   = 'uploads/documents/' . $newName;      // what goes in the column
    $savedPath = $uploadDir . $newName;     // where the file really goes

    if (!move_uploaded_file($pdf['tmp_name'], $savedPath)) {
        throw new Exception("Could not save the PDF file.");
    }

    $stmt = $conn->prepare("UPDATE file_document SET rule_pdf = :pdf WHERE id = :id");
    $stmt->execute([':pdf' => $dbPath, ':id' => $newId]);

    $conn->commit();

    echo json_encode(["status" => "Success", "message" => "Rule added successfully."]);
} catch (PDOException $e) {
    error_log($e->getMessage()); // real error goes to the server log
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    if ($savedPath && file_exists($savedPath)) unlink($savedPath);
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    if ($savedPath && file_exists($savedPath)) unlink($savedPath);
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}
