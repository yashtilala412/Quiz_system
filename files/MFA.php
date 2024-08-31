<?php
    include '../database/config.php';
    session_start();

    // Function to generate a random verification code
    function generateVerificationCode() {
        return rand(100000, 999999);
    }

    // Function to send the verification code via email
    function sendVerificationCode($email, $code) {
        $subject = "Your Verification Code";
        $message = "Your verification code is: $code";
        $headers = "From: no-reply@example.com";
        mail($email, $subject, $message, $headers);
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
        $sql1 = "SELECT id, role, email FROM student_data WHERE rollno = ?";
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
        $email = $row1["email"];
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
        sendVerificationCode($conn, $email, $verification_code);
        $student_id = filter_var(trim($_POST['rollno']), FILTER_SANITIZE_STRING);
$student_password = trim($_POST['password']);
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

        if (mysqli_num_rows($result2) > 0) {
            $row2 = mysqli_fetch_assoc($result2);
            if (password_verify($student_password, $row2['password'])) {
                unset($row2['password']); // Remove the password from the session data
                $info[] = $row2;

                // Generate and send the verification code
                $verification_code = generateVerificationCode();
                $_SESSION['verification_code'] = $verification_code;
                sendVerificationCode($email, $verification_code);

                echo 'VERIFICATION_CODE_SENT';
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
