<?php
    include '../database/config.php';
    session_start();

    if(!isset($_SESSION['test_id']))
        header("Location: ../index.php");
    else
        $_SESSION['test_ongoing'] = "true";
?>
<html>
    <head>
        <link rel="icon" type="image/png" href="../admin/assets/img/favicon.png">
        <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/header.css">
        <link rel="stylesheet" type="text/css" href="../css/util.css">
        <link rel="stylesheet" type="text/css" href="../css/main.css">
        <link rel="stylesheet" type="text/css" href="../css/quiz.css">
        <script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="../vendor/tilt/tilt.jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>
        <style>
			.loader {
			  border: 16px solid #f3f3f3;
			  border-radius: 50%;
			  border-top: 16px solid #3498db;
			  width: 120px;
			  height: 120px;
			  -webkit-animation: spin 2s linear infinite; /* Safari */
			  animation: spin 2s linear infinite;
			}

			/* Safari */
			@-webkit-keyframes spin {
			  0% { -webkit-transform: rotate(0deg); }
			  100% { -webkit-transform: rotate(360deg); }
			}

			@keyframes spin {
			  0% { transform: rotate(0deg); }
			  100% { transform: rotate(360deg); }
			}
		</style>
    </head>
    <body>
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
                            <img src="../images/icons/logout.png" class="header-icon1" alt="ICON" onclick = 'logout()'>
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
                            <div class="col" style="padding:0px;">
                                    <div class="card" style="padding-bottom: 20px;">
                                        <div class="container">
                                                <div class="row">
                                                    <div class="col-md-12" style="padding:0px;">
                                                        <div class="container-fluid">
                                                            <div class="modal-dialog" style="max-width: 100%">
                                                            <div class="modal-content">
                                                            	<div id="loader"class="loader" style="margin-left:45%;display:gone;"></div>
                                                            	<div id="content">
	                                                                <div class="modal-header">
	                                                                    <h5><span class="label label-warning" id="qid">1</span> <span id="question"></span></h5>
	                                                                </div>
	                                                                <div class="modal-body">

		                                                                <div class="col-xs-3 col-xs-offset-5">
		                                                                </div>

		                                                                <div class="quiz" id="quiz" data-toggle="buttons">
			                                                                <label id="optionA" onclick="getSelectedItem('a')" class="element-animation1 btn btn-lg btn-primary btn-block"><span class="btn-label" ><i class="glyphicon glyphicon-chevron-right"></i></span> <input type="radio" name="q_answer" value="1" onclick="alert('Jp')"></label>
			                                                                <label id="optionB" onclick="getSelectedItem('b')" class="element-animation2 btn btn-lg btn-primary btn-block"><span class="btn-label"><i class="glyphicon glyphicon-chevron-right"></i></span> <input type="radio" name="q_answer" value="2"></label>
			                                                                <label id="optionC" onclick="getSelectedItem('c')" class="element-animation3 btn btn-lg btn-primary btn-block"><span class="btn-label"><i class="glyphicon glyphicon-chevron-right"></i></span> <input type="radio" name="q_answer" value="3"></label>
			                                                                <label id="optionD" onclick="getSelectedItem('d')" class="element-animation4 btn btn-lg btn-primary btn-block"><span class="btn-label"><i class="glyphicon glyphicon-chevron-right"></i></span> <input type="radio" name="q_answer" value="4"></label>
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
            </div>
        </section>

        <script>
            var question_data;
            var question_count = 1;
            $(document).ready(function(){
                var temp = document.getElementById('content');
                var temp1 = document.getElementById('loader');
                temp.style.display = 'none';
                temp1.style.display = 'block';

                if(Cookies.get('last_question_was_answered') == undefined || Cookies.get('last_question_was_answered') == "true"){
                    createQuestion();
                    Cookies.set('last_question_was_answered', 'false')
                }else if (Cookies.get('last_question_was_answered') == "false"){
                    //load previous question,dont fire ajax request
                    //alert("Will load previous question");
                    loadQuestion(JSON.parse(Cookies.get('last_question')));
                    question_data = JSON.parse(Cookies.get('last_question'))
                }      
            });
        
            $('.js-tilt').tilt({
                scale: 1.1
            })	

            let startTime;

