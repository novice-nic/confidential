<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// require_once '../include/conn.php';
require_once __DIR__ . '/../../include/conn.php';

$query = $conn->query("SELECT id, file_number, file_code, subject, remarks FROM file_master WHERE is_deleted = FALSE ORDER BY id DESC");
// $query->execute();
$fetchFiles = $query->fetchAll(PDO::FETCH_ASSOC);
