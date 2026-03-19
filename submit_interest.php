<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission.php');
    exit;
}

$name         = trim($_POST['name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$programme_id = (int)($_POST['programme_id'] ?? 0);

// Basic server-side validation
if ($name === '' || $email === '' || $programme_id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: admission.php?error=1');
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email)
    VALUES (?, ?, ?)
");
$stmt->bind_param('iss', $programme_id, $name, $email);

if ($stmt->execute()) {
    header('Location: admission.php?success=1');
} else {
    header('Location: admission.php?error=1');
}
exit;
?>
