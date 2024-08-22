<?php
session_start();

// Force HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header('Status: 301 Moved Permanently');
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        echo 'Please use HTTPS connection.';
        exit();
    }
}

// Secure session cookies
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');

include '../database/config.php';
$testName = "";

function logMessage($message) {
    error_log($message . PHP_EOL, 3, 'log.txt');
}

function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

logMessage("Script execution started.");

if (isset($_SESSION['student_details'])) {
    $data = $_SESSION['student_details'];
    logMessage("Session data found: " . $data);

    $student_data = json_decode($data);
    
    if ($student_data === null) {
        logMessage("Invalid session data: " . json_last_error_msg());
        echo "Invalid data";
        exit();
    }

    foreach ($student_data as $obj) {
        $test_id = validateInput($obj->test_id);
        logMessage("Processing test_id: " . $test_id);

        $query = "SELECT * FROM tests WHERE id = ? AND status_id IN (2)";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $test_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $_SESSION['test_id'] = $row['id'];
                    $testName = $row['name'];
                    logMessage("Test found: " . $row['name']);
                }
            } else {
                logMessage("No test found for test_id: " . $test_id);
            }
            $stmt->close();
        } else {
            logMessage("Failed to prepare statement: " . $conn->error);
        }
    }

    echo $testName;
} else {
    logMessage("No session data found.");
    echo "Not Found";
}
logMessage("User IP Address: " . $_SERVER['REMOTE_ADDR']);
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 1800) {
    logMessage("Session timeout. Redirecting to login.");
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time();
$data = htmlspecialchars($_SESSION['student_details'], ENT_QUOTES, 'UTF-8');
logMessage("Sanitized session data: " . $data);
if (!isset($_SESSION['created']) || (time() - $_SESSION['created']) > 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
    logMessage("Session ID regenerated.");
}

mysqli_close($conn);
logMessage("Script execution ended.");
?>
