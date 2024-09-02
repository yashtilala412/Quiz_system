// updated create_test.php
<?php
session_start();
if(!isset($_SESSION["user_id"]))
  header("Location:../index.php");

include '../../database/config.php';
include 'email_function.php';

if(isset($_POST['new_test'])) {
  $test_name = $_POST['test_name'];
  $test_subject = $_POST['subject_name'];
  $test_date = $_POST['test_date'];
  $total_questions = $_POST['total_questions'];
  $test_status = $_POST['test_status'];
  $test_class = $_POST['test_class'];
  $status_id = $class_id = -1;

  // getting status id
  $status_sql = "SELECT id FROM status WHERE name LIKE '%$test_status%'";
  $status = mysqli_query($conn, $status_sql);
  if (mysqli_num_rows($status) > 0) {
    $status_row = mysqli_fetch_assoc($status);
    $status_id = $status_row["id"];
  }

  // getting class id
  $class_sql = "SELECT id FROM classes WHERE name LIKE '%$test_class%'";
  $class_result = mysqli_query($conn, $class_sql);
  if (mysqli_num_rows($class_result) > 0) {
    $class_row = mysqli_fetch_assoc($class_result);
    $class_id = $class_row["id"];
  }

  function generateRandomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  $teacher_id = $_SESSION["user_id"];
  // creating new test
  $sql = "INSERT INTO tests(teacher_id, name, date, status_id, subject, total_questions, class_id) VALUES('$teacher_id','$test_name','$test_date','$status_id','$test_subject','$total_questions','$class_id')";
  $result = mysqli_query($conn, $sql);
  $test_id = mysqli_insert_id($conn);
  if ($result) {
    // creating student entry in students table for the test
    $sql1 = "SELECT id FROM student_data WHERE class_id = '$class_id'";
    $result1 = mysqli_query($conn, $sql1);
    $temp = 8 - strlen($test_id);
    while ($row1 = mysqli_fetch_assoc($result1)) {
      $rollno = $row1["id"];
      $random = generateRandomString($temp);
      $random = $random . $test_id;
      $sql2 = "INSERT INTO students(test_id, rollno, password, score, status) VALUES ('$test_id','$rollno','$random',0,0)";
      $result2 = mysqli_query($conn, $sql2);
    }
    $test_name = mysqli_real_escape_string($conn, htmlspecialchars(trim($test_name)));
$test_date = mysqli_real_escape_string($conn, htmlspecialchars(trim($test_date)));
// Repeat for other variables
mysqli_begin_transaction($conn);
try {
    // Your existing code for inserting test and student entries
    mysqli_commit($conn);
} catch (Exception $e) {
    mysqli_rollback($conn);
    // Handle the error
    echo "Error: " . $e->getMessage();
}
$random = password_hash(generateRandomString($temp) . $test_id, PASSWORD_DEFAULT);
error_log("Test created with ID: $test_id by teacher ID: $teacher_id", 3, "/var/log/test_app.log");


    // Send email notification to teacher
    $teacher_email = $_SESSION["user_email"];
    $subject = "New Test Created: $test_name";
    $body = "Dear Teacher,<br><br>A new test titled '$test_name' has been created.<br><br>Best Regards,<br>Your Test Application";
    sendEmail($teacher_email, $subject, $body);

    // Send email notification to students
    $sql3 = "SELECT email FROM student_data WHERE class_id = '$class_id'";
    $result3 = mysqli_query($conn, $sql3);
    while ($row3 = mysqli_fetch_assoc($result3)) {
      $student_email = $row3["email"];
      $subject = "New Test Assigned: $test_name";
      $body = "Dear Student,<br><br>You have been assigned a new test titled '$test_name'.<br><br>Best Regards,<br>Your Test Application";
      sendEmail($student_email, $subject, $body);
    }

    header("Location: dashboard.php");
  }
}
?>
