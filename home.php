<?php
session_start();

/* ---------------- PROTECT DASHBOARD ---------------- */
if (!isset($_SESSION['user_id'], $_SESSION['firm_name'])) {
    header("Location: login.php");
    exit();
}

$firm_name = $_SESSION['firm_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | EXPIROCHAIN</title>
    <link rel="shortcut icon" href="images/favicon/android-chrome-192x192.png">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<div class="header">
    <h1>EXPIROCHAIN Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="dashboard">
    <div class="welcome-card">
        <h2>Welcome, <span><?php echo htmlspecialchars($firm_name); ?></span></h2>
        <p>Manage medicine expiry, reduce waste, and stay compliant — all in one place.</p>
    </div>

    <div class="stats">
        <div class="stat-box"><h3>Total Medicines</h3><p>0</p></div>
        <div class="stat-box"><h3>Near Expiry</h3><p>0</p></div>
        <div class="stat-box"><h3>Expired</h3><p>0</p></div>
        <div class="stat-box"><h3>Waste Prevented</h3><p>₹0</p></div>
    </div>
</div>

<footer>
    © <?php echo date('Y'); ?> EXPIROCHAIN. All rights reserved.
</footer>

</body>
</html>
