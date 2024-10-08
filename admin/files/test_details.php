<?php
session_start();
if(!isset($_SESSION["user_id"]))
  header("Location:../index.php");
?>
<?php
  include '../../database/config.php';

  require_once('../assets/vendor/excel_reader2.php');
  require_once('../assets/vendor/SpreadsheetReader.php');

  if(isset($_POST['general_settings_update'])) {
    $test_id = $_POST['test_id'];
    $test_name = $_POST['test_name'];
    $test_subject = $_POST['subject_name'];
    $test_date = $_POST['test_date'];
    $total_questions = $_POST['total_questions'];
    $test_status = $_POST['test_status'];
    $test_class = $_POST['test_class'];
    $status_id = $class_id = -1;
    $general_settings = false;

    //getting status id
    $status_sql = "SELECT id from status where name LIKE '%$test_status%'";
    $status = mysqli_query($conn,$status_sql);
    if(mysqli_num_rows($status) > 0) {
      $status_row = mysqli_fetch_assoc($status);
      $status_id = $status_row["id"];
    }

    $sql = "UPDATE tests SET name = '$test_name', date = '$test_date', status_id = '$status_id', subject = '$test_subject', total_questions = '$total_questions' WHERE id = '$test_id'";
    $result = mysqli_query($conn,$sql);
    if($result) {
      $general_settings = true;
    }
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

  if(isset($_POST['other_settings'])) {
    $test_id = $_POST['test_id'];
    $student_roll_no = $_POST['student_roll_no'];
    $other_settings = false;
    $sql = $conn->prepare("INSERT INTO student_data(rollno,class_id) VALUES (?, NULL)");
$sql->bind_param("s", $student_roll_no);
$result = $sql->execute();
mysqli_begin_transaction($conn);
if(empty($test_id) || empty($student_roll_no)) {
  die("Test ID and Student Roll Number are required.");
}
if (!$result) {
  error_log("Failed to insert student data: " . mysqli_error($conn));
  $other_settings = false;
}
$roll_no_id = mysqli_insert_id($conn);
if(!$roll_no_id) {
    error_log("Failed to retrieve insert ID: " . mysqli_error($conn));
    mysqli_rollback($conn);
    exit;
}
$roll_no_id = mysqli_insert_id($conn);
if(!$roll_no_id) {
    error_log("Failed to retrieve insert ID: " . mysqli_error($conn));
    mysqli_rollback($conn);
    exit;
}
if(!$result1) {
  mysqli_rollback($conn);
  die("Error inserting into students table.");
}
mysqli_commit($conn);
function generateRandomString($length = 8) {
  return bin2hex(random_bytes($length / 2));
}
if($result1) {
  echo "Data inserted successfully.";
} else {
  echo "Failed to insert data. Please try again.";
}


    $temp = 8 - strlen($test_id);
    $random = generateRandomString($temp);
    $random = $random . $test_id;

    $sql = "INSERT INTO student_data(rollno,class_id) values ($student_roll_no,null)";
    $result = mysqli_query($conn,$sql);
    $roll_no_id = mysqli_insert_id($conn);
    if($result) {
      $other_settings = true;
      $sql1 = "INSERT INTO students (test_id,rollno,password,score,status) values('$test_id','$roll_no_id','$random',0,0)";
      $result1 = mysqli_query($conn, $sql1);
      if($result1) {
        $other_settings = true;
      }
      else {
        $other_settings = false;
      }
    }
  }

  if(isset($_POST['deleted'])) {
    $test_id = $_POST['test_id'];
    $delete = false;
    // Commit the transaction if deletion is successful
$delete = true;
mysqli_commit($conn);
error_log("Transaction committed successfully for test ID: $test_id");
// Backup the data before deletion
$sqlBackup = "INSERT INTO question_test_mapping_backup SELECT * FROM question_test_mapping WHERE test_id = $test_id";
$resultBackup = mysqli_query($conn, $sqlBackup);
if ($resultBackup) {
    error_log("Backup created for test ID: $test_id");
} else {
    error_log("Error creating backup: " . mysqli_error($conn));
    mysqli_rollback($conn);
    return;
}
// Send email notification
$to = 'admin@example.com';
$subject = 'Test Data Deletion Notification';
$message = "The test data for test ID $test_id has been successfully deleted.";
mail($to, $subject, $message);
error_log("Email notification sent for test ID: $test_id");
// Log number of rows deleted
$rowsDeleted = mysqli_affected_rows($conn);
error_log("$rowsDeleted rows deleted from question_test_mapping for test ID: $test_id");
$sql2 = "DELETE FROM score WHERE test_id = $test_id";
$result2 = mysqli_query($conn, $sql2);
if ($result2) {
    error_log("Deleted from score for test ID: $test_id");
} else {
    error_log("Error deleting from score: " . mysqli_error($conn));
    mysqli_rollback($conn);
    return;
}
$sql3 = "DELETE FROM student_data WHERE test_id = $test_id";
$result3 = mysqli_query($conn, $sql3);
if ($result3) {
    error_log("Deleted from student_data for test ID: $test_id");
} else {
    error_log("Error deleting from student_data: " . mysqli_error($conn));
    mysqli_rollback($conn);
    return;
}
// Check if the user has the permission to delete
$user_id = $_SESSION['user_id'];
$sqlPermission = "SELECT role FROM users WHERE user_id = $user_id";
$resultPermission = mysqli_query($conn, $sqlPermission);
$role = mysqli_fetch_assoc($resultPermission)['role'];
if ($role !== 'admin') {
    error_log("User $user_id does not have permission to delete test ID: $test_id");
    return;
}
// Log the deletion in the logs table
$sqlLog = "INSERT INTO deletion_logs (test_id, deleted_by, timestamp) VALUES ($test_id, $user_id, NOW())";
$resultLog = mysqli_query($conn, $sqlLog);
if ($resultLog) {
    error_log("Deletion logged in database for test ID: $test_id");
} else {
    error_log("Error logging deletion in database: " . mysqli_error($conn));
}
// Prevent deletion if the test is archived
$sqlArchived = "SELECT archived FROM tests WHERE test_id = $test_id";
$resultArchived = mysqli_query($conn, $sqlArchived);
$archived = mysqli_fetch_assoc($resultArchived)['archived'];
if ($archived) {
    error_log("Cannot delete archived test ID: $test_id");
    return;
}
// Check for confirmation flag before proceeding
if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] !== 'yes') {
  error_log("Deletion not confirmed for test ID: $test_id");
  return;
}

    // Start transaction
    mysqli_begin_transaction($conn);

    // Log the start of deletion process
    error_log("Deletion process started for test ID: $test_id");

    $sql1 = "DELETE from question_test_mapping WHERE test_id = $test_id";
    $result1 = mysqli_query($conn, $sql1);
    if ($result1) {
        error_log("Deleted from question_test_mapping for test ID: $test_id");
    } else {
        error_log("Error deleting from question_test_mapping: " . mysqli_error($conn));
        mysqli_rollback($conn);
        error_log("Transaction rolled back");
        return;
    }

    $sql5 = "DELETE from score WHERE test_id = $test_id";
    $result5 = mysqli_query($conn, $sql5);
    if ($result5) {
        error_log("Deleted from score for test ID: $test_id");
    } else {
        error_log("Error deleting from score: " . mysqli_error($conn));
        mysqli_rollback($conn);
        error_log("Transaction rolled back");
        return;
    }

    $sql4 = "SELECT rollno from students where test_id = $test_id";
    $result4 = mysqli_query($conn, $sql4);
    if ($result4) {
        while($row4 = mysqli_fetch_assoc($result4)) {
            $rollno_id = $row4["rollno"];
            $sql3 = "DELETE from student_data WHERE id = '$rollno_id' AND class_id IS NULL";
            $result3 = mysqli_query($conn, $sql3);
            if ($result3) {
                error_log("Deleted from student_data for rollno ID: $rollno_id where class_id is NULL");
            } else {
                error_log("Error deleting from student_data: " . mysqli_error($conn));
                mysqli_rollback($conn);
                error_log("Transaction rolled back");
                return;
            }
        }
    } else {
        error_log("Error fetching roll numbers from students: " . mysqli_error($conn));
        mysqli_rollback($conn);
        error_log("Transaction rolled back");
        return;
    }
    
    // Commit the transaction if all queries were successful
    mysqli_commit($conn);
    error_log("Transaction committed successfully");
}
if(isset($_POST['deleted'])) {
  $test_id = $_POST['test_id'];
  $user_id = $_SESSION['user_id'];

  // Validate test_id
  if (!is_numeric($test_id)) {
      error_log("Invalid test_id: $test_id");
      echo "Invalid test ID";
      return;
  }

  // Start transaction
  mysqli_begin_transaction($conn);

  // Delete from question_test_mapping
  $sql1 = "DELETE from question_test_mapping WHERE test_id = $test_id";
  $result1 = mysqli_query($conn, $sql1);
  $deleted_question_test = mysqli_affected_rows($conn);
  error_log("Deleted $deleted_question_test rows from question_test_mapping for test ID: $test_id");

  // (Repeat for other deletions...)
}
if(isset($_POST['deleted'])) {
  $test_id = $_POST['test_id'];
  $user_id = $_SESSION['user_id'];
  $user_role = $_SESSION['role']; // Assuming user role is stored in session

  // Ensure only admins can delete
  if ($user_role !== 'admin') {
      error_log("Unauthorized deletion attempt by user $user_id");
      echo "You do not have permission to delete this test.";
      return;
  }

  // Validate test_id
  if (!is_numeric($test_id)) {
      error_log("Invalid test_id: $test_id");
      echo "Invalid test ID";
      return;
  }

  // Start transaction
  mysqli_begin_transaction($conn);

  // (Deletion queries...)
}






  if(isset($_POST['deleted'])) {
    $test_id = $_POST['test_id'];
    $delete = false;
    
    // Log the start of deletion process
    error_log("Deletion process started for test ID: $test_id");

    $sql1 = "DELETE from question_test_mapping WHERE test_id = $test_id";
    $result1 = mysqli_query($conn, $sql1);
    error_log("Deleted from question_test_mapping for test ID: $test_id");

    $sql5 = "DELETE from score WHERE test_id = $test_id";
    $result5 = mysqli_query($conn, $sql5);
    error_log("Deleted from score for test ID: $test_id");

    $sql4 = "SELECT rollno from students where test_id = $test_id";
    $result4 = mysqli_query($conn, $sql4);
    while($row4 = mysqli_fetch_assoc($result4)) {
        $rollno_id = $row4["rollno"];
        $sql3 = "DELETE from student_data WHERE id = '$rollno_id' AND class_id IS NULL";
        $result3 = mysqli_query($conn, $sql3);
        error_log("Deleted from student_data for rollno ID: $rollno_id where class_id is NULL");
    }
    
    // Log the end of deletion process
    error_log("Deletion process completed for test ID: $test_id");
}

    $sql2= "DELETE from students WHERE test_id = $test_id";
    $result2 = mysqli_query($conn,$sql2);
    $sql= "DELETE from tests WHERE id = $test_id";
    $result = mysqli_query($conn,$sql);
    if($result) {
      $delete = true;
    }
  

  if(isset($_POST['test_id'])) {
    $test_id = $_POST['test_id'];
    $sql = "SELECT * from tests where id = $test_id";
    $result = mysqli_query($conn,$sql);
    $test_details = mysqli_fetch_assoc($result);
    $status_id = $test_details["status_id"];
    $class_id = $test_details["class_id"];
    
    $sql1 = "SELECT name from status where id = $status_id";
    $result1 = mysqli_query($conn,$sql1);
    $gen = mysqli_fetch_assoc($result1);
    $status = $gen["name"];

    $sql2 = "SELECT name from classes where id = $class_id";
    $result2 = mysqli_query($conn,$sql2);
    $gen1 = mysqli_fetch_assoc($result2);
    $class = $gen1["name"];    
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="robots" content="noindex">
  <meta http-equiv="pragma" content="no-cache" />
  <meta http-equiv="expires" content="-1" />
  <title>
    <?=ucfirst(basename($_SERVER['PHP_SELF'], ".php"));?>
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <!-- <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- CSS Files -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/now-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
  <!-- <link type="text/css" rel="stylesheet" href="http://jqueryte.com/css/jquery-te.css" charset="utf-8"> -->
  <link type="text/css" rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" charset="utf-8">
  <link href="../assets/css/main.css" rel="stylesheet" />
</head>

<body class="">
  <div class="wrapper ">
    <!-- sidebar -->
    <?php
      include "sidebar.php";
    ?>
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-transparent  navbar-absolute bg-primary fixed-top">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-toggle">
              <button type="button" class="navbar-toggler">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </button>
            </div>
            <a class="navbar-brand" href="#pablo">Test Details</a>
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
          </button>
          <?php include "navitem.php"; ?>
        </div>
      </nav>
      <!-- End Navbar -->
      <div class="panel-header panel-header-sm">
      </div>
      <div class="content" style="min-height: auto;">
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="title">General Settings</h5>
              </div>
              <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                  <input type="hidden" name="general_settings_update">
                  <input type="hidden" name="test_id" value="<?= $test_id;?>">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Test name (title)</label>
                        <input type="text" class="form-control" name="test_name" placeholder="Test name" value="<?= $test_details["name"];?>"/>
                      </div>
                      <div class="form-group">
                        <label>Subject name</label>
                        <input type="text" class="form-control" name="subject_name" placeholder="Subject name" value="<?= $test_details["subject"];?>"/>
                      </div>
                      <div class="form-group">
                        <label>Test date</label>
                          <input type="date" class="form-control" name="test_date" placeholder="Test Date" value="<?= $test_details["date"];?>" required/>
                      </div>
                      <div class="form-group">
                        <label>Total Questions count</label>
                          <input type="number" class="form-control" name="total_questions" placeholder="Total Questions count" value="<?= $test_details["total_questions"];?>" required/>
                      </div>
                      <div class="form-group">
                        <select id="options" name="test_status" class="btn-round" required style="width:100%;">
                            <option selected="true" value="" disabled="disabled">Select test status</option>
                            <?php

                              $sql = "select * from status where id IN(1,2)";
                              $result = mysqli_query($conn,$sql);
                              while($row = mysqli_fetch_assoc($result)) {
                                ?>

                                <option value="<?= $row["name"];?>" <?php if($status == $row["name"]) echo "selected"; ?> ><?= $row["name"];?></option>

                                <?php
                              }
                            ?>                             
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row center-element">
                    <div class="col-md-8">
                      <div class="form-group">
                        <button class="btn btn-primary btn-block btn-round">UPDATE</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="title">Other Settings</h5>
              </div>
              <div class="card-body">
                <div class="row">

                  <form id="form-completed" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <input type="hidden" name="completed">
                    <input type="hidden" name="test_id" value="<?= $test_id;?>">
                  </form>

                  <div class="col-md-6">
                    <div class="form-group">
                      <button class="btn btn-primary btn-block" onclick="completed()">MAKE AS COMPLETED</button>
                    </div>
                  </div>            
                  
                  <form id="form-deleted" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <input type="hidden" name="deleted">
                    <input type="hidden" name="test_id" value="<?= $test_id;?>">
                  </form>

                  <div class="col-md-6">
                    <div class="form-group">
                      <button class="btn btn-primary btn-block" onclick="deleted()">DELETE TEST</button>
                    </div>  
                  </div>        
                </div>

                <form id="form-student-data" method="POST" action="student_test_credentials.php">
                    <input type="hidden" name="test_id" value="<?= $test_id;?>">
                </form>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <button class="btn btn-primary btn-block" onclick="student_data()">GET STUDENT DATA</button>
                    </div>
                  </div>   
                </div>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                  <input type="hidden" name="other_settings">
                  <input type="hidden" name="test_id" value="<?= $test_id;?>">
                  <div class="form-group" style="margin-top:10px;">
                    <label>Add guest student to test</label>
                    <input type="text" class="form-control" name="student_roll_no" placeholder="Student Roll number"/>
                  </div>

                  <div class="row center-element">
                    <div class="col-md-8">
                      <div class="form-group">
                        <button class="btn btn-primary btn-block">ADD</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="content" style="min-height: auto;">
        <div class="row">                      
          <div class="col-md-12">
            <div class="card" style="min-height:400px;">
              <div class="card-header">
                <div class="row">
                  <div class="col-md-4">
                    <h5 class="title">Test Questions</h5>
                  </div>
                  <form id="form-add-questions" method="POST" action="add_question.php">
                    <input type="hidden" name="test_id" value="<?= $test_id;?>">
                  </form>
                  <div class="col-md-4">
                    <button class="btn btn-primary btn-block btn-round" data-toggle="modal" data-target="#exampleModal" style="margin-top:0px;width:200px !important;float:right !important;">UPLOAD</button>
                  </div>

                  <div class="col-md-4">
                    <button class="btn btn-primary btn-block btn-round" onclick="redirect_to_add_question()" style="margin-top:0px;width:200px !important;float:right !important;">ADD NEW QUESTION</button>
                  </div>
                </div>  
              </div>
              <div class="card-body">
                  <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SERIAL NO</th>
                            <th>Question title</th>
                            <th>Option (A)</th>
                            <th>Option (B)</th>
                            <th>Option (C)</th>
                            <th>Option (D)</th>
                            <th>Correct Option</th>
                            <th>Score</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                          $sql = "select question_id from question_test_mapping where test_id = $test_id";
                          $result = mysqli_query($conn,$sql);
                          $i = 1;
                          while($row = mysqli_fetch_assoc($result)) {
                            $question_id = $row["question_id"];
                            $sql1 = "select * from Questions where id = $question_id";
                            $result1 = mysqli_query($conn,$sql1);
                            $row1 = mysqli_fetch_assoc($result1);
                            ?>
                            <tr id = "<?= $row1["id"]; ?>">
                              <input type="hidden" id="question_id" value="<?= $row1["id"]; ?>">
                              <td><?= $i;?></td>
                              <td><?= $row1["title"];?></td>
                              <td><?= $row1["optionA"];?></td>
                              <td><?= $row1["optionB"];?></td>
                              <td><?= $row1["optionC"];?></td>
                              <td><?= $row1["optionD"];?></td>
                              <td><?= $row1["correctAns"];?></td>
                              <td><?= $row1["score"];?></td>
                              <td><button id="delete" name="delete" class="btn btn-primary btn-block btn-round" onclick="delete_question('<?= $row1["id"]; ?>','<?php echo $test_id; ?>')">DELETE</button></td>
                            </tr>

                          <?php
                          $i++;
                          }    
                        ?>

                    </tbody>
                  </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <form id="form-file-upload" name="form-file-upload" method="POST" action="file_upload.php" enctype="multipart/form-data">
          <input type="hidden" name="file_upload">
          <input type="hidden" name="test_id" id ="test_id" value="<?= $test_id; ?>">
          <input type="hidden" name="tmp_name" id="tmp_name">
          <input type="hidden" name="type" id="type"> 
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Select spreadsheet to import</h5>
            </div>
            <div class="modal-body">
              <p><b>The spreadsheet column should contain (without header):</b> <br> Question, Option A, Option B, Option C, Option D, Correct Option, Score.</p>
              <p><b>Accepted file formats are:</b> .xls, .xlsx and .ods</p>
              <input type="file" name="file" id="file" accept=".xls,.xlsx,.ods">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onclick="file_upload_submit()">Upload</button>
            </div>
          </div>
        </form>
        </div>
      </div>


      <!-- footer -->
      <?php
        include "footer.php";
      ?>
    </div>
  </div>
<!--   Core JS Files   -->
<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<!--  Notifications Plugin    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/now-ui-dashboard.min.js?v=1.1.0" type="text/javascript"></script>
<!-- <script src="http://jqueryte.com/js/jquery-te-1.4.0.min.js"></script> -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<script>
    function redirect_to_add_question() {
      document.getElementById("form-add-questions").submit();
    }

    $(document).ready(function() {
        $('#example').DataTable();
    });

    function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("form-completed").submit();
  }
}

