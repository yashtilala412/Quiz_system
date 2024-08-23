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

// Check if the user is currently locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    echo json_encode(['error' => 'Too many attempts. Please try again later.']);
    exit;
}

try {
    if (!isset($_POST['verification_code'])) {
        throw new Exception('Verification code is missing.');
    }

    $entered_code = $_POST['verification_code'];
    $session_code = $_SESSION['verification_code'];

    if ($entered_code == $session_code) {
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
            throw new Exception('Too many incorrect attempts. Please try again later.');
        } else {
            throw new Exception('Invalid verification code.');
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
