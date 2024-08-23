<?php
session_start();

// Initialize the attempt counter if not already set
if (!isset($_SESSION['attempt_count'])) {
    $_SESSION['attempt_count'] = 0;
}

// Define the maximum number of allowed attempts
$max_attempts = 5;

// Define the lockout duration in seconds (e.g., 60 seconds)
$lockout_duration = 60;

// Function to log attempts
function log_attempt($message) {
    $log_file = 'verification_attempts.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if the user is currently locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    log_attempt('Attempt during lockout period.');
    echo json_encode(['error' => 'Too many attempts. Please try again later.']);
    exit;
}

try {
    if (!isset($_POST['verification_code'])) {
        log_attempt('Verification code missing.');
        throw new Exception('Verification code is missing.');
    }

    $entered_code = $_POST['verification_code'];
    $session_code = $_SESSION['verification_code'];

    if ($entered_code == $session_code) {
        log_attempt('Verification successful.');
        echo 'VERIFICATION_SUCCESS';
        
        // Reset attempt count on successful verification
        $_SESSION['attempt_count'] = 0;
        
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
