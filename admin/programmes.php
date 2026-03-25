<?php
require 'auth_check.php';
require '../database/db.php';

$msg = '';

// Fetch staff for leader dropdown
$staffList = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetch_all(MYSQLI_ASSOC);
$levelList = $conn->query("SELECT LevelID, LevelName FROM Levels")->fetch_all(MYSQLI_ASSOC);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = htmlspecialchars(strip_tags(trim($_POST['name'])), ENT_QUOTES, 'UTF-8');
        $desc     = htmlspecialchars(strip_tags(trim($_POST['description'])), ENT_QUOTES, 'UTF-8');
        $levelID  = (int)$_POST['level_id'];
        $leaderID = (int)$_POST['leader_id'];
        $stmt = $conn->prepare("INSERT INTO Programmes (ProgrammeName, Description, LevelID, ProgrammeLeaderID, IsPublished) VALUES (?,?,?,?,1)");
        $stmt->bind_param("ssii", $name, $desc, $levelID, $leaderID);
        $stmt->execute();
        $stmt->close();
        $msg = 'Programme added.';

    } elseif ($action === 'edit') {
        $id       = (int)$_POST['id'];
        $name     = htmlspecialchars(strip_tags(trim($_POST['name'])), ENT_QUOTES, 'UTF-8');
        $desc     = htmlspecialchars(strip_tags(trim($_POST['description'])), ENT_QUOTES, 'UTF-8');
        $levelID  = (int)$_POST['level_id'];
        $leaderID = (int)$_POST['leader_id'];
        $stmt = $conn->prepare("UPDATE Programmes SET ProgrammeName=?, Description=?, LevelID=?, ProgrammeLeaderID=? WHERE ProgrammeID=?");
        $stmt->bind_param("ssiii", $name, $desc, $levelID, $leaderID, $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Programme updated.';

    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM Programmes WHERE ProgrammeID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Programme deleted.';

    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE Programmes SET IsPublished = NOT IsPublished WHERE ProgrammeID = $id");
        $msg = 'Status updated.';
    }
}

// Fetch all programmes
$programmes = $conn->query("
    SELECT p.ProgrammeID, p.ProgrammeName, p.Description, p.IsPublished,
           l.LevelName, s.Name AS LeaderName, p.LevelID, p.ProgrammeLeaderID
    FROM Programmes p
    JOIN Levels l ON p.LevelID = l.LevelID
    JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
    ORDER BY l.LevelID, p.ProgrammeName
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Programmes</title>
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
.badge-pub{background:#27ae60;color:#fff;padding:3px 8px;border-radius:4px;font-size:0.8rem;}
.badge-draft{background:#e67e22;color:#fff;padding:3px 8px;border-radius:4px;font-size:0.8rem;}
.btn-sm{padding:5px 10px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;}
.btn-edit{background:#2980b9;color:#fff;}
.btn-del{background:#c0392b;color:#fff;}
.btn-toggle{background:#7f8c8d;color:#fff;}
.form-box{background:#fff;padding:20px;border-radius:8px;margin-bottom:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.form-box input,.form-box select,.form-box textarea{width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ccc;border-radius:5px;}
.form-box button{padding:10px 20px;background:#0b3c6f;color:#fff;border:none;border-radius:5px;cursor:pointer;}
.logout-btn{padding:8px 16px;background:#c0392b;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;}
.msg{color:green;font-weight:bold;margin-bottom:15px;}
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
            <h1>Manage Programmes</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($msg): ?>
            <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <!-- Add Form -->
        <div class="form-box">
            <h3>Add New Programme</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Programme Name" required>
                <select name="level_id" required>
                    <option value="">Select Level</option>
                    <?php foreach ($levelList as $l): ?>
                        <option value="<?php echo $l['LevelID']; ?>"><?php echo htmlspecialchars($l['LevelName']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="leader_id" required>
                    <option value="">Select Programme Leader</option>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?php echo $s['StaffID']; ?>"><?php echo htmlspecialchars($s['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="description" placeholder="Description" rows="3"></textarea>
                <button type="submit">Add Programme</button>
            </form>
        </div>

        <!-- Programmes Table -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Leader</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($programmes as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ProgrammeName']); ?></td>
                    <td><?php echo htmlspecialchars($p['LevelName']); ?></td>
                    <td><?php echo htmlspecialchars($p['LeaderName']); ?></td>
                    <td>
                        <?php if ($p['IsPublished']): ?>
                            <span class="badge-pub">Published</span>
                        <?php else: ?>
                            <span class="badge-draft">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Toggle publish -->
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $p['ProgrammeID']; ?>">
                            <button class="btn-sm btn-toggle" type="submit"><?php echo $p['IsPublished'] ? 'Unpublish' : 'Publish'; ?></button>
                        </form>
                        <!-- Edit -->
                        <button class="btn-sm btn-edit" onclick="fillEdit(<?php echo htmlspecialchars(json_encode($p)); ?>)">Edit</button>
                        <!-- Delete -->
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this programme?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['ProgrammeID']; ?>">
                            <button class="btn-sm btn-del" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div id="editBox" class="form-box" style="display:none;margin-top:25px;">
            <h3>Edit Programme</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <input type="text" name="name" id="editName" placeholder="Programme Name" required>
                <select name="level_id" id="editLevel" required>
                    <?php foreach ($levelList as $l): ?>
                        <option value="<?php echo $l['LevelID']; ?>"><?php echo htmlspecialchars($l['LevelName']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="leader_id" id="editLeader" required>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?php echo $s['StaffID']; ?>"><?php echo htmlspecialchars($s['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="description" id="editDesc" rows="3"></textarea>
                <button type="submit">Save Changes</button>
                <button type="button" onclick="document.getElementById('editBox').style.display='none'">Cancel</button>
            </form>
        </div>
    </main>
</div>
<script>
function fillEdit(p) {
    document.getElementById('editBox').style.display = 'block';
    document.getElementById('editId').value = p.ProgrammeID;
    document.getElementById('editName').value = p.ProgrammeName;
    document.getElementById('editDesc').value = p.Description;
    document.getElementById('editLevel').value = p.LevelID;
    document.getElementById('editLeader').value = p.ProgrammeLeaderID;
    document.getElementById('editBox').scrollIntoView();
}
</script>
</body>
</html>
