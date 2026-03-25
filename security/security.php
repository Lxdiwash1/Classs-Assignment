<?php
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function sanitize_input($data) {
    return sanitize($data);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('admission.php');
    }
}

function validate_id($id) {
    $id = (int)$id;
    return $id > 0 ? $id : 0;
}

function validate_name($name) {
    if (trim($name) === '') return 'Name is required.';
    if (strlen($name) > 100) return 'Name is too long.';
    return '';
}

function validate_email($email) {
    if (trim($email) === '') return 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email address.';
    return '';
}

function validate_programme_exists($conn, $id) {
    $stmt = $conn->prepare("SELECT ProgrammeID FROM Programmes WHERE ProgrammeID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}
?>