function startQuestionTimer() {
    startTime = new Date();
}

function getSelectedItem(val) {
    var temp = document.getElementById('content');
    var temp1 = document.getElementById('loader');
    temp.style.display = 'none';
    temp1.style.display = 'block';

    // Disable all option buttons after selection
    var optionButtons = document.querySelectorAll('.option-button');
    optionButtons.forEach(button => button.disabled = true);

    // Calculate the time taken to answer
    var endTime = new Date();
    var timeTaken = (endTime - startTime) / 1000; // time in seconds

    Cookies.set('last_question_was_answered', 'true')

    $.ajax({
        type: 'POST',
        url: "check_answer.php",
        data: {
            'question_id': question_data.id.toString(),
            'selected_option': val.toString(),
            'score': question_data.score.toString(),
            'time_taken': timeTaken.toString() // Sending time taken with the answer
        },
        success: function(result) {
            createQuestion();

            // Re-enable the option buttons when a new question is loaded
            optionButtons.forEach(button => button.disabled = false);
        }
    });
}
function showSubmissionMessage(customMessage = "Answer submitted!", duration = 2000, color = "black", persistent = false, bgColor = "lightgray", callback = null) {
    var message = document.getElementById('submissionMessage');
    message.textContent = customMessage;
    message.style.display = 'block';
    message.style.color = color;
    message.style.backgroundColor = bgColor;
    
    if (!persistent) {
        setTimeout(() => {
            message.style.display = 'none';
            if (callback) callback();
        }, duration);
    }
}

function showSubmissionMessage(customMessage = "Answer submitted!", duration = 2000, color = "black", persistent = false, bgColor = "lightgray", callback = null, fontSize = "16px", padding = "10px", centerHorizontally = false, centerVertically = false, draggable = false, modal = false, requireUserAction = false) {
    var message = document.getElementById('submissionMessage');
    message.textContent = customMessage;
    message.style.display = 'block';
    message.style.color = color;
    message.style.backgroundColor = bgColor;
    message.style.fontSize = fontSize;
    message.style.padding = padding;

    if (!requireUserAction && !persistent) {
        setTimeout(() => {
            message.style.display = 'none';
        }, duration);
    }

    if (requireUserAction) {
        let closeButton = document.createElement('button');
        closeButton.textContent = 'Close';
        closeButton.onclick = () => {
            message.style.display = 'none';
        };
        message.appendChild(closeButton);
    }
}
function showSubmissionMessage(customMessage = "Answer submitted!", duration = 2000, color = "black", persistent = false, bgColor = "lightgray", callback = null, fontSize = "16px", padding = "10px", centerHorizontally = false, centerVertically = false, draggable = false, modal = false, requireUserAction = false, messageId = 'submissionMessage', customClass = '', language = 'en') {
    var message = document.getElementById(messageId);
    
    const messages = {
        en: "Answer submitted!",
        es: "¡Respuesta enviada!",
        fr: "Réponse soumise!",
        de: "Antwort eingereicht!"
    };

    message.textContent = customMessage || messages[language];
    message.style.display = 'block';
    message.style.color = color;
    message.style.backgroundColor = bgColor;
    message.style.fontSize = fontSize;
    message.style.padding = padding;

    // Rest of the logic remains the same
}






function showSubmissionMessage(customMessage = "Answer submitted!", duration = 2000, color = "black", persistent = false) {
    var message = document.getElementById('submissionMessage');
    message.textContent = customMessage;
    message.style.display = 'block';
    message.style.color = color;
    
    if (!persistent) {
        setTimeout(() => {
            message.style.display = 'none';
        }, duration);
    }
}



