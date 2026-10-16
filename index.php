<?php
session_start();

if (isset($_SESSION['user_id']) && (int)($_SESSION['role'] ?? 0) === 1) {
    header('Location: ./admin/dashboard.php');
    exit;
}

header('Location: ./login.php');
exit;
