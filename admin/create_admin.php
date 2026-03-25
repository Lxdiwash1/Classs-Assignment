<?php
require 'db.php';

$email    = 'admin@ltc.com';
$password = password_hash('Admin1234', PASSWORD_DEFAULT);

$stmt = $conn->prepare("DELETE FROM Admins WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO Admins (Email, Password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    echo "Admin created successfully! <a href='admin/login.php'>Go to Login</a>";
} else {
    echo "Error: " . $conn->error;
}
$stmt->close();
?>
