<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view results.');
    redirect('login.php');
}

// Check if result ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('error', 'No result selected.');
    redirect('dashboard.php');
}

$result_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get result details
$query = "SELECT r.*, q.title as quiz_title FROM quiz_results r 
          JOIN quizzes q ON r.quiz_id = q.id 
          WHERE r.id = ? AND r.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $result_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    setFlashMessage('error', 'Result not found or you do not have permission to view it.');
    redirect('dashboard.php');
}

$quiz_result = mysqli_fetch_assoc($result);

// Get user answers
$query = "SELECT ua.*, q.question_text, o.option_text 
          FROM user_answers ua 
          JOIN questions q ON ua.question_id = q.id 
          JOIN options o ON ua.option_id = o.id 
          WHERE ua.result_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $result_id);
mysqli_stmt_execute($stmt);
$answers_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - QuizMaster</title>
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
            <div class="results-container slide-in-up">
                <h2>Quiz Results: <?php echo $quiz_result['quiz_title']; ?></h2>
                
                <div class="score-display" data-score="<?php echo round($quiz_result['percentage']); ?>">
                    <?php echo round($quiz_result['percentage']); ?>%
                </div>
                
                <p class="results-message">
                    <?php if ($quiz_result['passed']): ?>
                        Congratulations! You passed the quiz.
                    <?php else: ?>
                        You didn't pass this time. Keep practicing!
                    <?php endif; ?>
                </p>
                
                <div class="results-summary">
                    <p>Score: <?php echo $quiz_result['score']; ?> out of <?php echo $quiz_result['total_points']; ?> points</p>
                    <p>Completed on: <?php echo date('F j, Y, g:i a', strtotime($quiz_result['completed_at'])); ?></p>
                </div>
                
                <h3 style="margin-top: 2rem;">Question Details</h3>
                <div class="results-details">
                    <?php while ($answer = mysqli_fetch_assoc($answers_result)): ?>
                        <div class="result-item">
                            <p class="result-question"><?php echo $answer['question_text']; ?></p>
                            <p class="result-answer <?php echo $answer['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                Your answer: <?php echo $answer['option_text']; ?>
                                <?php if ($answer['is_correct']): ?>
                                    <span>✓</span>
                                <?php else: ?>
                                    <span>✗</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!$answer['is_correct']): ?>
                                <?php
                                // Get correct answer
                                $query = "SELECT o.option_text FROM options o 
                                          JOIN questions q ON o.question_id = q.id 
                                          WHERE q.id = ? AND o.is_correct = 1";
                                $stmt = mysqli_prepare($conn, $query);
                                mysqli_stmt_bind_param($stmt, "i", $answer['question_id']);
                                mysqli_stmt_execute($stmt);
                                $correct_result = mysqli_stmt_get_result($stmt);
                                $correct_answer = mysqli_fetch_assoc($correct_result);
                                ?>
                                <p class="correct-answer">Correct answer: <?php echo $correct_answer['option_text']; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="margin-top: 2rem; text-align: center;">
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    <a href="quizzes.php" class="btn btn-secondary" style="margin-left: 1rem;">Take Another Quiz</a>
                </div>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize results celebration animation
            initResultsCelebration();
        });
    </script>
</body>
</html>