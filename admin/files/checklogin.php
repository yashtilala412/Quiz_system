<?php
session_start();
if($_SERVER["REQUEST_METHOD"]=="POST")
{
	include "../../database/config.php";
	$username=$_POST["username"];
	$password=$_POST["password"];
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