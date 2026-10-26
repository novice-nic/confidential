<?php

require_once './include/conn.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $sql = "SELECT user_id, user_name, role
                FROM users
                WHERE user_name = :username
                  AND password = :password
                  AND role = 1
                  AND status = 1";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];

            $_SESSION['successMessage'] = "Login successful. Welcome, " . htmlspecialchars($user['user_name']) . "!";

            header("Location: ./admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
