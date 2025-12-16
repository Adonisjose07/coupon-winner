<?php
require 'includes/db.php';
require 'includes/draw_logic.php';
require_once 'includes/mail_sender.php';

session_start();

// 1. Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// 2. Authentication Barrier
if (!isset($_SESSION['admin_logged_in'])) {
    $error = "";
    if (isset($_POST['password'])) {
        if ($_POST['password'] === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php"); // Refresh to clear POST
            exit();
        } else {
            $error = "Incorrect Password";
        }
    }

    // Show Login Form and EXIT
    echo '<!DOCTYPE html>
    <html>
    <head><title>Admin Login</title><style>body{font-family:sans-serif;padding:50px;text-align:center;background:#f0f0f0;}</style></head>
    <body>
        <div style="background:white;padding:20px;max-width:300px;margin:0 auto;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);">
            <h2>Login Required</h2>
            ' . ($error ? '<p style="color:red">' . $error . '</p>' : '') . '
            <form method="POST">
                <input type="password" name="password" placeholder="Password" style="padding:10px;width:90%;margin-bottom:10px;">
                <br>
                <button style="padding:10px 20px;cursor:pointer;">Login</button>
            </form>
        </div>
    </body>
    </html>';
    exit();
}

// --- SECURE ZONE (Only runs if logged in) ---

// 3. Export CSV Logic
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="participants_export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Referral Code', 'Referred By (ID)', 'Registration Date']);

    $stmt = $pdo->query("SELECT id, name, email, phone, referral_code, referred_by, created_at FROM participants ORDER BY id ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

$message = "";
$draw_result = null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_date'])) {
        updateSetting('draw_date', $_POST['draw_date']);
        $message = "Date updated.";
    }
    if (isset($_POST['run_draw'])) {
        $draw_result = performDraw();
    }
    if (isset($_POST['reset_draw'])) {
        updateSetting('draw_completed', '0');
        updateSetting('winner_id', NULL);
        $message = "Draw reset. You can run it again.";
    }

    // Email Testing Logic
    if (isset($_POST['test_email_action'])) {
        $test_email = trim($_POST['test_email_to']);
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email address for testing.";
        } else {
            $type = $_POST['test_email_action'];
            $dummy_data = ['name' => 'Test User'];

            if ($type === 'winner') {
                $res = sendEmail($test_email, "TEST: Winner Email", "email_winner.html", $dummy_data);
            } else {
                $res = sendEmail($test_email, "TEST: Loser Email", "email_loser.html", $dummy_data);
            }

            if ($res['status'] == 'success') {
                $message = "Test email sent to $test_email.";
            } else {
                $message = "Error sending test: " . $res['message'];
            }
        }
    }
}

$current_date = getSetting('draw_date');
$is_completed = getSetting('draw_completed');
$winner_id = getSetting('winner_id');

// Get Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background: #f0f0f0;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
        }

        .btn {
            padding: 10px 20px;
            cursor: pointer;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .btn-danger {
            background: #d32f2f;
        }

        .btn-success {
            background: #388e3c;
        }

        pre {
            background: #eee;
            padding: 10px;
            overflow: auto;
            max-height: 200px;
        }
    </style>
</head>

<body>

    <div class="card">
        <a href="?action=logout"
            style="float: right; font-size: 0.9rem; color: #d32f2f; text-decoration: none; margin-left: 15px;">Logout</a>
        <a href="?action=export_csv"
            style="float: right; font-size: 0.9rem; color: #1976d2; text-decoration: none;">Download CSV</a>
        <h1>Sweepstakes Admin</h1>
        <p>Total Participants: <strong><?php echo $total_users; ?></strong></p>

        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>

        <hr>

        <h3>Settings</h3>
        <form method="POST">
            <label>Draw Date:</label>
            <input type="datetime-local" name="draw_date"
                value="<?php echo date('Y-m-d\TH:i', strtotime($current_date)); ?>">
            <button type="submit" name="update_date" class="btn">Update Date</button>
        </form>
    </div>

    <div class="card">
        <h3>Test Emails</h3>
        <form method="POST">
            <label>Send To:</label>
            <input type="email" name="test_email_to" placeholder="your@email.com" required
                style="padding:8px; width:200px;">
            <div style="margin-top:10px;">
                <button type="submit" name="test_email_action" value="winner" class="btn btn-success">Test Winner
                    Template</button>
                <button type="submit" name="test_email_action" value="loser" class="btn">Test Loser Template</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Draw Actions</h3>

        <?php if ($is_completed == '1'): ?>
            <div style="background: #e8f5e9; padding: 15px; border: 1px solid #c8e6c9; border-radius: 4px;">
                <h2 style="color: #2e7d32; margin-top:0;">Draw Completed!</h2>
                <p>Winner ID: <?php echo $winner_id; ?></p>
                <form method="POST" onsubmit="return confirm('Are you sure?');">
                    <button type="submit" name="reset_draw" class="btn btn-danger">Reset Draw</button>
                </form>
            </div>
        <?php else: ?>
            <p>No winner selected yet.</p>
            <form method="POST"
                onsubmit="return confirm('This will select a winner and simulate sending emails. Proceed?');">
                <button type="submit" name="run_draw" class="btn btn-success">RUN DRAW & SEND EMAILS</button>
            </form>
        <?php endif; ?>

        <?php if ($draw_result): ?>
            <div style="margin-top: 20px;">
                <h4>Draw Result Log:</h4>
                <?php if ($draw_result['status'] == 'success'): ?>
                    <p><strong>Winner:</strong> <?php echo htmlspecialchars($draw_result['winner']['name']); ?>
                        (<?php echo $draw_result['winner']['email']; ?>)</p>
                    <pre><?php print_r($draw_result['log']); ?></pre>
                <?php else: ?>
                    <p style="color: red;"><?php echo $draw_result['message']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>