function getSelectedItem(val) {
    // Existing code...

    Cookies.set('last_question_was_answered', 'true')

    $.ajax({
        type: 'POST',
        url: "check_answer.php",
        data: {
            'question_id': question_data.id.toString(),
            'selected_option': val.toString(),
            'score': question_data.score.toString(),
            'time_taken': timeTaken.toString()
        },
        success: function(result) {
            createQuestion();
            showSubmissionMessage();
            optionButtons.forEach(button => button.disabled = false);
        }
    });
}
function highlightSelectedOption(val) {
    var options = document.querySelectorAll('.option-button');
    options.forEach(button => button.classList.remove('selected-option'));
    document.querySelector(`.option-button[data-value='${val}']`).classList.add('selected-option');
}

function getSelectedItem(val) {
    // Existing code...
    
    highlightSelectedOption(val);

    Cookies.set('last_question_was_answered', 'true')

    $.ajax({
        // Existing AJAX code...
    });
}
function playSubmitSound() {
    var audio = new Audio('submit_sound.mp3'); // Path to your audio file
    audio.play();
}

function showLoader() {
    var loader = document.getElementById('answer-loader');
    loader.style.display = 'block';
}

function hideLoader() {
    var loader = document.getElementById('answer-loader');
    loader.style.display = 'none';
}

function logAnswerSubmission(questionId, selectedOption) {
    console.log(`Question ID: ${questionId}, Selected Option: ${selectedOption}`);
}

function getSelectedItem(val) {
    // Existing code...

    logAnswerSubmission(question_data.id, val);

    Cookies.set('last_question_was_answered', 'true')

    $.ajax({
        // Existing AJAX code...
    });
}
function showError() {
    var error = document.getElementById('error-message');
    error.textContent = "There was a problem submitting your answer. Please try again.";
    error.style.display = 'block';
}

function getSelectedItem(val) {
    // Existing code...

    $.ajax({
        // Existing AJAX code...
        error: function() {
            hideLoader();
            showError();
        }
    });
}
           function createQuestion(){
                $.ajax({url: "get_question.php", success: function(result){
                    if(result === "QUESTION_SET_FINISHED"){
                        $.ajax({
                            type: 'POST',
                            url: 'end_session.php',
                            data: { 
                                'message': '0',
                            },
                            success: function(msg){
                                alert(msg);
                                Cookies.remove('last_question_was_answered');
                                Cookies.remove('last_question');
                                Cookies.set('test_submitted_status', msg.toString());
                                window.location.replace("test_finished.php");
                            }
                        });
                    }else{
                        question_data = JSON.parse(result);
                        Cookies.set('last_question', result)
                        Cookies.set('last_question_was_answered', "false")
                        loadQuestion(question_data);
                    }
                }});
            }

            function loadQuestion(question_data){
            	var temp = document.getElementById('content');
            	var temp1 = document.getElementById('loader');
            	temp.style.display = 'block';
            	temp1.style.display = 'none';

                $('#qid').text(question_count);
                $('#question').text(question_data.title);
                $('#optionA').text('A)   ' + question_data.optionA);
                $('#optionB').text('B)   ' + question_data.optionB);
                $('#optionC').text('C)   ' + question_data.optionC);
                $('#optionD').text('D)   ' + question_data.optionD);
                question_count++;
            }

            function logout(){
    if (confirm("Are you sure you want to log out?")) {
        console.log("User initiated logout");
        $('#loader').show();

        // Google Analytics event tracking
        if (typeof gtag === "function") {
            gtag('event', 'logout', {
                'event_category': 'User Actions',
                'event_label': 'User logged out'
            });
        }
        
        $.ajax({
            type: 'POST',
            url: 'end_session.php',
            data: { 
                'message': '1',
            },
            success: function(msg){
                alert(msg);
                Cookies.remove('last_question_was_answered');
                Cookies.remove('last_question');
                Cookies.set('test_submitted_status', msg.toString());
                console.log("Logout successful");
                window.location.replace("test_finished.php");
            },
            error: function(xhr, status, error){
                console.error("Error during logout: " + error);
                alert("Error occurred during logout. Please try again.");
            },
            complete: function() {
                $('#loader').hide();
            }
        });
    }
}



        </script>
    </body>
</html>