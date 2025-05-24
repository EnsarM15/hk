<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view the dashboard.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Get user's quiz results
$query = "SELECT r.*, q.title as quiz_title, q.image_url 
          FROM quiz_results r 
          JOIN quizzes q ON r.quiz_id = q.id 
          WHERE r.user_id = ? 
          ORDER BY r.completed_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);

// Get statistics
$query = "SELECT 
            COUNT(*) as total_quizzes,
            SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_quizzes,
            AVG(percentage) as avg_percentage
          FROM quiz_results 
          WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$stats_result = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QuizMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: var(--space-6);
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-4);
        }
        
        .stat-card {
            background-color: var(--color-neutral-100);
            border-radius: var(--radius-md);
            padding: var(--space-4);
            text-align: center;
        }
        
        .stat-value {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: var(--space-2);
        }
        
        .stat-label {
            font-size: var(--font-size-sm);
            color: var(--color-neutral-600);
        }
        
        .quiz-history-item {
            display: flex;
            padding: var(--space-4) 0;
            border-bottom: 1px solid var(--color-neutral-200);
        }
        
        .quiz-history-item:last-child {
            border-bottom: none;
        }
        
        .quiz-history-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-md);
            margin-right: var(--space-4);
        }
        
        .quiz-history-content {
            flex: 1;
        }
        
        .quiz-history-title {
            font-weight: 500;
            margin-bottom: var(--space-2);
        }
        
        .quiz-history-meta {
            display: flex;
            justify-content: space-between;
            font-size: var(--font-size-sm);
            color: var(--color-neutral-600);
            margin-bottom: var(--space-2);
        }
        
        .quiz-history-score {
            font-weight: 700;
        }
        
        .quiz-history-score.passed {
            color: var(--color-success);
        }
        
        .quiz-history-score.failed {
            color: var(--color-error);
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'components/header.php'; ?>
        
        <main class="main-content">
            <h2>Welcome, <?php echo $user['username']; ?>!</h2>
            
            <?php displayFlashMessage(); ?>
            
            <div class="dashboard-grid">
                <div class="dashboard-sidebar">
                    <div class="dashboard-card slide-in-left">
                        <h3>Your Stats</h3>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $stats['total_quizzes'] ?? 0; ?></div>
                                <div class="stat-label">Quizzes Taken</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $stats['passed_quizzes'] ?? 0; ?></div>
                                <div class="stat-label">Quizzes Passed</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo round($stats['avg_percentage'] ?? 0); ?>%</div>
                                <div class="stat-label">Average Score</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card slide-in-left" style="animation-delay: 0.2s;">
                        <h3>Quick Actions</h3>
                        <a href="quizzes.php" class="btn btn-primary" style="width: 100%; margin-bottom: var(--space-3);">Take a Quiz</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="btn btn-secondary" style="width: 100%;">Admin Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-main">
                    <div class="dashboard-card slide-in-right">
                        <h3>Recent Quiz Results</h3>
                        
                        <?php if (mysqli_num_rows($results) > 0): ?>
                            <div class="quiz-history">
                                <?php while ($result = mysqli_fetch_assoc($results)): ?>
                                    <div class="quiz-history-item">
                                        <img src="<?php echo $result['image_url'] ?? 'https://images.pexels.com/photos/356079/pexels-photo-356079.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'; ?>" alt="<?php echo $result['quiz_title']; ?>" class="quiz-history-image">
                                        <div class="quiz-history-content">
                                            <h4 class="quiz-history-title"><?php echo $result['quiz_title']; ?></h4>
                                            <div class="quiz-history-meta">
                                                <span>Completed on <?php echo date('M j, Y', strtotime($result['completed_at'])); ?></span>
                                                <span class="quiz-history-score <?php echo $result['passed'] ? 'passed' : 'failed'; ?>">
                                                    <?php echo round($result['percentage']); ?>%
                                                </span>
                                            </div>
                                            <a href="results.php?id=<?php echo $result['id']; ?>" class="btn btn-secondary" style="font-size: var(--font-size-sm); padding: var(--space-2) var(--space-3);">View Details</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p>You haven't taken any quizzes yet. <a href="quizzes.php" class="form-link">Take your first quiz!</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>