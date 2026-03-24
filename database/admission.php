<?php
require 'db.php';

// Fetch all programmes with their level name and leader name
$sql = "
    SELECT p.ProgrammeID, p.ProgrammeName, p.Description, l.LevelName, s.Name AS LeaderName
    FROM Programmes p
    JOIN Levels l ON p.LevelID = l.LevelID
    JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
    ORDER BY l.LevelID, p.ProgrammeName
";
$result = $conn->query($sql);
$programmes = [];
while ($row = $result->fetch_assoc()) {
    $programmes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Course Hub</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<header>
<h1>Student Course Hub</h1>
<p>Explore our undergraduate and postgraduate computer programmes</p>
</header>

<section class="programmes">
<?php foreach ($programmes as $prog): ?>
<div class="card">
  <h2><?php echo htmlspecialchars($prog['ProgrammeName']); ?></h2>
  <p class="level-badge"><?php echo htmlspecialchars($prog['LevelName']); ?></p>
  <p><?php echo htmlspecialchars($prog['Description']); ?></p>
  <a href="programme.php?id=<?php echo $prog['ProgrammeID']; ?>">View Programme</a>
</div>
<?php endforeach; ?>
</section>

<section class="interest">

<h2>Register Your Interest</h2>

<?php if (isset($_GET['success'])): ?>
  <p class="success-text">Thank you! Your interest has been registered successfully.</p>
<?php elseif (isset($_GET['error'])): ?>
  <p class="error">Something went wrong. Please try again.</p>
<?php endif; ?>

<form action="submit_interest.php" method="POST">

  <div class="form-group">
    <input type="text" name="name" placeholder="Your Name" required>
  </div>

  <div class="form-group">
    <input type="email" name="email" placeholder="Your Email" required>
  </div>

  <div class="form-group">
    <select name="programme_id" required>
      <option value="" disabled selected>Select Programme</option>
      <?php foreach ($programmes as $prog): ?>
        <option value="<?php echo $prog['ProgrammeID']; ?>">
          <?php echo htmlspecialchars($prog['ProgrammeName']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit">Submit</button>
</form>

</section>

<footer>
<p>London Technology College &copy;copyright</p>
</footer>

</body>
</html>
