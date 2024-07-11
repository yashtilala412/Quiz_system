<?php
    $id;
	include "../../database/config.php";
    if (!isset($_POST['class_name']) || !isset($_POST['extra_roll_number'])) {
        die("Invalid input");
    }
    
   
        $classes = "SELECT id FROM classes where name = '".$_POST['class_name']."' ";
        $result = mysqli_query($conn, $classes);
        $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
        $roll_number = mysqli_real_escape_string($conn, $_POST['extra_roll_number']);

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                $id  = $row['id'];
            }
              
            $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
            $stmt->bind_param("s", $class_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['id'];
            }
            
        $sql = "INSERT INTO student_data (rollno, class_id) VALUES ('".$_POST['extra_roll_number']."', $id)";

        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }

        } else {
            echo "0 results";
        }
        $check = "SELECT * FROM student_data WHERE rollno = '$roll_number'";
        $check_result = mysqli_query($conn, $check);
        if (mysqli_num_rows($check_result) > 0) {
            die("Roll number already exists");
        }
        mysqli_begin_transaction($conn);
        if (mysqli_query($conn, $sql)) {
            mysqli_commit($conn);
            echo "New record created successfully";
        } else {
            mysqli_rollback($conn);
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
                
    mysqli_close($conn);
?>