<?php
$host = "localhost";
$port = "5432";
$dbname = "filemanagementdb";
$user = "postgres";
$password = "postgres";

try {
    // Create PDO connection
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    //  Set PDO error mode to exception     
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //    echo "Database connected successfully";
} catch (PDOException $e) {
    throw new Exception("Database connection failed.");
}
