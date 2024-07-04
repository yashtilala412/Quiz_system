<?php
    session_start();

    try {
        if (!isset($_POST['verification_code'])) {
            throw new Exception('Verification code is missing.');
        }

        $entered_code = $_POST['verification_code'];
        $session_code = $_SESSION['verification_code'];

        if ($entered_code == $session_code) {
            echo 'VERIFICATION_SUCCESS';
            // Proceed with granting access or finalizing the login
        } else {
            throw new Exception('Invalid verification code.');
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
?>
