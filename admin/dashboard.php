<?php
require 'auth_check.php';
require '../database/db.php';

$totalProgrammes  = $conn->query("SELECT COUNT(*) AS c FROM Programmes")->fetch_assoc()['c'];
$totalModules     = $conn->query("SELECT COUNT(*) AS c FROM Modules")->fetch_assoc()['c'];
$published        = $conn->query("SELECT COUNT(*) AS c FROM Programmes WHERE IsPublished = 1")->fetch_assoc()['c'];
$drafts           = $totalProgrammes - $published;
$totalInterested  = $conn->query("SELECT COUNT(*) AS c FROM InterestedStudents")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../html/style.css">
<style>
.admin-layout { display:flex; min-height:100vh; }
.sidebar { width:220px; background:#0b3c6f; color:#fff; padding:20px; }
.sidebar h3 { margin-bottom:20px; font-size:1.1rem; }
.sidebar a { display:block; color:#fff; text-decoration:none; padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.1); }
.sidebar a:hover { color:#ffd166; }
.main { flex:1; padding:30px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
.stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:15px; margin-bottom:30px; }
.stat-card { background:#fff; padding:20px; border-radius:8px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.stat-card .num { font-size:2rem; font-weight:bold; color:#0b3c6f; }
.stat-card .label { font-size:0.85rem; color:#666; margin-top:5px; }
.logout-btn { padding:8px 16px; background:#c0392b; color:#fff; border:none; border-radius:5px; cursor:pointer; text-decoration:none; }
</style>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <h3>Admin Panel</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="programmes.php">Programmes</a>
        <a href="modules.php">Modules</a>
        <a href="mailing_list.php">Mailing List</a>
    </aside>
    <main class="main">
        <div class="top-bar">
            <h1>Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="num"><?php echo $totalProgrammes; ?></div>
                <div class="label">Total Programmes</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $totalModules; ?></div>
                <div class="label">Total Modules</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $published; ?></div>
                <div class="label">Published</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $drafts; ?></div>
                <div class="label">Drafts</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $totalInterested; ?></div>
                <div class="label">Interested Students</div>
            </div>
        </div>
        <h2>Quick Links</h2>
        <p><a href="programmes.php">Manage Programmes</a> &nbsp;|&nbsp; <a href="modules.php">Manage Modules</a> &nbsp;|&nbsp; <a href="mailing_list.php">View Mailing List</a></p>
    </main>
</div>
</body>
</html>
