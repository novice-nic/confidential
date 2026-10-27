<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../include/conn.php';

    // Only logged-in admins
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
        throw new Exception("You are not allowed to do this.");
    }

    $fileId = (int)($_GET['file_id'] ?? 0);
    if ($fileId <= 0) {
        throw new Exception("Invalid file.");
    }

    $stmt = $conn->prepare(
        "SELECT fd.id, dt.name AS doc_type_name, fd.doc_name, fd.doc_date, fd.remarks
         FROM public.file_document AS fd
         INNER JOIN public.doc_type AS dt ON fd.doc_type_id = dt.id
         WHERE fd.file_id = :file_id AND fd.is_deleted = FALSE
         ORDER BY fd.doc_date DESC, fd.id DESC"
    );
    $stmt->execute([':file_id' => $fileId]);

    $rows = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rows[] = [
            'id'       => (int)$r['id'],
            'type'     => $r['doc_type_name'] ?? '',
            'name'     => $r['doc_name'] ?? '',
            'date_fmt' => !empty($r['doc_date']) ? date('d-m-Y', strtotime($r['doc_date'])) : '',
            'remarks'  => $r['remarks'] ?? '',
        ];
    }

    echo json_encode(["status" => "Success", "data" => $rows]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}