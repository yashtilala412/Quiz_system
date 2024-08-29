<?php
session_start();
include '../database/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
}

if(!isset($_SESSION['test_id'])){
    header("Location: ../index.php");
}

$test_id = $_SESSION['test_id'];

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    if (!isset($_SESSION['question_IDS_fetched'])) {
        $result = mysqli_query($conn, "SELECT question_id FROM question_test_mapping WHERE test_id = '" . $test_id . "' ");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $question_ids[] = $row;
            }
            shuffle($question_ids); // Shuffle the questions
            $_SESSION['question_IDS_fetched'] = $question_ids;
            $_SESSION['question_counter'] = 0;
            getQuestion($conn, true);
        }
    } else {
        if ($_SESSION['question_counter'] >= sizeof($_SESSION['question_IDS_fetched'])) {
            echo 'QUESTION_SET_FINISHED';
            exit();
        } else {
            getQuestion($conn, false);
        }
    }
}

function getQuestion($conn, $isFirst)
{
    if ($isFirst == true) {
        $question = mysqli_query($conn, "SELECT id, title, optionA, optionB, optionC, optionD, score FROM Questions WHERE id = '" . $_SESSION['question_IDS_fetched'][0]['question_id'] . "' ");
        $_SESSION['question_counter']++;
        fetchAndReturnQuestion($question);
    } else {
        $question = mysqli_query($conn, "SELECT id, title, optionA, optionB, optionC, optionD, score FROM Questions WHERE id = '" . $_SESSION['question_IDS_fetched'][$_SESSION['question_counter']]['question_id'] . "' ");
        $_SESSION['question_counter']++;
        fetchAndReturnQuestion($question);
    }
}

function fetchAndReturnQuestion($question)
{
    if (mysqli_num_rows($question) > 0) {
        while ($row = mysqli_fetch_assoc($question)) {
            $fetched_question = $row;
        }
        echo json_encode($fetched_question);
    }
}

mysqli_close($conn);
?>

<script>
var timer = setTimeout(function(){ 
    alert("Time up!"); 
    window.location.href = "results.php"; 
}, 60000); // 60 seconds for example
// Feature 1: Log timestamp when each question is fetched
if (!isset($_SESSION['question_fetch_log'])) {
    $_SESSION['question_fetch_log'] = [];
}
$_SESSION['question_fetch_log'][] = [
    'question_id' => $_SESSION['question_IDS_fetched'][$_SESSION['question_counter']]['question_id'],
    'timestamp' => time()
];
// Feature 2: Implement CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';


// Feature 3: Allow users to mark questions for review
if (!isset($_SESSION['reviewed_questions'])) {
    $_SESSION['reviewed_questions'] = [];
}
if (isset($_POST['mark_for_review'])) {
    $_SESSION['reviewed_questions'][] = $_SESSION['question_IDS_fetched'][$_SESSION['question_counter'] - 1]['question_id'];
}
// Feature 4: Provide feedback messages for answers
function provideFeedback($isCorrect) {
    if ($isCorrect) {
        echo '<div class="feedback success">Correct Answer!</div>';
    } else {
        echo '<div class="feedback error">Incorrect Answer. Try again!</div>';
    }
}

function saveAnswer() {
    // Save answer logic
}
// Feature 5: Auto-save user progress every 30 seconds
// Feature 6: Limit the number of skips
if (!isset($_SESSION['skip_count'])) {
    $_SESSION['skip_count'] = 0;
}
if ($_SESSION['skip_count'] >= 3) {
    echo '<div class="error">Skip limit reached!</div>';
} else if (isset($_POST['skip_question'])) {
    $_SESSION['skip_count']++;
    // Logic to skip the question
}
// Feature 7: Display remaining time
// Feature 8: Temporarily store answers in session
if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}
if (isset($_POST['save_answer'])) {
    $_SESSION['answers'][$_SESSION['question_IDS_fetched'][$_SESSION['question_counter'] - 1]['question_id']] = $_POST['answer'];
}
// Feature 9: Add security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
// Feature 10: Auto-logout after inactivity
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
} else if (time() - $_SESSION['LAST_ACTIVITY'] > 1800) { // 30 minutes
    session_unset();
    session_destroy();
    header("Location: login.php");
}
$_SESSION['LAST_ACTIVITY'] = time();
// Feature 11: Add hints for questions
if (!isset($_SESSION['hints_shown'])) {
    $_SESSION['hints_shown'] = 0;
}
if ($_SESSION['hints_shown'] < 3) {
    echo '<button onclick="showHint()">Show Hint</button>';
    $_SESSION['hints_shown']++;
}



function reviewAnswers() {
    // Review answers logic
}

function skipQuestion() {
    // Skip question logic
}
</script>

<progress id="progressBar" value="<?php echo $_SESSION['question_counter']; ?>" max="<?php echo sizeof($_SESSION['question_IDS_fetched']); ?>"></progress>
<button onclick="saveAnswer()">Save Answer</button>
<button onclick="reviewAnswers()">Review Answers</button>
<button onclick="skipQuestion()">Skip Question</button>
<div id="feedback"></div>
<div id="score"></div>
