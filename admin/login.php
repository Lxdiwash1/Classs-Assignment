<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

require '../database/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $stmt = $conn->prepare("SELECT AdminID, Password FROM Admins WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['Password'])) {
            $_SESSION['admin_id'] = $admin['AdminID'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link rel="stylesheet" href="../html/style.css">
<style>
.login-box { max-width:380px; margin:80px auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
.login-box h2 { margin-bottom:20px; color:#0b3c6f; }
.login-box input { width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; }
.login-box button { width:100%; padding:10px; background:#0b3c6f; color:#fff; border:none; border-radius:5px; cursor:pointer; }
</style>
</head>
<body>
<div class="login-box">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
