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
            
        $sql = "INSERT INTO student_data (rollno, class_id) VALUES ('".$_POST['extra_roll_number']."', $id)";

        if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }

        } else {
            echo "0 results";
        }

    mysqli_close($conn);
?>