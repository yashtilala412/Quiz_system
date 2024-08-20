<?php
session_start();

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../database/config.php";

    $username = $_POST["username"];
    $password = $_POST["password"];

    // Log the user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check if the account is locked due to too many failed login attempts
    $stmt = $conn->prepare("SELECT locked_until FROM teachers WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['locked_until'] && strtotime($row['locked_until']) > time()) {
            echo "Account locked due to too many failed login attempts. Please try again later.";
            exit;
        }
    }

    // Check login attempts and increment counter
    $attempts = $_SESSION['attempts'] ?? 0;
    if ($attempts >= 5) {
        echo "Too many login attempts. Please try again later.";
        exit;
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    // Verify password and log the result
    $row = $res->fetch_assoc();
    if ($row && password_verify($password, $row["password"])) {
        // Successful login
        $_SESSION["user_id"] = $row["id"];
        $_SESSION['attempts'] = 0;

        // Reset login attempts
        $log_status = 'success';

        // Check session timeout and user-agent to prevent hijacking
        $_SESSION['last_activity'] = time();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Send email notification on new login
        $user_email = $row["email"];
        mail($user_email, "New Login Detected", "A new login to your account was detected from IP: $ip_address");

        // Redirect on successful login
        header("Location: /dashboard.php");
        exit;
    } else {
        // Failed login
        $_SESSION['attempts'] = $attempts + 1;
        $log_status = 'fail';

        // Check if the account should be locked
        if ($_SESSION['attempts'] >= 5) {
            $lock_sql = "UPDATE teachers SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email = ?";
            $lock_stmt = $conn->prepare($lock_sql);
            $lock_stmt->bind_param("s", $username);
            $lock_stmt->execute();
            echo "Account locked due to too many failed login attempts. Please try again later.";
            exit;
        }

        echo "fail";
    }

    // Log login attempts
    $log_sql = "INSERT INTO login_attempts (email, ip_address, status, timestamp) VALUES (?, ?, ?, NOW())";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("sss", $username, $ip_address, $log_status);
    $log_stmt->execute();
}
?>
