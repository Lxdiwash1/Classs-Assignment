<?php
require 'auth_check.php';
require '../database/db.php';

$msg = '';

$staffList      = $conn->query("SELECT StaffID, Name FROM Staff ORDER BY Name")->fetch_all(MYSQLI_ASSOC);
$programmeList  = $conn->query("SELECT ProgrammeID, ProgrammeName FROM Programmes ORDER BY ProgrammeName")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = htmlspecialchars(strip_tags(trim($_POST['name'])), ENT_QUOTES, 'UTF-8');
        $desc     = htmlspecialchars(strip_tags(trim($_POST['description'])), ENT_QUOTES, 'UTF-8');
        $leaderID = (int)$_POST['leader_id'];
        $stmt = $conn->prepare("INSERT INTO Modules (ModuleName, Description, ModuleLeaderID) VALUES (?,?,?)");
        $stmt->bind_param("ssi", $name, $desc, $leaderID);
        $stmt->execute();
        $moduleID = $stmt->insert_id;
        $stmt->close();

        // Assign to programme if selected
        if (!empty($_POST['programme_id']) && !empty($_POST['year'])) {
            $progID = (int)$_POST['programme_id'];
            $year   = (int)$_POST['year'];
            $stmt2  = $conn->prepare("INSERT INTO ProgrammeModules (ProgrammeID, ModuleID, Year) VALUES (?,?,?)");
            $stmt2->bind_param("iii", $progID, $moduleID, $year);
            $stmt2->execute();
            $stmt2->close();
        }
        $msg = 'Module added.';

    } elseif ($action === 'edit') {
        $id       = (int)$_POST['id'];
        $name     = htmlspecialchars(strip_tags(trim($_POST['name'])), ENT_QUOTES, 'UTF-8');
        $desc     = htmlspecialchars(strip_tags(trim($_POST['description'])), ENT_QUOTES, 'UTF-8');
        $leaderID = (int)$_POST['leader_id'];
        $stmt = $conn->prepare("UPDATE Modules SET ModuleName=?, Description=?, ModuleLeaderID=? WHERE ModuleID=?");
        $stmt->bind_param("ssii", $name, $desc, $leaderID, $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Module updated.';

    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM ProgrammeModules WHERE ModuleID = $id");
        $stmt = $conn->prepare("DELETE FROM Modules WHERE ModuleID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Module deleted.';
    }
}

$modules = $conn->query("
    SELECT m.ModuleID, m.ModuleName, m.Description, s.Name AS LeaderName, m.ModuleLeaderID
    FROM Modules m
    JOIN Staff s ON m.ModuleLeaderID = s.StaffID
    ORDER BY m.ModuleName
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Modules</title>
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
.btn-edit{background:#2980b9;color:#fff;}
.btn-del{background:#c0392b;color:#fff;}
.form-box{background:#fff;padding:20px;border-radius:8px;margin-bottom:25px;box-shadow:0 2px 8px rgba(0,0,0,0.08);}
.form-box input,.form-box select,.form-box textarea{width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ccc;border-radius:5px;}
.form-box button{padding:10px 20px;background:#0b3c6f;color:#fff;border:none;border-radius:5px;cursor:pointer;margin-right:8px;}
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
            <h1>Manage Modules</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php if ($msg): ?>
            <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <!-- Add Form -->
        <div class="form-box">
            <h3>Add New Module</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Module Name" required>
                <select name="leader_id" required>
                    <option value="">Select Module Leader</option>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?php echo $s['StaffID']; ?>"><?php echo htmlspecialchars($s['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="description" placeholder="Description" rows="2"></textarea>
                <select name="programme_id">
                    <option value="">Assign to Programme (optional)</option>
                    <?php foreach ($programmeList as $pr): ?>
                        <option value="<?php echo $pr['ProgrammeID']; ?>"><?php echo htmlspecialchars($pr['ProgrammeName']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="year">
                    <option value="">Year (if assigning)</option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                </select>
                <button type="submit">Add Module</button>
            </form>
        </div>

        <!-- Modules Table -->
        <table>
            <thead>
                <tr>
                    <th>Module Name</th>
                    <th>Leader</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($modules as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['ModuleName']); ?></td>
                    <td><?php echo htmlspecialchars($m['LeaderName']); ?></td>
                    <td><?php echo htmlspecialchars(substr($m['Description'], 0, 60)) . '...'; ?></td>
                    <td>
                        <button class="btn-sm btn-edit" onclick="fillEdit(<?php echo htmlspecialchars(json_encode($m)); ?>)">Edit</button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this module?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $m['ModuleID']; ?>">
                            <button class="btn-sm btn-del" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit Form -->
        <div id="editBox" class="form-box" style="display:none;margin-top:25px;">
            <h3>Edit Module</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <input type="text" name="name" id="editName" placeholder="Module Name" required>
                <select name="leader_id" id="editLeader" required>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?php echo $s['StaffID']; ?>"><?php echo htmlspecialchars($s['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="description" id="editDesc" rows="2"></textarea>
                <button type="submit">Save Changes</button>
                <button type="button" onclick="document.getElementById('editBox').style.display='none'">Cancel</button>
            </form>
        </div>
    </main>
</div>
<script>
function fillEdit(m) {
    document.getElementById('editBox').style.display = 'block';
    document.getElementById('editId').value = m.ModuleID;
    document.getElementById('editName').value = m.ModuleName;
    document.getElementById('editDesc').value = m.Description;
    document.getElementById('editLeader').value = m.ModuleLeaderID;
    document.getElementById('editBox').scrollIntoView();
}
</script>
</body>
</html>
