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

// Database connection fallback
if (!$conn) {
    logAction("Database connection failed, falling back to backup.");
    $conn = new mysqli($backup_host, $username, $password, $dbname);
}

// Validate and decode student details from session
$temp = $_SESSION['student_details'] ?? '';
$student_data = json_decode($temp);

if ($student_data && is_array($student_data)) {
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
// Log user's IP address
logAction("User IP: " . $_SERVER['REMOTE_ADDR']);
// Log user's User-Agent
logAction("User Agent: " . $_SERVER['HTTP_USER_AGENT']);
// Simple CAPTCHA verification
if ($_POST['captcha'] !== $_SESSION['captcha_code']) {
    die("CAPTCHA validation failed!");
}

// Conditional response based on validation
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
