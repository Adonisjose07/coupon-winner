<?php
require 'includes/db.php';
$stats = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id, name, referral_code FROM participants WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM participants WHERE referred_by = ?");
            $stmt->execute([$user['id']]);
            $referral_count = $stmt->fetch()['count'];

            $stats = [
                'name' => $user['name'],
                'entries' => 1 + $referral_count,
                'referrals' => $referral_count,
                'code' => $user['referral_code'],
                'link' => "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?ref=" . $user['referral_code']
            ];
        } else {
            $error = "Email not found.";
        }
    } else {
        $error = "Please enter your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Status | Car Dealership</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container">
        <h1>Check Your Status</h1>

        <?php if ($stats): ?>
            <p class="subtitle">Welcome back, <?php echo htmlspecialchars($stats['name']); ?>.</p>

            <div class="entries-box">
                <span class="label">Total Entries</span>
                <span class="entries-count"><?php echo $stats['entries']; ?></span>
                <p style="font-size: 0.9rem; margin-top: 10px; color: #fff;">Referrals: <?php echo $stats['referrals']; ?>
                </p>
            </div>

            <div class="referral-link-box">
                <label>Your Sharing Link:</label>
                <div class="referral-input">
                    <input type="text" value="<?php echo $stats['link']; ?>" readonly>
                    <button class="copy-btn">Copy</button>
                </div>
            </div>

            <a href="check_status.php" style="display: block; margin-top: 20px; font-size: 0.9rem; color: #aaa;">Check
                another email</a>

        <?php else: ?>
            <p class="subtitle">Enter your email to see your entries and referral link.</p>

            <?php if ($error): ?>
                <p style="color: var(--primary-color); margin-bottom: 1rem;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="check_status.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com">
                </div>
                <button type="submit" class="cta-btn">Check Status</button>
            </form>
        <?php endif; ?>

        <a href="index.php" style="display: block; margin-top: 30px; color: #aaa; text-decoration: none;">&larr; Back to
            Home</a>
    </div>

    <script src="js/script.js"></script>
</body>

</html>