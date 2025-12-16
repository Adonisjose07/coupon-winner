<?php
require 'includes/db.php';

if (!isset($_GET['code'])) {
    header("Location: index.php");
    exit();
}

$user_code = $_GET['code'];

// Fetch user data and referral count
$stmt = $pdo->prepare("SELECT id, name FROM participants WHERE referral_code = ?");
$stmt->execute([$user_code]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM participants WHERE referred_by = ?");
$stmt->execute([$user['id']]);
$referral_count = $stmt->fetch()['count'];

$total_entries = 1 + $referral_count;
$share_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?ref=" . $user_code;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Registered! | Car Dealership</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container">
        <h1>You're In The Race!</h1>
        <p class="subtitle">Thanks for registering, <?php echo htmlspecialchars($user['name']); ?>.</p>

        <div class="entries-box">
            <span class="label">Your Total Chances</span>
            <span class="entries-count"><?php echo $total_entries; ?></span>
            <p style="font-size: 0.9rem; margin-top: 10px;">Get +1 chance for every friend you invite.</p>
        </div>

        <div class="referral-link-box">
            <label>Your Personal Sharing Link:</label>
            <div class="referral-input">
                <input type="text" value="<?php echo $share_link; ?>" readonly>
                <button class="copy-btn">Copy</button>
            </div>
        </div>

        <a href="index.php" style="display: block; margin-top: 30px; color: #aaa; text-decoration: none;">&larr; Back to
            Home</a>
    </div>

    <script src="js/script.js"></script>
</body>

</html>