function deleted() {
  if (confirm("Are you sure you want to delete this entry?")) {
    document.getElementById("form-deleted").submit();
  }
}

function student_data() {
  if (confirm("Are you sure you want to submit the student data?")) {
    document.getElementById("form-student-data").submit();
  }
}

function file_upload_submit() {
  if (confirm("Are you sure you want to upload this file?")) {
    document.getElementById("form-file-upload").submit();
  }
}

function delete_question(temp, testid) {
  var temp1 = document.getElementById(temp);
  if (confirm("Are you sure you want to delete this question?")) {
    temp1.style.display = 'none';
    $.ajax({
      type: 'POST',
      url: 'delete_question.php',
      data: {
        'question_id': temp,
        'test_id': testid,
      },
      success: function (response) {
        // Additional success handling can be added here
      },
      error: function (xhr, status, error) {
        alert("An error occurred while deleting the question: " + error);
      }
    });
  }
}
function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true; // Disable the button
    document.getElementById("form-completed").submit();
  }
}

function deleted() {
  if (confirm("Are you sure you want to delete this entry?")) {
    document.getElementById("delete-btn").disabled = true; // Disable the button
    document.getElementById("form-deleted").submit();
  }
}

function student_data() {
  if (confirm("Are you sure you want to submit the student data?")) {
    document.getElementById("student-data-btn").disabled = true; // Disable the button
    document.getElementById("form-student-data").submit();
  }
}

