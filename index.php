<?php
// Check for referral code in URL
$ref_code = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Win a $200 Amazon Gift Card | Car Dealership</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <?php
    require_once 'includes/db.php';
    // Fetch Draw Date
    $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'draw_date'");
    $stmt->execute();
    $draw_date_str = $stmt->fetchColumn();
    // Use stored date or default +14 days. 
    // Return Milliseconds for JS (timestamp * 1000)
    $draw_timestamp = $draw_date_str ? strtotime($draw_date_str) * 1000 : strtotime("+14 days") * 1000;
    ?>
    <script>
        window.DRAW_DATE = <?php echo $draw_timestamp; ?>;
    </script>
</head>

<body>

    <div class="container">
        <h1>Win <span class="highlight">$200</span> Amazon Card</h1>
        <p class="subtitle">Experience the thrill. Drive the best. Win big.</p>

        <!-- Countdown -->
        <div class="countdown-container">
            <div class="time-box">
                <span class="number" id="days">00</span>
                <span class="label">Days</span>
            </div>
            <div class="time-box">
                <span class="number" id="hours">00</span>
                <span class="label">Hours</span>
            </div>
            <div class="time-box">
                <span class="number" id="minutes">00</span>
                <span class="label">Mins</span>
            </div>
            <div class="time-box">
                <span class="number" id="seconds">00</span>
                <span class="label">Secs</span>
            </div>
        </div>

        <form action="process_register.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="john@example.com">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="(555) 123-4567">
            </div>

            <!-- Hidden field for tracking referral -->
            <input type="hidden" name="referred_by_code" value="<?php echo $ref_code; ?>">

            <button type="submit" class="cta-btn">Enter to Win</button>
        </form>

        <p class="fine-print">No purchase necessary. Terms and conditions apply.</p>

        <p style="margin-top: 20px; font-size: 0.9rem;">
            Already registered? <a href="check_status.php"
                style="color: var(--primary-color); text-decoration: none;">Check your status</a>
        </p>
    </div>

    <script src="js/script.js"></script>
</body>

</html>