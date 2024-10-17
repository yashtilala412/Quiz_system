<?php
// Database configurations
define("DB_HOST", "localhost");
define("DB_UNAME", "root");
define("DB_PASS", "Shyam@5000");
define("DB_DNAME", "quiz_system");

// Connect to the database
$conn = mysqli_connect(DB_HOST, DB_UNAME, DB_PASS, DB_DNAME);

// Check connection
if ($conn) {
    echo "Connection successful!";
} else {
    die("Connection failed: " . mysqli_connect_error());
}
?>
