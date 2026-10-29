<?php
require_once __DIR__ . '/../../include/conn.php';

$stmt = $conn->prepare(
    "SELECT f.id, f.question, f.answer, f.total_docs,
            fm.file_number, fm.subject AS file_subject
     FROM public.faq AS f
     INNER JOIN public.file_master AS fm ON fm.id = f.file_id
     WHERE f.is_deleted = FALSE AND fm.is_deleted = FALSE
     ORDER BY f.created_at DESC"
);
$stmt->execute();
$fetchFaqs = $stmt->fetchAll(PDO::FETCH_ASSOC);