<?php
    include '../database/config.php';
    session_start();

    try {
        if (!isset($_POST['rollNumber']) || !isset($_POST['password'])) {
            throw new Exception('Roll number or password is missing.');
        }

        $student_roll_number = $_POST['rollNumber'];
        $student_password = $_POST['password'];

        // Function to get the user's IP addressfunction getUserIP() {function getUserIP() {
            session_start();

            function getUserIP() {
                if (isset($_SESSION['user_ip'])) {
                    return $_SESSION['user_ip'];
                }
            
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $ip = trim($ipList[0]);
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
            
                if (strpos($ip, '::ffff:') === 0) {
                    $ip = substr($ip, 7);
                }
            
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ip = 'Invalid IP';
                } elseif ($ip == '127.0.0.1' || $ip == '::1') {
                    $ip = 'Localhost';
                }
            
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    echo 'Warning: You are accessing the site via a proxy.';
                }
            
                $_SESSION['user_ip'] = $ip;
            
                $logEntry = date('Y-m-d H:i:s') . " - " . $ip . PHP_EOL;
                file_put_contents('ip_log.txt', $logEntry, FILE_APPEND);
                
                return $ip;
            }
            
            
            function getCountryFromIP($ip) {
                // This is a placeholder; actual implementation would query an IP geolocation service
                return 'Unknown Country';
            }
            
            

        // Log the IP address
        $user_ip = getUserIP();
        error_log("Login attempt from IP: $user_ip with Roll Number: $student_roll_number");

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
        session_regenerate_id(true);
        $max_attempts = 5;
        $lockout_time = 900; // 15 minutes
        
        if ($_SESSION['failed_attempts'] >= $max_attempts) {
            if (time() - $_SESSION['last_attempt_time'] < $lockout_time) {
                throw new Exception('Too many failed attempts. Try again later.');
            } else {
                $_SESSION['failed_attempts'] = 0; // Reset after lockout period
            }
        }
        
        if (!password_verify($student_password, $row2['password'])) {
            $_SESSION['failed_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            throw new Exception('Incorrect password.');
        }
        $password_expiry_days = 90;
        $last_password_change = strtotime($row2['last_password_change']); // Assuming a 'last_password_change' field
        
        if (time() - $last_password_change > ($password_expiry_days * 86400)) {
            throw new Exception('Password expired. Please change your password.');
        }
        if ($role !== 'student') {
            throw new Exception('Unauthorized access.');
        }
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $timestamp = date('Y-m-d H:i:s');
        if (password_verify($student_password, $row2['password'])) {
            // Log successful login
            mysqli_query($conn, "INSERT INTO login_logs (student_id, ip_address, timestamp, status) 
                                  VALUES ('{$row2['student_id']}', '$ip_address', '$timestamp', 'success')");
        } else {
            // Log failed login
            mysqli_query($conn, "INSERT INTO login_logs (student_id, ip_address, timestamp, status) 
                                  VALUES ('{$row2['student_id']}', '$ip_address', '$timestamp', 'failed')");
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
