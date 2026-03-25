<?php
require 'db.php';
require '../security/security.php';

// Block non-POST requests
require_post();

// Sanitize inputs
$name         = sanitize_input($_POST['name'] ?? '');
$email        = sanitize_input($_POST['email'] ?? '');
$programme_id = validate_id($_POST['programme_id'] ?? 0);

// Server-side validation
$errors = [];

$nameErr = validate_name($name);
if ($nameErr) $errors[] = $nameErr;

$emailErr = validate_email($email);
if ($emailErr) $errors[] = $emailErr;

if (!$programme_id) {
    $errors[] = "Please select a valid programme.";
} elseif (!validate_programme_exists($conn, $programme_id)) {
    $errors[] = "Selected programme does not exist.";
}

if (!empty($errors)) {
    redirect('admission.php?error=1');
}

// Check for duplicate
$check = $conn->prepare("SELECT InterestID FROM InterestedStudents WHERE Email = ? AND ProgrammeID = ?");
$check->bind_param("si", $email, $programme_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    redirect('admission.php?error=duplicate');
}
$check->close();

// Insert using prepared statement (prevents SQL injection)
$stmt = $conn->prepare("INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $programme_id, $name, $email);

if ($stmt->execute()) {
    header('Location: admission.php?success=1');
} else {
    header('Location: admission.php?error=1');
}

$stmt->close();
$conn->close();
exit;
?>
