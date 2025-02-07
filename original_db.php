<?php
$host = 'localhost';
$db = 'dsbu';
$user = 'root'; // Default user for localhost
$pass = '';

try {
    $origin_db = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $origin_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
