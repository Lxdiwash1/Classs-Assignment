<?php
require 'db.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: admission.php');
    exit;
}

// Fetch programme details
$stmt = $conn->prepare("
    SELECT p.ProgrammeName, p.Description, l.LevelName, s.Name AS LeaderName
    FROM Programmes p
    JOIN Levels l ON p.LevelID = l.LevelID
    JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
    WHERE p.ProgrammeID = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$prog = $stmt->get_result()->fetch_assoc();

if (!$prog) {
    header('Location: admission.php');
    exit;
}

// Fetch modules grouped by year
$stmt2 = $conn->prepare("
    SELECT m.ModuleName, m.Description, s.Name AS ModuleLeader, pm.Year
    FROM ProgrammeModules pm
    JOIN Modules m ON pm.ModuleID = m.ModuleID
    JOIN Staff s ON m.ModuleLeaderID = s.StaffID
    WHERE pm.ProgrammeID = ?
    ORDER BY pm.Year, m.ModuleName
");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$modulesResult = $stmt2->get_result();

$modulesByYear = [];
while ($row = $modulesResult->fetch_assoc()) {
    $modulesByYear[$row['Year']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($prog['ProgrammeName']); ?></title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<header>
<h1><?php echo htmlspecialchars($prog['ProgrammeName']); ?></h1>
<p><?php echo htmlspecialchars($prog['LevelName']); ?></p>
</header>

<section class="modules">

<h2>Course Overview</h2>
<p><?php echo htmlspecialchars($prog['Description']); ?></p>

<p><strong>Programme Leader:</strong> <?php echo htmlspecialchars($prog['LeaderName']); ?></p>

<h2>Modules</h2>

<?php foreach ($modulesByYear as $year => $modules): ?>
  <h3><?php echo $year == 1 ? 'Year 1' : ($year == 2 ? 'Year 2' : 'Year 3'); ?></h3>
  <table class="module-table">
    <thead>
      <tr>
        <th>Module</th>
        <th>Module Leader</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($modules as $mod): ?>
      <tr>
        <td><?php echo htmlspecialchars($mod['ModuleName']); ?></td>
        <td><?php echo htmlspecialchars($mod['ModuleLeader']); ?></td>
        <td><?php echo htmlspecialchars($mod['Description']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>

</section>

<a href="admission.php" class="btn back">&#8592; Back to Programmes</a>

<footer>
<p>London Technology College &copy;copyright</p>
</footer>

</body>
</html>
