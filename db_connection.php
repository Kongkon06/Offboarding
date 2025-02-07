<?php
$host = 'localhost';
$db = 'offboarding';
$user = 'root'; // Default user for localhost
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
