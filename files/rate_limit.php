<?php
    include '../database/config.php';
    session_start();

    // Configuration
    define('RATE_LIMIT', 5); // Number of allowed requests
    define('RATE_LIMIT_TIME_WINDOW', 60); // Time window in seconds

    // Function to get the user's IP address
    function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    // Function to check and update rate limit
    function checkRateLimit($conn, $ip) {
        $current_time = time();
        $time_window_start = $current_time - RATE_LIMIT_TIME_WINDOW;

        // Check if the IP has made requests within the time window
        $sql = "SELECT COUNT(*) as request_count, MIN(timestamp) as first_request_time FROM rate_limit WHERE ip = ? AND timestamp > ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            throw new Exception('Prepare error for rate limit check: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "si", $ip, $time_window_start);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($data['request_count'] >= RATE_LIMIT) {
            $retry_after = RATE_LIMIT_TIME_WINDOW - ($current_time - $data['first_request_time']);
            throw new Exception('Rate limit exceeded. Try again in ' . $retry_after . ' seconds.');
        } else {
            // Log the request
            $sql = "INSERT INTO rate_limit (ip, timestamp) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt === false) {
                throw new Exception('Prepare error for logging rate limit: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "si", $ip, $current_time);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    try {
        if (!isset($_POST['rollNumber']) || !isset($_POST['password'])) {
            throw new Exception('Roll number or password is missing.');
        }

        $user_ip = getUserIP();
        checkRateLimit($conn, $user_ip); // Check and update rate limit

        $student_roll_number = $_POST['rollNumber'];
        $student_password = $_POST['password'];

        // Prepare the first statement
        $sql1 = "SELECT id, role FROM student_data WHERE rollno = ?";
        $stmt1 = mysqli_prepare($conn, $sql1);
        if ($stmt1 === false) {
            throw new Exception('Prepare error for SQL1: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt1, "s", $student_roll_number);
        mysqli_stmt_execute($stmt1);
        $result1 = mysqli_stmt_get_result($stmt1);
        if ($result1 === false) {
            throw new Exception('Execute error for SQL1: ' . mysqli_error($conn));
        }
        $row1 = mysqli_fetch_assoc($result1);
        if ($row1 === null) {
            throw new Exception('No student found with the provided roll number.');
        }
        $student_id = $row1["id"];
        $role = $row1["role"];
        mysqli_stmt_close($stmt1);

        // Prepare the second statement
        $sql2 = "SELECT id, test_id, rollno, password, score, status FROM students WHERE rollno = ? AND status = 0";
        $stmt2 = mysqli_prepare($conn, $sql2);
        if ($stmt2 === false) {
            throw new Exception('Prepare error for SQL2: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt2, "s", $student_id);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        if ($result2 === false) {
            throw new Exception('Execute error for SQL2: ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($result2) > 0) {
            $row2 = mysqli_fetch_assoc($result2);
            if (password_verify($student_password, $row2['password'])) {
                unset($row2['password']); // Remove the password from the session data
                $info[] = $row2;

                echo 'CREDS_OK';
                $_SESSION['student_details'] = json_encode($info);
                $_SESSION['role'] = $role;
            } else {
                throw new Exception('Incorrect password.');
            }
        } else {
            throw new Exception('Student record not found or already processed.');
        }

        mysqli_stmt_close($stmt2);
    } catch (Exception $e) {
        if (isset($role) && $role === 'admin') {
            // Detailed error for admin
            echo json_encode(['error' => $e->getMessage()]);
        } else {
            // Generic error for students
            echo json_encode(['error' => 'An error occurred during the login process.']);
        }
        error_log("Error for user with Roll Number: $student_roll_number from IP: $user_ip - " . $e->getMessage());
    } finally {
        mysqli_close($conn);
    }
?>
