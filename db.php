<?php

$host = "localhost";
$port = "5432";
$dbname = "classwall";
$username = "postgres";
$password = "thea4517";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
