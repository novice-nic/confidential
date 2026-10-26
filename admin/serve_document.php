<?php
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../include/conn.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT doc_pdf FROM public.file_document
     WHERE id = :id AND is_deleted = FALSE"
);
$stmt->execute([':id' => $id]);
$path = $stmt->fetchColumn();

if (!$path) {
    http_response_code(404);
    exit('Document not found');
}

$full = realpath(__DIR__ . '/../' . $path);
$base = realpath(__DIR__ . '/../uploads/documents');

if ($full === false || strpos($full, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($full)) {
    http_response_code(404);
    exit('File not found');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($full) . '"');
header('Content-Length: ' . filesize($full));
readfile($full);
