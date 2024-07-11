<?php
include "../../database/config.php";

if (!isset($_POST['class_name']) || !isset($_POST['extra_roll_number'])) {
    die("Invalid input");
}

$class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
$roll_number = mysqli_real_escape_string($conn, $_POST['extra_roll_number']);

$stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
$stmt->bind_param("s", $class_name);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    
    $check = "SELECT * FROM student_data WHERE rollno = '$roll_number'";
    $check_result = mysqli_query($conn, $check);
    if (mysqli_num_rows($check_result) > 0) {
        die("Roll number already exists");
    }

    $sql = "INSERT INTO student_data (rollno, class_id) VALUES ('$roll_number', $id)";
    
    mysqli_begin_transaction($conn);
    if (mysqli_query($conn, $sql)) {
        mysqli_commit($conn);
        echo "New record created successfully";
    } else {
        mysqli_rollback($conn);
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    mail("admin@example.com", "New Student Added", "Student with roll number $roll_number added to class ID $id.");
    $log = "INSERT INTO activity_log (action) VALUES ('Added roll number $roll_number to class $class_name')";
    mysqli_query($conn, $log);
} else {
    die("Class not found");
}

$stmt->close();
mysqli_close($conn);
?>
