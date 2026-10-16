<?php
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../include/conn.php';

    // 1. Only logged-in admins
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
        throw new Exception("You are not allowed to do this.");
    }

    // 2. Only POST, with a valid id
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception("Invalid file.");
    }

    // 3. The file must exist and not be deleted already
    $stmt = $conn->prepare(
        "SELECT 1 FROM public.file_master WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        throw new Exception("File not found.");
    }

    // 4. Block the delete if documents are attached
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM public.file_document
         WHERE file_id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([':id' => $id]);
    $count = (int)$stmt->fetchColumn();

    if ($count > 0) {
        throw new Exception(
            "This file has $count document(s). Delete those documents first."
        );
    }

    $deleted_at_date = date('Y-m-d H:i:s');
    // 5. Soft delete: mark it, do not remove the row
    $stmt = $conn->prepare(
        "UPDATE public.file_master SET is_deleted = TRUE, deleted_at = :deleted_at_date
         WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([
        ':id' => $id,
        ':deleted_at_date' => $deleted_at_date
    ]);

    echo json_encode(["status" => "Success", "message" => "File deleted successfully."]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}
