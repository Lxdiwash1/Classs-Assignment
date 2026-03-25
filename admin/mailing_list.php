<?php
require 'auth_check.php';
require '../database/db.php';

$msg = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM InterestedStudents WHERE InterestID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Entry removed.';

    } elseif ($action === 'delete_duplicates') {
        // Remove duplicate emails per programme, keep the earliest registration
        $conn->query("
            DELETE i1 FROM InterestedStudents i1
            INNER JOIN InterestedStudents i2
            WHERE i1.ProgrammeID = i2.ProgrammeID
              AND i1.Email = i2.Email
              AND i1.InterestID > i2.InterestID
        ");
        $msg = 'Duplicate entries removed.';
    }
}

// Handle CSV export
if (isset($_GET['export'])) {
    $data = $conn->query("
        SELECT i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
        FROM InterestedStudents i
        JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
        ORDER BY i.RegisteredAt DESC
    ")->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mailing_list.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Programme', 'Registered At']);
    foreach ($data as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit();
}

// Fetch all entries
$entries = $conn->query("
    SELECT i.InterestID, i.StudentName, i.Email, p.ProgrammeName, i.RegisteredAt
    FROM InterestedStudents i
    JOIN Programmes p ON i.ProgrammeID = p.ProgrammeID
    ORDER BY i.RegisteredAt DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mailing List</title>
<link rel="stylesheet" href="../html/style.css">
<style>
.admin-layout{display:flex;min-height:100vh;}
.sidebar{width:220px;background:#0b3c6f;color:#fff;padding:20px;}
.sidebar h3{margin-bottom:20px;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.1);}
.sidebar a:hover{color:#ffd166;}
.main{flex:1;padding:30px;}
.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;}
th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #eee;}
th{background:#0b3c6f;color:#fff;}
.btn-sm{padding:5px 10px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;}
.btn-del{background:#c0392b;color:#fff;}
.btn-export{padding:10px 18px;background:#27ae60;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;font-size:0.9rem;}
.btn-dedup{padding:10px 18px;background:#e67e22;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:0.9rem;}
.logout-btn{padding:8px 16px;background:#c0392b;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;}
.msg{color:green;font-weight:bold;margin-bottom:15px;}
.actions-row{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;}
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
            <h1>Mailing List (<?php echo count($entries); ?> entries)</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($msg): ?>
            <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <div class="actions-row">
            <a href="?export=1" class="btn-export">Export CSV</a>
            <form method="POST" onsubmit="return confirm('Remove all duplicate registrations?')">
                <input type="hidden" name="action" value="delete_duplicates">
                <button type="submit" class="btn-dedup">Remove Duplicates</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Programme</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['StudentName']); ?></td>
                    <td><?php echo htmlspecialchars($e['Email']); ?></td>
                    <td><?php echo htmlspecialchars($e['ProgrammeName']); ?></td>
                    <td><?php echo htmlspecialchars($e['RegisteredAt']); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this entry?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $e['InterestID']; ?>">
                            <button class="btn-sm btn-del" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
