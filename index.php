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

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
        }
        .header {
            background: #0f172a;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 20px; margin: 0; }
        .logout-btn {
            background: #ef4444;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .dashboard { padding: 30px; }
        .welcome-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .welcome-card span { color: #2563eb; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .stat-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        footer {
            text-align: center;
            padding: 15px;
            color: #64748b;
            font-size: 14px;
        }
    </style>
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
