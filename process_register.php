<?php
require 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $referred_by_code = isset($_POST['referred_by_code']) ? trim($_POST['referred_by_code']) : null;

    // Basic Validation
    if (empty($name) || empty($email) || empty($phone)) {
        die("Please fill all fields.");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Email already registered! <a href='index.php'>Go back</a>");
    }

    // Resolve Referrer
    $referrer_id = null;
    if ($referred_by_code) {
        $stmt = $pdo->prepare("SELECT id FROM participants WHERE referral_code = ?");
        $stmt->execute([$referred_by_code]);
        $referrer = $stmt->fetch();
        if ($referrer) {
            $referrer_id = $referrer['id'];
        }
    }

    // Generate Unique Referral Code
    function generateCode($length = 6)
    {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
    }

    $referral_code = generateCode();
    // Ensure uniqueness (simple check, in prod loop to be sure)

    // Insert User
    $sql = "INSERT INTO participants (name, email, phone, referral_code, referred_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$name, $email, $phone, $referral_code, $referrer_id]);

        // Redirect to Thank You page
        header("Location: thank_you.php?code=" . $referral_code);
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>