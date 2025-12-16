<?php
$host = 'localhost';
$dbname = 'car_sweepstakes';
$username = 'root'; // Update if different
$password = ''; // Update if different

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set a default timezone to ensure dates are consistent
    date_default_timezone_set('America/New_York'); // Change as needed for dealership location
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>