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
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [IP: $ip] [User Agent: $userAgent] " . $message . PHP_EOL, 3, 'log.txt');
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
    $data = validateInput($_SESSION['student_details']);
    logMessage("Session data found: " . $data);
} else {
    logMessage("No session data found for student_details.");
}
if (isset($_SESSION['student_details'])) {
    $data = filter_var($_SESSION['student_details'], FILTER_SANITIZE_STRING);
    logMessage("Session data found: " . $data);
}
logMessage("Session ID: " . session_id());
if (!session_destroy()) {
    logMessage("Failed to destroy session.");
}
if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 1800) {
    logMessage("Session timeout. Redirecting to login.php.");
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
session_start();
logMessage("Session started successfully.");
session_unset();
logMessage("Session data unset.");
function logMessage($message) {
    $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : 'Unknown IP';
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [IP: $ip] [User Agent: $userAgent] " . $message . PHP_EOL, 3, 'log.txt');
}
if (!isset($_SESSION['logged_messages'])) {
    $_SESSION['logged_messages'] = [];
}
if (!in_array($message, $_SESSION['logged_messages'])) {
    logMessage($message);
    $_SESSION['logged_messages'][] = $message;
}
if (isset($_SESSION['creation_time']) && (time() - $_SESSION['creation_time']) > 86400) {
    logMessage("Session exceeded maximum lifetime. Redirecting to login.");
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['creation_time'])) {
    $_SESSION['creation_time'] = time();
    logMessage("Session creation time: " . date('Y-m-d H:i:s', $_SESSION['creation_time']));
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    logMessage("User inactive for more than 10 minutes.");
}
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    logMessage("Potential session hijacking detected. Terminating session.");
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    logMessage("IP address mismatch. Possible session hijacking.");
}
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} else {
    $inactive_time = time() - $_SESSION['last_activity'];
    logMessage("User inactive for " . $inactive_time . " seconds.");
}
session_regenerate_id(true);
logMessage("Session ID regenerated.");
session_regenerate_id(false);
logMessage("Session ID rejected.");
if ($loginSuccess) {
    logMessage("User successfully logged in.");
    if (!$loginSuccess) {
        logMessage("Failed login attempt.");
    }
}    

    // Establishing database connection
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        logMessage("Database connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }
    logMessage("Database connection established.");

    if (isset($_SESSION['student_details'])) {
        $data = validateInput1($_SESSION['student_details']);
        logMessage("Session data found: " . $data);
    }
    session_set_cookie_params([
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    logMessage("Session cookie parameters set to secure, httponly, and samesite=Strict.");
    
    

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
