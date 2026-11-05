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

$ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
$types = [
    'pdf'  => ['application/pdf', 'inline'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'attachment'],
    'odt'  => ['application/vnd.oasis.opendocument.text', 'attachment'],
];
if (!isset($types[$ext])) {
    http_response_code(404);
    exit('File not found');
}

header('Content-Type: ' . $types[$ext][0]);
header('Content-Disposition: ' . $types[$ext][1] . '; filename="' . basename($full) . '"');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($full));
readfile($full);