function file_upload_submit() {
  if (confirm("Are you sure you want to upload this file?")) {
    document.getElementById("upload-btn").disabled = true; // Disable the button
    document.getElementById("form-file-upload").submit();
  }
}

function showLoadingSpinner() {
  document.getElementById("loading-spinner").style.display = "block";
}

function hideLoadingSpinner() {
  document.getElementById("loading-spinner").style.display = "none";
}

function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true;
    showLoadingSpinner(); // Show loading spinner
    document.getElementById("form-completed").submit();
  }
}

function deleted() {
  if (confirm("Are you sure you want to delete this entry?")) {
    document.getElementById("delete-btn").disabled = true;
    showLoadingSpinner(); // Show loading spinner
    document.getElementById("form-deleted").submit();
  }
}

// Similar changes for other functions...

function clearFormFields(formId) {
  document.getElementById(formId).reset();
}

function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true;
    showLoadingSpinner();
    document.getElementById("form-completed").submit();
    clearFormFields("form-completed"); // Clear form fields
  }
}

// Similar changes for other functions...

function delete_question(temp, testid) {
  var temp1 = document.getElementById(temp);
  if (confirm("Are you sure you want to delete this question?")) {
    document.getElementById("delete-question-btn").disabled = true;
    showLoadingSpinner();
    temp1.style.display = 'none';
    $.ajax({
      type: 'POST',
      url: 'delete_question.php',
      data: {
        'question_id': temp,
        'test_id': testid,
      },
      success: function (response) {
        hideLoadingSpinner();
        clearFormFields("form-deleted"); // Clear form fields
        // Additional success handling can be added here
      },
      error: function (xhr, status, error) {
        hideLoadingSpinner();
        alert("An error occurred while deleting the question: " + error);
      }
    });
  }
}
function showSuccessMessage(message) {
  var successMsgElement = document.getElementById("success-message");
  successMsgElement.innerText = message;
  successMsgElement.style.display = "block";
}

