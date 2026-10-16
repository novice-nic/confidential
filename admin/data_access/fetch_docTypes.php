<?php

require_once __DIR__ . '/../../include/conn.php';

$query = $conn->query("SELECT id, name FROM doc_type ORDER BY id ASC");
// $query->execute();
$fetchDocTypes = $query->fetchAll(PDO::FETCH_ASSOC);

