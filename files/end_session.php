<?php
// Secure session handling settings
session_set_cookie_params([
    'secure' => true,       // Only send cookies over HTTPS
    'httponly' => true,     // Prevent JavaScript access to session cookies
    'samesite' => 'Strict'  // Strict SameSite policy
]);
session_start();
include '../database/config.php';

// Logging function
function logAction($message) {
    file_put_contents('actions.log', "[".date("Y-m-d H:i:s")."] - $message" . PHP_EOL, FILE_APPEND);
}

// Log user's IP address
logAction("User IP: " . $_SERVER['REMOTE_ADDR']);

// Log user's User-Agent
logAction("User Agent: " . $_SERVER['HTTP_USER_AGENT']);

// Sanitize POST data
$sanitized_post = filter_var_array($_POST, FILTER_SANITIZE_STRING);

// Encrypt session data
$_SESSION['student_details'] = openssl_encrypt($temp, 'aes-256-cbc', 'encryption_key', 0, 'iv12345678901234');

// Rate limiting
if ($_SESSION['last_request'] && (time() - $_SESSION['last_request']) < 5) {
    die("Too many requests, slow down!");
}
$_SESSION['last_request'] = time();

// Track failed login attempts
$_SESSION['failed_logins'] = ($_SESSION['failed_logins'] ?? 0) + 1;

// Simple CAPTCHA verification
if ($_POST['captcha'] !== $_SESSION['captcha_code']) {
    die("CAPTCHA validation failed!");
}

// Database connection fallback
if (!$conn) {
    logAction("Database connection failed, falling back to backup.");
    $conn = new mysqli($backup_host, $username, $password, $dbname);
}

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Secure password hashing
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Enable HSTS
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
}
$_SESSION['last_activity'] = time();

// Sanitize session data
$temp = filter_var($_SESSION['student_details'], FILTER_SANITIZE_STRING);

// Implement CSP header
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

// Secure token generation
$reset_token = bin2hex(random_bytes(16));

// Secure session cookie settings
ini_set('session.cookie_secure', 'On');
ini_set('session.cookie_httponly', 'On');

// Email validation
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    die("Invalid email address");
}

// Account lockout mechanism
if ($_SESSION['failed_logins'] > 5) {
    die("Account locked due to too many failed login attempts.");
}

// Encrypt sensitive data
$encrypted_data = openssl_encrypt($sensitive_data, 'aes-256-cbc', 'encryption_key', 0, 'iv12345678901234');
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
// Time-based failed login limit
if (isset($_SESSION['failed_logins']) && $_SESSION['failed_logins'] > 5 && time() - $_SESSION['last_attempt_time'] < 900) {
    die("Account locked due to too many failed login attempts. Try again later.");
}
// Sanitize email input for database query
setcookie('session', $session_id, [
    'expires' => time() + 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict' // Prevent cross-site requests
]);
if (!filter_var($email_sanitized, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email format.');
}
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
echo '<link href="styles.css" integrity="sha384-abc123" crossorigin="anonymous">';
header('X-Content-Type-Options: nosniff');


// Prevent clickjacking
header('X-Frame-Options: DENY');

// Validate and decode student details from session
$temp = $_SESSION['student_details'] ?? '';
$student_data = json_decode($temp);

if ($student_data && is_array($student_data)) {
    $log_file = 'student_updates.log';
file_put_contents($log_file, "Updated student ID: $student_id\n", FILE_APPEND);
if (!$stmt->execute()) {
    error_log("Error updating student ID $student_id: " . $stmt->error);
}
$stmt = $conn->prepare("UPDATE students SET status = 1 WHERE id IN (" . implode(',', array_fill(0, count($student_data), '?')) . ")");
$ids = array_map(fn($obj) => filter_var($obj->id, FILTER_VALIDATE_INT), $student_data);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$conn->begin_transaction();
try {
    foreach($student_data as $obj) {
        // Same update logic here
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
}
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    die("Session timed out.");
}
$_SESSION['last_activity'] = time();

    // Using prepared statements for secure database interaction
    $stmt = $conn->prepare("UPDATE students SET status = 1 WHERE id = ?");
    foreach($student_data as $obj) {
        $student_id = filter_var($obj->id, FILTER_VALIDATE_INT);
        if ($student_id) {
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// CSRF protection: Generate and validate token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate CSRF token from POST request
$csrf_token_valid = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '');

// Input validation
$message = filter_input(INPUT_POST, 'message', FILTER_VALIDATE_INT);

// Conditional response based on validation
$csrf_token_lifetime = 300; // 5 minutes
if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $csrf_token_lifetime) {
    die("CSRF token has expired. Please refresh and try again.");
}
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to perform this action.");
}

$csrf_token_lifetime = 300; // 5 minutes
if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $csrf_token_lifetime) {
    die("CSRF token has expired. Please refresh and try again.");
}

$to = 'admin@example.com';
$subject = 'Action Notification';
$message_body = 'The action was: ' . $log_message;
$headers = 'From: no-reply@example.com';

mail($to, $subject, $message_body, $headers);

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to perform this action.");
}

$csrf_token_lifetime = 300; // 5 minutes
if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $csrf_token_lifetime) {
    die("CSRF token has expired. Please refresh and try again.");
}

$ip_address = $_SERVER['REMOTE_ADDR'];
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] IP: $ip_address, Action: $log_message" . PHP_EOL, FILE_APPEND);

sleep(2); // 2 second delay before response

if (file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Action: " . $log_message . PHP_EOL, FILE_APPEND) === false) {
    die("Failed to write to log file.");
}

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("Are you sure you want to perform this action? <a href='?confirm=yes'>Yes</a>");
}

if (file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Action: " . $log_message . PHP_EOL, FILE_APPEND) === false) {
    die("Failed to write to log file.");
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("Are you sure you want to perform this action? <a href='?confirm=yes'>Yes</a>");
}

if (file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Action: " . $log_message . PHP_EOL, FILE_APPEND) === false) {
    die("Failed to write to log file.");
}

$to = 'admin@example.com';
$subject = 'Action Notification';
$message_body = 'The action was: ' . $log_message;
$headers = 'From: no-reply@example.com';

if (!mail($to, $subject, $message_body, $headers)) {
    die("Failed to send email notification.");
}

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to perform this action.");
}

$csrf_token_lifetime = 300; // 5 minutes
if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > $csrf_token_lifetime) {
    die("CSRF token has expired. Please refresh and try again.");
}

session_start();
$min_time_between_requests = 5; // 5 seconds
if (isset($_SESSION['last_request_time']) && (time() - $_SESSION['last_request_time']) < $min_time_between_requests) {
    die("Too many requests. Please wait before trying again.");
}
$_SESSION['last_request_time'] = time();

$log_file = 'log.txt';
$log_message = $message === 1 ? 'Aborted' : 'Completed';
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Action: " . $log_message . PHP_EOL, FILE_APPEND);

if ($message === 1 && $csrf_token_valid) {
    echo htmlspecialchars("Aborted", ENT_QUOTES, 'UTF-8');
} else {
    echo htmlspecialchars("Completed", ENT_QUOTES, 'UTF-8');
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Destroy session
session_destroy();
?>
