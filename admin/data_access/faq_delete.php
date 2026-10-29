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
        throw new Exception("Invalid FAQ.");
    }

    // 3. Soft delete: mark it, do not remove the row.
    // The link rows in faq_document stay as they are.
    $stmt = $conn->prepare(
        "UPDATE public.faq
         SET is_deleted = TRUE, deleted_at = :deleted_at
         WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([
        ':deleted_at' => date('Y-m-d H:i:s'),
        ':id'         => $id,
    ]);

    // Nothing changed: the FAQ does not exist or is already deleted
    if ($stmt->rowCount() === 0) {
        throw new Exception("FAQ not found.");
    }

    echo json_encode(["status" => "Success", "message" => "FAQ deleted successfully."]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}
