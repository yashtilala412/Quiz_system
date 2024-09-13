<html>
<head>
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../css/header.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/test_finished.css">
	<link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
	<link rel="stylesheet" type="text/css" href="../vendor/css-hamburgers/hamburgers.min.css">
	<link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
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
			</div>
		</div>

		<!-- Header Mobile -->
		<div class="wrap_header_mobile">
			<!-- Logo moblie -->
			<a href="../index.php" class="logo-mobile">
				<img src="../images/icons/logo.png" alt="IMG-LOGO">
			</a>
		</div>
    </header>

	<section>
        <h5 id="test_submit_status"></h5>
	</section>

	<script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
	<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="../vendor/tilt/tilt.jquery.min.js"></script>
	<script>
			if(Cookies.get('test_submitted_status') == undefined)
				window.location.replace("../index.php");
			else{
				$('#test_submit_status').text("Test "+Cookies.get('test_submitted_status')+", You will be logged out shortly....");
			setTimeout(function() { 
				Cookies.remove('test_submitted_status');
                window.location.replace("../index.php");
           }, 3000);
			}

		$('.js-tilt').tilt({
			scale: 1.1
		})
		if (Cookies.get('test_submitted_status') == undefined) {
    window.location.replace("../index.php");
} else {
    $('#test_submit_status').text("Test " + Cookies.get('test_submitted_status') + ", You will be logged out shortly....");

    // New Confirmation Popup
    let logoutConfirm = confirm("Do you want to log out?");
    if (logoutConfirm) {
        setTimeout(function () {
            Cookies.remove('test_submitted_status');
            window.location.replace("../index.php");
        }, 3000);
    }
}

$('.js-tilt').tilt({
    scale: 1.1
});
if (Cookies.get('test_submitted_status') == undefined) {
    window.location.replace("../index.php");
} else {
    let countdown = 3;
    $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");

    // New countdown logic
    let timer = setInterval(function () {
        countdown--;
        $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
        if (countdown == 0) {
            clearInterval(timer);
            Cookies.remove('test_submitted_status');
            window.location.replace("../index.php");
        }
    }, 1000);

    // Cancel button functionality
    $('#cancel_logout').on('click', function () {
        clearInterval(timer); // Stop the countdown
        $('#test_submit_status').text("Logout canceled.");
    });

    // Auto-logout after 5 minutes
    setTimeout(function () {
        Cookies.remove('test_submitted_status');
        window.location.replace("../index.php");
    }, 5 * 60 * 1000); // 5 minutes
}

$('.js-tilt').tilt({
    scale: 1.1
});

if (Cookies.get('test_submitted_status') == undefined) {
    window.location.replace("../index.php");
} else {
    let countdown = 3;
    $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");

    // New countdown logic
    let timer = setInterval(function () {
        countdown--;
        $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
        $('#test_submit_status').css('color', countdown % 2 == 0 ? 'red' : 'black'); // Flashing color

        if (countdown == 0) {
            clearInterval(timer);
            Cookies.remove('test_submitted_status');
            window.location.replace("../index.php");
        }
    }, 1000);

    $('#cancel_logout').on('click', function () {
        clearInterval(timer);
        $('#test_submit_status').text("Logout canceled.");
    });

    setTimeout(function () {
        Cookies.remove('test_submitted_status');
        window.location.replace("../index.php");
    }, 5 * 60 * 1000);
}

$('.js-tilt').tilt({
    scale: 1.1
});

if (Cookies.get('test_submitted_status') == undefined) {
    window.location.replace("../index.php");
} else {
    let countdown = 3;
    $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");

    let timer = setInterval(function () {
        countdown--;
        $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
        $('#test_submit_status').css('color', countdown % 2 == 0 ? 'red' : 'black');
        if (countdown == 0) {
            clearInterval(timer);
            Cookies.remove('test_submitted_status');
            window.location.replace("../index.php");
        }
    }, 1000);

    $('#cancel_logout').on('click', function () {
        clearInterval(timer);
        $('#test_submit_status').text("Logout canceled.");
    });

    // Reset countdown on any activity (click)
    $(document).on('click', function () {
        clearInterval(timer);
        countdown = 3; // Reset countdown
        $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
        timer = setInterval(function () {
            countdown--;
            $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
            $('#test_submit_status').css('color', countdown % 2 == 0 ? 'red' : 'black');
            if (countdown == 0) {
                clearInterval(timer);
                Cookies.remove('test_submitted_status');
                window.location.replace("../index.php");
            }
        }, 1000);
    });

    setTimeout(function () {
        Cookies.remove('test_submitted_status');
        window.location.replace("../index.php");
    }, 5 * 60 * 1000);
}

$('.js-tilt').tilt({
    scale: 1.1
});

if (Cookies.get('test_submitted_status') == undefined) {
    window.location.replace("../index.php");
} else {
    let countdown = 3;
    $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");

    let audio = new Audio('logout-sound.mp3'); // Sound to play before logout

    let timer = setInterval(function () {
        countdown--;
        $('#test_submit_status').html("Test " + Cookies.get('test_submitted_status') + ", You will be logged out in " + countdown + " seconds.... <button id='cancel_logout'>Cancel</button>");
        $('#test_submit_status').css('color', countdown % 2 == 0 ? 'red' : 'black');

        if (countdown == 1) {
            audio.play(); // Play sound 1 second before logout
        }

        if (countdown == 0) {
            clearInterval(timer);
            Cookies.remove('test_submitted_status');
            window.location.replace("../index.php");
        }
    }, 1000);

$('.js-tilt').tilt({
    scale: 1.1
});

	</script>
</body>
</html>