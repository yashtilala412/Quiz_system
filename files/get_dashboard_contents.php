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
    $ip = $_SERVER['REMOTE_ADDR'];
    error_log("[IP: $ip] " . $message . PHP_EOL, 3, 'log.txt');
}

logMessage("Script execution started.");

// Check for session timeout
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 1800) {
    logMessage("Session timeout. Redirecting to login.");
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}


$_SESSION['last_activity'] = time();

if (isset($_SESSION['student_details'])) {
    $data = $_SESSION['student_details'];
    logMessage("Session data found: " . $data);

    $student_data = json_decode($data);

    if ($student_data === null) {
        logMessage("Invalid session data: " . json_last_error_msg());
        echo "Invalid data";
        exit();
    }

    // Establishing database connection
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        logMessage("Database connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }
    logMessage("Database connection established.");

    // Process each student data
    foreach ($student_data as $obj) {
        $test_id = validateInput($obj->test_id);

        // Validate test ID
        if (!filter_var($test_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
            logMessage("Invalid test ID: " . $test_id);
            echo "Invalid test ID";
            exit();
        }

        logMessage("Processing test_id: " . $test_id);

        $query = "SELECT * FROM tests WHERE id = ? AND status_id IN (2)";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            logMessage("SQL error: " . $conn->error);
            error_log("SQL error: " . $conn->error, 3, "/var/log/php_errors.log");
            echo "Database error";
            exit();
        }

        $stmt->bind_param("i", $test_id);
        $queryStartTime = microtime(true);
        $stmt->execute();
        $queryEndTime = microtime(true);
        logMessage("Query execution time: " . ($queryEndTime - $queryStartTime) . " seconds.");

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
    }

    echo $testName;

} else {
    logMessage("No session data found.");
    echo "Not Found";
}

// Secure headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Content-Security-Policy: default-src 'self';");

logMessage("User IP Address: " . $_SERVER['REMOTE_ADDR']);
logMessage("User Agent: " . $_SERVER['HTTP_USER_AGENT']);

// Close database connection
$conn->close();

// Log execution time
$endTime = microtime(true);
logMessage("Script execution time: " . ($endTime - $_SERVER["REQUEST_TIME_FLOAT"]) . " seconds.");
logMessage("Script execution ended.");
?>
