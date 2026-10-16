<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
header("Content-Type: application/json");

function checkMaxLength(string $value, int $max, string $label): void
{
    if ($value !== '' && mb_strlen($value) > $max) {
        throw new Exception("$label must not be more than $max characters.");
    }
}

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
    $id      = (int)($_POST['id'] ?? 0);   // 0 = new record, above 0 = update
    $fileNum = trim($_POST['fileNumber'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $remarks = trim($_POST['remark'] ?? '');
    $modified_at_date = date('Y-m-d H:i:s');

    // 4. Checks
    if ($fileNum === '' || $subject === '' || $remarks === '') {
        throw new Exception("All fields are required.");
    }

    checkMaxLength($fileNum, 15, "File number");
    checkMaxLength($subject, 200, "Subject");
    checkMaxLength($remarks, 500, "Remarks");

    if (!preg_match('/^[A-Za-z0-9\/-]+$/', $fileNum)) {
        throw new Exception("File number can only have letters, numbers, - and /.");
    }

    // Duplicate file number (when updating, ignore the record itself)
    $stmt = $conn->prepare(
        "SELECT 1 FROM file_master WHERE file_number = :fileNum AND id <> :id"
    );
    $stmt->execute([':fileNum' => $fileNum, ':id' => $id]);
    if ($stmt->fetch()) {
        throw new Exception("This file number already exists.");
    }

    if ($id > 0) {
        // ---------- UPDATE ----------
        $stmt = $conn->prepare(
            "UPDATE file_master
             SET file_number = :file_number, subject = :subject, remarks = :remarks, modified_at = :modified_at_date
             WHERE id = :id AND is_deleted = FALSE"
        );
        $stmt->execute([
            ':file_number' => $fileNum,
            ':subject'     => $subject,
            ':remarks'     => $remarks,
            ':modified_at_date' => $modified_at_date,
            ':id'          => $id,
        ]);

        $message = "Data Updated Successfully";
    } else {
        // ---------- INSERT ----------
        $row = $conn->query("SELECT MAX(RIGHT(file_code, 5)) AS maxno FROM file_master")
            ->fetch(PDO::FETCH_ASSOC);
        $filecode = 'F' . str_pad((int)($row['maxno'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare(
            "INSERT INTO file_master (file_number, subject, remarks, created_at, file_code)
             VALUES (:file_number, :subject, :remarks, :created_at, :filecode)"
        );
        $stmt->execute([
            ':file_number' => $fileNum,
            ':subject'     => $subject,
            ':remarks'     => $remarks,
            ':created_at'  => date('Y-m-d H:i:s'),
            ':filecode'    => $filecode,
        ]);

        $message = "Data Inserted Successfully";
    }

    echo json_encode([
        "status"  => "Success",
        "message" => $message,
        "mode"    => $id > 0 ? "update" : "insert"
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}
