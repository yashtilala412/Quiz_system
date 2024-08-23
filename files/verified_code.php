<?php
session_start();
header("X-Frame-Options: SAMEORIGIN"); // Prevent clickjacking
header("X-Content-Type-Options: nosniff"); // Prevent MIME-sniffing
header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // Enforce HTTPS
header("Content-Security-Policy: default-src 'self'; script-src 'self'"); // Basic CSP
session_start([
    'cookie_httponly' => true, // Prevent access via JavaScript
    'cookie_secure' => true, // Only send cookies over HTTPS
    'cookie_samesite' => 'Strict' // Restrict cross-site sending
]);
function fetchUserData($pdo, $username) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialize the attempt counter if not already set
if (!isset($_SESSION['attempt_count'])) {
    $_SESSION['attempt_count'] = 0;
}

// Define the maximum number of allowed attempts
$max_attempts = 5;

// Define the lockout duration in seconds (e.g., 60 seconds)
$lockout_duration = 60;

// Verification code validity duration in seconds (e.g., 300 seconds = 5 minutes)
$code_validity_duration = 300;

// Function to log attempts
function log_attempt($message) {
    $log_file = 'verification_attempts.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to encrypt and decrypt session data
function encrypt_session_data($data, $key) {
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_session_data($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Encryption key for session data (ensure to keep this key secure and confidential)
$encryption_key = 'your_secret_key';

// Encrypt verification code before storing in session
if (isset($_SESSION['verification_code'])) {
    $_SESSION['verification_code'] = encrypt_session_data($_SESSION['verification_code'], $encryption_key);
}

// Check if the user is currently locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    log_attempt('Attempt during lockout period.');
    echo json_encode(['error' => 'Too many attempts. Please try again later.']);
    exit;
}

// Check if the verification code has expired
if (isset($_SESSION['verification_time']) && (time() - $_SESSION['verification_time']) > $code_validity_duration) {
    log_attempt('Verification code expired.');
    echo json_encode(['error' => 'Verification code has expired.']);
    exit;
}

try {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        log_attempt('CSRF token missing or invalid.');
        throw new Exception('Invalid request.');
    }

    if (!isset($_POST['verification_code'])) {
        log_attempt('Verification code missing.');
        throw new Exception('Verification code is missing.');
    }

    // Validate the input
    $entered_code = trim($_POST['verification_code']);
    if (!preg_match('/^[A-Za-z0-9]{6,10}$/', $entered_code)) {  // Example regex for alphanumeric code
        log_attempt('Invalid input format.');
        throw new Exception('Invalid verification code format.');
    }

    // Decrypt session verification code for comparison
    $session_code = decrypt_session_data($_SESSION['verification_code'], $encryption_key);

    if ($entered_code === $session_code) {
        log_attempt('Verification successful.');
        echo 'VERIFICATION_SUCCESS';
        
        // Reset attempt count on successful verification
        $_SESSION['attempt_count'] = 0;

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        // Proceed with granting access or finalizing the login
    } else {
        // Increment attempt count
        $_SESSION['attempt_count']++;

        // Check if the maximum number of attempts has been reached
        if ($_SESSION['attempt_count'] >= $max_attempts) {
            // Set lockout time
            $_SESSION['lockout_time'] = time() + $lockout_duration;
            log_attempt('Too many incorrect attempts.');
            throw new Exception('Too many incorrect attempts. Please try again later.');
        } else {
            log_attempt('Invalid verification code.');
            throw new Exception('Invalid verification code.');
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
