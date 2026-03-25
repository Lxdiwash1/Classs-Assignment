<?php
require 'db.php';
require '../security/security.php';

// Validate ID from URL
$id = validate_id($_GET['id'] ?? 0);

if (!$id) {
    redirect('admission.php');
}

// Fetch programme details
$stmt = $conn->prepare("
    SELECT p.ProgrammeName, p.Description, l.LevelName, s.Name AS LeaderName
    FROM Programmes p
    JOIN Levels l ON p.LevelID = l.LevelID
    JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
    WHERE p.ProgrammeID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$programme = $result->fetch_assoc();
$stmt->close();

if (!$programme) {
    header('Location: admission.php');
    exit;
}

// Fetch modules for this programme
$stmt2 = $conn->prepare("
    SELECT m.ModuleName, m.Description, s.Name AS ModuleLeader, pm.Year
    FROM ProgrammeModules pm
    JOIN Modules m ON pm.ModuleID = m.ModuleID
    JOIN Staff s ON m.ModuleLeaderID = s.StaffID
    WHERE pm.ProgrammeID = ?
    ORDER BY pm.Year, m.ModuleName
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$modResult = $stmt2->get_result();
$modules = [];
while ($row = $modResult->fetch_assoc()) {
    $modules[$row['Year']][] = $row;
}
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($programme['ProgrammeName']); ?></title>
<link rel="stylesheet" href="../html/style.css">
</head>
<body>

<header>
<h1><?php echo htmlspecialchars($programme['ProgrammeName']); ?></h1>
<p><?php echo htmlspecialchars($programme['LevelName']); ?></p>
</header>

<section class="modules">

<h2>Course Overview</h2>
<p><?php echo htmlspecialchars($programme['Description']); ?></p>

<p><strong>Programme Leader:</strong> <?php echo htmlspecialchars($programme['LeaderName']); ?></p>

<?php foreach ($modules as $year => $mods): ?>
<h2><?php echo $year == 1 ? 'Year 1' : ($year == 2 ? 'Year 2' : 'Year 3'); ?> Modules</h2>
<ul>
  <?php foreach ($mods as $mod): ?>
  <li>
    <strong><?php echo htmlspecialchars($mod['ModuleName']); ?></strong>
    — <?php echo htmlspecialchars($mod['Description']); ?>
    <br><small>Module Leader: <?php echo htmlspecialchars($mod['ModuleLeader']); ?></small>
  </li>
  <?php endforeach; ?>
</ul>
<?php endforeach; ?>

</section>

<a href="admission.php" class="btn back">← Back to Programmes</a>

<footer>
<p>London Technology College &copy;copyright</p>
</footer>

</body>
</html>
