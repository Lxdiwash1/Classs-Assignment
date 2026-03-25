<?php

// Prevent direct access to this file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied');
}

// --- XSS PROTECTION ---
// Sanitize any string output to the browser
function sanitize_output($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// --- INPUT SANITIZATION ---
// Clean user input coming from forms
function sanitize_input($data) {
    return trim(htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8'));
}

// --- EMAIL VALIDATION ---
function validate_email($email) {
    if (empty($email)) return "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email address (example@gmail.com).";
    if (strlen($email) > 255) return "Email must be under 255 characters.";
    return null;
}

// --- NAME VALIDATION ---
function validate_name($name) {
    if (empty($name)) return "Name is required.";
    if (strlen($name) > 100) return "Name must be under 100 characters.";
    if (!preg_match("/^[a-zA-Z\s\-']+$/", $name)) return "Name can only contain letters, spaces, hyphens and apostrophes.";
    return null;
}

// --- ID VALIDATION ---
// Ensures any ID from URL/form is a positive integer
function validate_id($id) {
    $id = intval($id);
    if ($id <= 0) return false;
    return $id;
}

// --- SQL INJECTION PROTECTION ---
// Validates that a programme ID exists in the database
function validate_programme_exists($conn, $programme_id) {
    $stmt = $conn->prepare("SELECT ProgrammeID FROM Programmes WHERE ProgrammeID = ?");
    $stmt->bind_param("i", $programme_id);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// --- REQUEST METHOD CHECK ---
// Only allow POST requests to form handlers
function require_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: admission.php');
        exit;
    }
}

// --- REDIRECT HELPER ---
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
?>
