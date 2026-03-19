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

    // Reset errors
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
