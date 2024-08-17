<?php
	if(isset($_SESSION['test_ongoing']))
		header("Location: files/quiz.php");
?>

<html>
	<head>
		<meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="keywords" content="iamrohitsuthar,Iamrohitsuthar,i am rohit suthar,Hi i am rohit suthar,Hi iamrohitsuthar,i am Rohit Suthar,I am RohitSuthar,mrrohitsuthar,rohit suthar,RohitSuthar,Rohit Suthar,rohitsuthar website,rohit suthar website,programmer,amravati,rohitsuthar,rohit suthar blog,Rohit Suthar,Rohit,Suthar,Rohit Karma,Suthar Rohit,iamrohitsuthar blog,iamrohitsuthar twitter,iamrohitsuthar instagram,iamrohitsuthar stackoverflow,iamrohitsuthar github,iamrohitsuthar linkedin,iamrohitsuthar website">
	    <title>Quizller</title>
	    <link rel="icon" type="image/png" href="admin/assets/img/favicon.png">
		<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/header.css">
		<link rel="stylesheet" type="text/css" href="css/util.css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
		<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
		<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
		<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="vendor/tilt/tilt.jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>

	</head>

	<body>
		<!-- Header -->
		<header class="header1">
			<!-- Header desktop -->
			<div class="container-menu-header">
				<div class="wrap_header">
					<!-- Logo -->
					<a href="index.php" class="logo">
						<img src="images/icons/logo.png" alt="IMG-LOGO">
					</a>

					<!-- Header Icon -->

				</div>
			</div>

			<!-- Header Mobile -->
			<div class="wrap_header_mobile">
				<!-- Logo moblie -->
				<a href="index.php" class="logo-mobile">
					<img src="images/icons/logo.png" alt="IMG-LOGO">
				</a>
			</div>
			</div>
		</header>

		<section>
			<div class="limiter">
				<div class="container-login100">
					<div class="wrap-login100">
						<div class="login100-pic js-tilt" data-tilt>
							<img src="images/img-01.png" alt="IMG">
						</div>
						<div class="login100-form validate-form">
						<span class="login100-form-title">
							Student Login
						</span>
						
						<div class="wrap-input100 validate-input">
							<input class="input100" id="studentRollNumber" type="text" name="rollNumber"
								placeholder="Roll Number" required>
							<span class="focus-input100"></span>
							<span class="symbol-input100">
								<i class="fa fa-user-circle-o" aria-hidden="true"></i>
							</span>
							<span class="error text-danger" id="empty_roll_number_field"></span>
						</div>

						<div class="wrap-input100 validate-input">
							<input class="input100" id="studentPassword" type="password" name="password"
								placeholder="Password" required>
							<span class="focus-input100"></span>
							<span class="symbol-input100">
								<i class="fa fa-lock" aria-hidden="true"></i>
							</span>
							<span class="error text-danger" id="empty_roll_password_field"></span>
						</div>

						<div class="container-login100-form-btn">
							<button class="login100-form-btn" onclick="login()">
								Login
							</button>
						</div>

						<div class="text-center p-t-136">
						</div>
</div>
					</div>
				</div>
			</div>
		</section>
		<script>
			$(document).ready(function () {

				if (Cookies.get('last_question_was_answered') != undefined) {
					Cookies.remove('last_question_was_answered');
					Cookies.remove('last_question');
				}
				if (Cookies.get('test_submitted_status') != undefined)
					Cookies.remove('test_submitted_status');	
			});


			$('.js-tilt').tilt({
				scale: 1.1
			})
function sanitizeInput(input) {
	return input.replace(/['"]/g, "");
}
$(document).ready(function () {
	$('#studentRollNumber, #studentPassword').on('keyup', function () {
		if ($('#studentRollNumber').val() && $('#studentPassword').val()) {
			$('#loginButton').prop('disabled', false);
		} else {
			$('#loginButton').prop('disabled', true);
		}
	});
});

$(document).ready(function () {
	$('#togglePassword').click(function () {
		const passwordField = $('#studentPassword');
		const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
		passwordField.attr('type', type);
		this.classList.toggle('fa-eye-slash');
	});
});

var loginAttempts = 0;
const maxAttempts = 3;
$(document).ready(function () {
	if (localStorage.getItem('rememberMe') === 'true') {
		$('#studentRollNumber').val(localStorage.getItem('rollNumber'));
		$('#studentPassword').val(localStorage.getItem('password'));
		$('#rememberMe').prop('checked', true);
	}

	$('#togglePassword').click(function () {
		const passwordField = $('#studentPassword');
		const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
		passwordField.attr('type', type);
		this.classList.toggle('fa-eye-slash');
	});
});

function logout() {
	$.ajax({
		type: 'POST',
		url: 'files/logout.php',
		success: function (response) {
			if (response === 'LOGOUT_SUCCESS') {
				window.location.replace('index.php');
			} else {
				alert('Logout failed. Please try again.');
			}
		},
		error: function () {
			alert('An error occurred during logout. Please try again.');
		}
	});
}

$(document).ready(function () {
	$('#logoutButton').click(function () {
		logout();
	});
});





		</script>
	</body>
</html>