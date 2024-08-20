<?php
session_start();
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	include "../../database/config.php";
	$username=$_POST["username"];
	$password=$_POST["password"];
	$password=$_POST["password"];
$enc_password = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$row = mysqli_fetch_assoc($res);
if ($row && password_verify($password, $row["password"])) {
    echo "success";
    $_SESSION["user_id"] = $row["id"];
} else {
    echo "fail";
}
$attempts = $_SESSION['attempts'] ?? 0;
if ($attempts >= 5) {
    echo "Too many login attempts. Please try again later.";
    exit;
}
if ($row && password_verify($password, $row["password"])) {
    echo "success";
    $_SESSION["user_id"] = $row["id"];
    $_SESSION['attempts'] = 0;
} else {
    echo "fail";
}
$ip_address = $_SERVER['REMOTE_ADDR'];
// Log IP to the database or a file
$_SESSION['last_activity'] = time();
if (time() - $_SESSION['last_activity'] > 1800) { // 30 minutes
    session_unset();
    session_destroy();
}
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
if ($_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
}
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
if ($row && password_verify($password, $row["password"])) {
    $_SESSION["user_id"] = $row["id"];
    $_SESSION['attempts'] = 0;
    header("Location: /dashboard.php");
    exit;
}
$login_status = ($row && password_verify($password, $row["password"])) ? 'success' : 'fail';
$log_sql = "INSERT INTO login_attempts (email, ip_address, status, timestamp) VALUES (?, ?, ?, NOW())";
$log_stmt = $conn->prepare($log_sql);
$log_stmt->bind_param("sss", $username, $ip_address, $login_status);
$log_stmt->execute();
if ($row && password_verify($password, $row["password"])) {
    $_SESSION["user_id"] = $row["id"];
    $_SESSION['attempts'] = 0;
    $user_email = $row["email"];
    mail($user_email, "New Login Detected", "A new login to your account was detected from IP: $ip_address");
    header("Location: /dashboard.php");
    exit;
}
// After successful password verification
$otp = rand(100000, 999999); // Generate OTP
$_SESSION['otp'] = $otp;
// Send OTP to the user via email or SMS
header("Location: /verify_otp.php");
exit;
if ($_SESSION['attempts'] >= 5) {
    $lock_sql = "UPDATE teachers SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email = ?";
    $lock_stmt = $conn->prepare($lock_sql);
    $lock_stmt->bind_param("s", $username);
    $lock_stmt->execute();
    echo "Account locked due to too many failed login attempts. Please try again later.";
    exit;
}

$_SESSION['attempts'] = $attempts + 1;

	$enc_password=$password;
	$sql="SELECT * from teachers where email='$username' AND password='$enc_password'";
	$res=mysqli_query($conn,$sql);
	if(mysqli_num_rows($res) == 1)
	{
		echo "success";
		//if login successful then initialize the session
		$row = mysqli_fetch_assoc($res);
		$_SESSION["user_id"] = $row["id"];
	}
	else
	{
		echo "fail";
	}
}
?>