<?php
    session_start();
    if(!isset($_SESSION['student_details']))
        header("Location: ../index.php"); 
    if(isset($_SESSION['test_ongoing']))
        header("Location: quiz.php"); 
?>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizller- Dashboard</title>
    <link rel="icon" type="image/png" href="../admin/assets/img/favicon.png">
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../css/header.css">
    <link rel="stylesheet" type="text/css" href="../css/util.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/tilt/tilt.jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>

</head>

<body>
    <!-- Header -->
    <header class="header1">
        <!-- Header desktop -->
        <div class="container-menu-header">
            <div class="wrap_header">
                <!-- Logo -->
                <a href="../index.php" class="logo">
                    <img src="../images/icons/logo.png" alt="IMG-LOGO">
                </a>

                <!-- Header Icon -->
                <div class="header-icons">
                    <a href="#" class="header-wrapicon1 dis-block">
                        <img src="../images/icons/logout.png" class="header-icon1" alt="ICON" onclick='logout()'>
                    </a>
                </div>
            </div>
        </div>

        <!-- Header Mobile -->
        <div class="wrap_header_mobile">
            <!-- Logo moblie -->
            <a href="../index.php" class="logo-mobile">
                <img src="../images/icons/logo.png" alt="IMG-LOGO">
            </a>

            <!-- Button show menu -->
            <div class="btn-show-menu">
                <!-- Header Icon mobile -->
                <div class="header-icons-mobile">
                    <a href="#" class="header-wrapicon1 dis-block">
                        <img src="../images/icons/logout.png" class="header-icon1" alt="ICON" onclick = 'logout()'>
                    </a>
                </div>
            </div>
        </div>
        </div>
    </header>
    <section>
        <div class="limiter">
            <div class="container-login100" style="display:block;">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="card" style="padding-bottom: 20px;">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12" id="row" style="display:none;">
                                                <div class="card" style="background: #ededed;margin:20px 10px 0px 10px;">
                                                    <div class="card-body">
                                                        <div class="container">
                                                            <div class="row">
                                                                <div class="col-md-8">
                                                                        <p id="test_name"></p>
                                                                </div>
                                                                <div class="col-md-4">
                                                                        <a href="quiz.php"><button type="button" class="btn btn-success" style="float:right;">Start Test</button></a>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function isEmpty(str) {
            return (!str || 0 === str.length);
        }

        $(document).ready(function () {
            $.ajax({
                type: 'POST',
                url: 'get_dashboard_contents.php',
                success: function (response) {
                    console.log('hi');
                    console.log(response);
                    console.log(response.length);
                    if(response.length > 0) {
                        console.log('not');
                        var temp = document.getElementById('row');
                        temp.style.display = 'block';
                        $('#test_name').text(response);
                    }
                }
            });
        });

        function logout() {
            $.ajax({
                type: 'POST',
                url: 'end_session.php',
                data: {
                    'message': '1',
                },
                success: function (msg) {
                    alert(msg);
                    Cookies.remove('last_question_was_answered');
                    Cookies.remove('last_question');
                    Cookies.set('test_submitted_status', msg.toString());
                    window.location.replace("test_finished.php");
                }
            });
        }


      
        <?php
    session_start();
    
    if($_SESSION['test_ongoing']  == "true"){
        echo "Test Ongoing";
        header("Location: quiz.php");
    }else if(!isset($_SESSION['student_details'])){
        echo "You are not logged in";
        header("Location: ../index.php");
    }
// Set the session timeout duration (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes
session_start();

// Update the last activity time stamp
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    // Last request was more than 30 minutes ago
    session_unset();     // Unset $_SESSION variable for the run-time 
    session_destroy();   // Destroy session data in storage
    header("Location: ../index.php"); // Redirect to login page
    exit();
}
session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    // Feature 10: Implement session regeneration for security
    session_regenerate_id(true);  // Regenerate session ID to prevent fixation

    error_log("Session timed out for user ID: " . $_SESSION['user_id'] . " at " . date("Y-m-d H:i:s"));
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$_SESSION['last_activity'] = time(); // Update last activity time stamp
session_start();

// Store the user's IP address when the test starts
if (!isset($_SESSION['user_ip'])) {
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
}
session_start();

// Check if the user's IP address matches the one stored in the session
if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();     // Unset $_SESSION variable for the run-time 
    session_destroy();   // Destroy session data in storage
    header("Location: ../index.php"); // Redirect to login page
    exit();
}
session_start();

$user_id = $_SESSION['user_id'];
$session_id = session_id();
$last_activity = date('Y-m-d H:i:s');

$conn = new mysqli($servername, $username, $password, $dbname);
$sql = "REPLACE INTO active_sessions (user_id, session_id, last_activity) VALUES ('$user_id', '$session_id', '$last_activity')";
$conn->query($sql);
$conn->close();


$_SESSION['last_activity'] = time(); // Update last activity time stamp

    function createCard(array $row) { ?>
  
<?php } ?>

    </script>
</body>

</html>