<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to take a quiz.');
    redirect('login.php');
}

// Check if quiz ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('error', 'No quiz selected.');
    redirect('quizzes.php');
}

$quiz_id = intval($_GET['id']);

// Get quiz details
$query = "SELECT * FROM quizzes WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    setFlashMessage('error', 'Quiz not found.');
    redirect('quizzes.php');
}

$quiz = mysqli_fetch_assoc($result);

// Get questions for this quiz
$query = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY RAND()";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $quiz_id);
mysqli_stmt_execute($stmt);
$questions_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($questions_result) === 0) {
    setFlashMessage('error', 'This quiz has no questions yet.');
    redirect('quizzes.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $total_points = 0;
    $score = 0;
    $user_answers = [];
    
    // Get all questions to calculate total points
    $query = "SELECT id, points FROM questions WHERE quiz_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $quiz_id);
    mysqli_stmt_execute($stmt);
    $all_questions = mysqli_stmt_get_result($stmt);
    
    while ($q = mysqli_fetch_assoc($all_questions)) {
        $total_points += $q['points'];
        
        // Check if user answered this question
        if (isset($_POST['question_' . $q['id']])) {
            $option_id = intval($_POST['question_' . $q['id']]);
            
            // Check if answer is correct
            $query = "SELECT is_correct FROM options WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $option_id);
            mysqli_stmt_execute($stmt);
            $option_result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($option_result) > 0) {
                $option = mysqli_fetch_assoc($option_result);
                $is_correct = $option['is_correct'];
                
                if ($is_correct) {
                    $score += $q['points'];
                }
                
                $user_answers[] = [
                    'question_id' => $q['id'],
                    'option_id' => $option_id,
                    'is_correct' => $is_correct
                ];
            }
        }
    }
    
    // Calculate percentage
    $percentage = ($total_points > 0) ? ($score / $total_points) * 100 : 0;
    $passed = ($percentage >= $quiz['passing_score']);
    
    // Insert quiz result
    $query = "INSERT INTO quiz_results (user_id, quiz_id, score, total_points, percentage, passed) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiiddi", $user_id, $quiz_id, $score, $total_points, $percentage, $passed);
    
    if (mysqli_stmt_execute($stmt)) {
        $result_id = mysqli_insert_id($conn);
        
        // Insert user answers
        foreach ($user_answers as $answer) {
            $query = "INSERT INTO user_answers (result_id, question_id, option_id, is_correct) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iiis", $result_id, $answer['question_id'], $answer['option_id'], $answer['is_correct']);
            mysqli_stmt_execute($stmt);
        }
        
        // Redirect to results page
        redirect('results.php?id=' . $result_id);
    } else {
        setFlashMessage('error', 'An error occurred while saving your results.');
        redirect('quizzes.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $quiz['title']; ?> - QuizMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <?php include 'components/header.php'; ?>
        
        <main class="main-content">
            <div class="quiz-container slide-in-up">
                <div class="quiz-header">
                    <h2><?php echo $quiz['title']; ?></h2>
                    <div id="quiz-timer" class="quiz-timer"><?php echo $quiz['time_limit']; ?>:00</div>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <div class="progress-text">Question 1 of <?php echo $quiz['question_count']; ?></div>
                
                <form id="quiz-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo $quiz_id; ?>" method="POST">
                    <?php
                    $question_num = 1;
                    while ($question = mysqli_fetch_assoc($questions_result)) {
                        // Get options for this question
                        $query = "SELECT * FROM options WHERE question_id = ? ORDER BY RAND()";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "i", $question['id']);
                        mysqli_stmt_execute($stmt);
                        $options_result = mysqli_stmt_get_result($stmt);
                        
                        ?>
                        <div class="question-container" id="question-<?php echo $question_num; ?>" style="display: <?php echo $question_num === 1 ? 'block' : 'none'; ?>">
                            <h3 class="question-text"><?php echo $question['question_text']; ?></h3>
                            
                            <ul class="options-list">
                                <?php while ($option = mysqli_fetch_assoc($options_result)) { ?>
                                    <li class="option-item">
                                        <label class="option-label transition-all">
                                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $option['id']; ?>" class="option-input" required>
                                            <span class="option-text"><?php echo $option['option_text']; ?></span>
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php
                        $question_num++;
                    }
                    ?>
                    
                    <div class="quiz-navigation">
                        <button type="button" id="prev-button" class="btn btn-secondary" style="display: none;">Previous</button>
                        <button type="button" id="next-button" class="btn btn-primary">Next</button>
                        <button type="submit" id="submit-button" class="btn btn-accent" style="display: none;">Submit Quiz</button>
                    </div>
                </form>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize quiz timer
            initQuizTimer(<?php echo $quiz['time_limit']; ?>);
            
            // Initialize quiz options
            initQuizOptions();
            
            // Quiz navigation
            const totalQuestions = <?php echo $quiz['question_count']; ?>;
            let currentQuestion = 1;
            
            const prevButton = document.getElementById('prev-button');
            const nextButton = document.getElementById('next-button');
            const submitButton = document.getElementById('submit-button');
            
            // Update progress initially
            updateQuizProgress(currentQuestion, totalQuestions);
            
            // Next button click
            nextButton.addEventListener('click', function() {
                // Validate current question is answered
                const currentQuestionContainer = document.getElementById(`question-${currentQuestion}`);
                const selectedOption = currentQuestionContainer.querySelector('input[type="radio"]:checked');
                
                if (!selectedOption) {
                    alert('Please select an answer before proceeding.');
                    return;
                }
                
                // Hide current question
                currentQuestionContainer.style.display = 'none';
                
                // Show next question
                currentQuestion++;
                document.getElementById(`question-${currentQuestion}`).style.display = 'block';
                
                // Update buttons
                prevButton.style.display = 'block';
                
                if (currentQuestion === totalQuestions) {
                    nextButton.style.display = 'none';
                    submitButton.style.display = 'block';
                }
                
                // Update progress
                updateQuizProgress(currentQuestion, totalQuestions);
            });
            
            // Previous button click
            prevButton.addEventListener('click', function() {
                // Hide current question
                document.getElementById(`question-${currentQuestion}`).style.display = 'none';
                
                // Show previous question
                currentQuestion--;
                document.getElementById(`question-${currentQuestion}`).style.display = 'block';
                
                // Update buttons
                if (currentQuestion === 1) {
                    prevButton.style.display = 'none';
                }
                
                nextButton.style.display = 'block';
                submitButton.style.display = 'none';
                
                // Update progress
                updateQuizProgress(currentQuestion, totalQuestions);
            });
        });
    </script>
</body>
</html>