function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true;
    showLoadingSpinner();
    document.getElementById("form-completed").submit();
    clearFormFields("form-completed");
    showSuccessMessage("Form marked as completed successfully!"); // Show success message
  }
}

// Similar changes for other functions...

function showAjaxLoading() {
  document.getElementById("ajax-loading").style.display = "block";
}

function hideAjaxLoading() {
  document.getElementById("ajax-loading").style.display = "none";
}

function delete_question(temp, testid) {
  var temp1 = document.getElementById(temp);
  if (confirm("Are you sure you want to delete this question?")) {
    document.getElementById("delete-question-btn").disabled = true;
    showLoadingSpinner();
    showAjaxLoading(); // Show AJAX loading
    temp1.style.display = 'none';
    $.ajax({
      type: 'POST',
      url: 'delete_question.php',
      data: {
        'question_id': temp,
        'test_id': testid,
      },
      success: function (response) {
        hideLoadingSpinner();
        hideAjaxLoading(); // Hide AJAX loading
        clearFormFields("form-deleted");
        showSuccessMessage("Question deleted successfully!");
      },
      error: function (xhr, status, error) {
        hideLoadingSpinner();
        hideAjaxLoading(); // Hide AJAX loading
        alert("An error occurred while deleting the question: " + error);
      }
    });
  }
}
function highlightFieldWithError(fieldId) {
  var field = document.getElementById(fieldId);
  field.style.border = "2px solid red";
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function completed() {
  if (confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true;
    showLoadingSpinner();
    scrollToTop(); // Scroll to top
    document.getElementById("form-completed").submit();
    clearFormFields("form-completed");
    showSuccessMessage("Form marked as completed successfully!");
  }
}

// Similar changes for other functions...
function validateForm(formId) {
  var form = document.getElementById(formId);
  var inputs = form.getElementsByTagName("input");
  for (var i = 0; i < inputs.length; i++) {
    if (inputs[i].hasAttribute("required") && inputs[i].value === "") {
      alert("Please fill in all required fields.");
      return false;
    }
  }
  return true;
}

function autoFocusFirstInput(formId) {
  var form = document.getElementById(formId);
  var firstInput = form.querySelector("input:not([type='hidden'])");
  if (firstInput) {
    firstInput.focus();
  }
}
function disableFieldsForRole(role) {
  if (role === "viewer") {
    document.getElementById("editable-field").disabled = true; // Disable specific field
  }
}

disableFieldsForRole("viewer"); // Example usage
function logSubmissionAttempt(formId) {
  console.log("Form submission attempt: " + formId);
}

function completed() {
  logSubmissionAttempt("form-completed"); // Log submission attempt
  if (validateForm("form-completed") && confirm("Are you sure you want to mark this as completed?")) {
    document.getElementById("completed-btn").disabled = true;
    showLoadingSpinner();
    scrollToTop();
    document.getElementById("form-completed").submit();
    clearFormFields("form-completed");
    showSuccessMessage("Form marked as completed successfully!");
  }
}

// Similar changes for other functions...

// Similar changes for other functions...




</script>
<?php
//Checking if general settings updated successfully
  if($general_settings == "true")
  {
    ?>

    <script type="text/javascript">
      $.notify({
        message: 'General Settings Updated Successfully' 
      },{
        type: 'success'
      });
    </script>

    <?php
  }
  else if($general_settings == "false")
  {
    ?>

      <script type="text/javascript">
        $.notify({
          message: 'There was an error updating general settings' 
        },{
          type: 'danger'
        });
      </script>

    <?php
  }
  else {}
  
  //Checking if other settings updated successfully
  if($other_settings == "true")
  {
    ?>

    <script type="text/javascript">
      $.notify({
        message: 'Student Added Successfully' 
      },{
        type: 'success'
      });
    </script>

    <?php
  }
  else if($other_settings == "false")
  {
    ?>

      <script type="text/javascript">
        $.notify({
          message: 'There was an error in adding student' 
        },{
          type: 'danger'
        });
      </script>

    <?php
  }
  else {}

  //mark test as completed
  if($complete == "true")
  {
    ?>

    <script type="text/javascript">
      $.notify({
        message: 'Test Completed Successfully' 
      },{
        type: 'success'
      });
      var delayInMilliseconds = 1500; //1 second

      setTimeout(function() {
        window.location = 'dashboard.php';
      }, delayInMilliseconds);
    
    </script>

    <?php
  }
  else if($complete == "false")
  {
    ?>

      <script type="text/javascript">
        $.notify({
          message: 'There was an error in updating test status' 
        },{
          type: 'danger'
        });
      </script>

    <?php
  }
  else {}

  //Checking if other settings updated successfully
  if($delete == "true")
  {
    ?>

    <script type="text/javascript">
      $.notify({
        message: 'Test deleted successfully' 
      },{
        type: 'success'
      });
      var delayInMilliseconds = 1500; //1 second

      setTimeout(function() {
        window.location = 'dashboard.php';
      }, delayInMilliseconds);
    </script>

    <?php
  }
  else if($delete == "false")
  {
    ?>

      <script type="text/javascript">
        $.notify({
          message: 'There was an error in deleting test' 
        },{
          type: 'danger'
        });
      </script>

    <?php
  }
  else {}
?>

</body>
</html>