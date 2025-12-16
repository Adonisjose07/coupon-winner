<?php
require_once __DIR__ . '/env_loader.php';

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'car_sweepstakes';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set a default timezone to ensure dates are consistent
    date_default_timezone_set('America/New_York'); // Change as needed for dealership location
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>