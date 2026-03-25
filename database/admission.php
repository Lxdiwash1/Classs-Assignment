<?php
require 'db.php';
require '../security/security.php';

$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$levelFilter = isset($_GET['level']) ? (int)$_GET['level'] : 0;

$sql = "
    SELECT p.ProgrammeID, p.ProgrammeName, p.Description, l.LevelName, l.LevelID, s.Name AS LeaderName
    FROM Programmes p
    JOIN Levels l ON p.LevelID = l.LevelID
    JOIN Staff s ON p.ProgrammeLeaderID = s.StaffID
    WHERE p.IsPublished = 1
";
$params = [];
$types  = '';

if ($levelFilter > 0) {
    $sql .= " AND p.LevelID = ?";
    $params[] = $levelFilter;
    $types   .= 'i';
}
if ($search !== '') {
    $sql .= " AND (p.ProgrammeName LIKE ? OR p.Description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
$sql .= " ORDER BY l.LevelID, p.ProgrammeName";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$programmes = [];
while ($row = $result->fetch_assoc()) {
    $programmes[] = $row;
}
$stmt->close();

// All programmes for the interest form dropdown (always show all published)
$allProgrammes = $conn->query("
    SELECT p.ProgrammeID, p.ProgrammeName FROM Programmes p WHERE p.IsPublished = 1 ORDER BY p.ProgrammeName
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Course Hub</title>
<link rel="stylesheet" href="../html/style.css">
</head>
<body>

<header>
<h1>Student Course Hub</h1>
<p>Explore our undergraduate and postgraduate computer programmes</p>
</header>

<!-- Filter & Search Bar -->
<section style="padding:15px 20px;background:#fff;border-bottom:1px solid #eee;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <input type="text" name="search" placeholder="Search programmes..." value="<?php echo htmlspecialchars($search); ?>" style="padding:8px;border:1px solid #ccc;border-radius:5px;flex:1;min-width:180px;">
        <select name="level" style="padding:8px;border:1px solid #ccc;border-radius:5px;">
            <option value="0">All Levels</option>
            <option value="1" <?php echo $levelFilter == 1 ? 'selected' : ''; ?>>Undergraduate</option>
            <option value="2" <?php echo $levelFilter == 2 ? 'selected' : ''; ?>>Postgraduate</option>
        </select>
        <button type="submit" style="padding:8px 16px;background:#0b3c6f;color:#fff;border:none;border-radius:5px;cursor:pointer;">Filter</button>
        <a href="admission.php" style="padding:8px 12px;background:#888;color:#fff;border-radius:5px;text-decoration:none;">Clear</a>
    </form>
</section>

<section class="programmes">
<?php if (empty($programmes)): ?>
    <p style="padding:20px;grid-column:1/-1;">No programmes found.</p>
<?php endif; ?>
<?php foreach ($programmes as $prog): ?>
<div class="card">
  <h2><?php echo htmlspecialchars($prog['ProgrammeName']); ?></h2>
  <p class="level-badge"><?php echo htmlspecialchars($prog['LevelName']); ?></p>
  <p><?php echo htmlspecialchars($prog['Description']); ?></p>
  <a href="programme.php?id=<?php echo (int)$prog['ProgrammeID']; ?>">View Programme</a>
</div>
<?php endforeach; ?>
</section>

<section class="interest">
<h2>Register Your Interest</h2>

<?php if (isset($_GET['success'])): ?>
  <p class="success-text">Thank you! Your interest has been registered successfully.</p>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
  <p class="error">You have already registered interest in this programme.</p>
<?php elseif (isset($_GET['error'])): ?>
  <p class="error">Something went wrong. Please try again.</p>
<?php endif; ?>

<form action="submit_interest.php" method="POST" id="interestForm">
  <div class="form-group">
    <span class="error" id="nameError"></span>
    <input type="text" name="name" id="name" placeholder="Your Name">
  </div>
  <div class="form-group">
    <span class="error" id="emailError"></span>
    <input type="text" name="email" id="email" placeholder="Your Email">
  </div>
  <div class="form-group">
    <span class="error" id="programmeError"></span>
    <select name="programme_id" id="programme">
      <option value="" disabled selected>Select Programme</option>
      <?php foreach ($allProgrammes as $prog): ?>
        <option value="<?php echo (int)$prog['ProgrammeID']; ?>">
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

<script>
document.getElementById("interestForm").addEventListener("submit", function(e){
    e.preventDefault();

    let name = document.getElementById("name");
    let email = document.getElementById("email");
    let programme = document.getElementById("programme");

    let nameError = document.getElementById("nameError");
    let emailError = document.getElementById("emailError");
    let programmeError = document.getElementById("programmeError");

    let emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

    nameError.innerHTML = "";
    emailError.innerHTML = "";
    programmeError.innerHTML = "";
    name.classList.remove("error-border");
    email.classList.remove("error-border");
    programme.classList.remove("error-border");

    let valid = true;

    if(name.value.trim() == ""){
        nameError.innerHTML = "Mandatory";
        name.classList.add("error-border");
        valid = false;
    }

    if(email.value.trim() == ""){
        emailError.innerHTML = "Mandatory";
        email.classList.add("error-border");
        valid = false;
    } else if(!emailPattern.test(email.value)){
        emailError.innerHTML = "Invalid Email (example@gmail.com)";
        email.classList.add("error-border");
        valid = false;
    }

    if(programme.value == ""){
        programmeError.innerHTML = "Mandatory";
        programme.classList.add("error-border");
        valid = false;
    }

    if(valid){
        this.submit();
    }
});
</script>

</body>
</html>
