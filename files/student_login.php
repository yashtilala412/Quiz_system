<?php
    include '../database/config.php';
    session_start();
    
    $student_roll_number = $_POST['rollNumber'];
    $student_password = $_POST['password'];

    // Prepare the first statement
    $sql1 = "SELECT id FROM student_data WHERE rollno = ?";
    $stmt1 = mysqli_prepare($conn, $sql1);
    if ($stmt1 === false) {
        die('Prepare error: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt1, "s", $student_roll_number);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    if ($result1 === false) {
        die('Execute error: ' . mysqli_error($conn));
    }
    $row1 = mysqli_fetch_assoc($result1);
    if ($row1 === null) {
        die('No student found with the provided roll number.');
    }
    $student_id = $row1["id"];
    mysqli_stmt_close($stmt1);

    // Prepare the second statement
    $sql2 = "SELECT id, test_id, rollno, password, score, status FROM students WHERE rollno = ? AND status = 0";
    $stmt2 = mysqli_prepare($conn, $sql2);
    if ($stmt2 === false) {
        die('Prepare error: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt2, "s", $student_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    if ($result2 === false) {
        die('Execute error: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($result2) > 0) {
        $row2 = mysqli_fetch_assoc($result2);
        if (password_verify($student_password, $row2['password'])) {
            unset($row2['password']); // Remove the password from the session data
            $info[] = $row2;

            echo 'CREDS_OK';
            $_SESSION['student_details'] = json_encode($info); 
        } else {
            echo json_encode("STUDENT_RECORD_NOT_FOUND");
        }
    } else {
        echo json_encode("STUDENT_RECORD_NOT_FOUND");
    }

    mysqli_stmt_close($stmt2);
    mysqli_close($conn);
?>
