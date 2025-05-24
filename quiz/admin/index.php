<?php
require_once '/config.php';
require_once '../config/db.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin area.');
    redirect('../index.php');
}

// Get statistics
$stats = [
    'users' => 0,
    'quizzes' => 0,
    'questions' => 0,
    'results' => 0
];

// Get user count
$query = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $query);
$stats['users'] = mysqli_fetch_assoc($result)['count'];

// Get quiz count
$query = "SELECT COUNT(*) as count FROM quizzes";
$result = mysqli_query($conn, $query);
$stats['quizzes'] = mysqli_fetch_assoc($result)['count'];

// Get question count
$query = "SELECT COUNT(*) as count FROM questions";
$result = mysqli_query($conn, $query);
$stats['questions'] = mysqli_fetch_assoc($result)['count'];

// Get result count
$query = "SELECT COUNT(*) as count FROM quiz_results";
$result = mysqli_query($conn, $query);
$stats['results'] = mysqli_fetch_assoc($result)['count'];

// Get recent quizzes
$query = "SELECT q.*, u.username FROM quizzes q 
          LEFT JOIN users u ON q.created_by = u.id 
          ORDER BY q.created_at DESC LIMIT 5";
$recent_quizzes = mysqli_query($conn, $query);

// Get recent results
$query = "SELECT r.*, u.username, q.title as quiz_title 
          FROM quiz_results r 
          JOIN users u ON r.user_id = u.id 
          JOIN quizzes q ON r.quiz_id = q.id 
          ORDER BY r.completed_at DESC LIMIT 5";
$recent_results = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuizMaster</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: var(--space-6);
        }
        
        .admin-sidebar {
            background-color: var(--color-neutral-800);
            color: white;
            min-height: calc(100vh - 60px);
            position: sticky;
            top: 60px;
        }
        
        .admin-nav {
            padding: var(--space-4);
        }
        
        .admin-nav-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            padding: var(--space-4);
            border-bottom: 1px solid var(--color-neutral-700);
            margin-bottom: var(--space-4);
        }
        
        .admin-nav-menu {
            list-style: none;
        }
        
        .admin-nav-link {
            display: block;
            padding: var(--space-3) var(--space-4);
            color: var(--color-neutral-300);
            text-decoration: none;
            border-radius: var(--radius-md);
            margin-bottom: var(--space-2);
            transition: all 0.2s ease;
        }
        
        .admin-nav-link:hover {
            background-color: var(--color-neutral-700);
            color: white;
        }
        
        .admin-nav-link.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .admin-content {
            padding: var(--space-6);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-4);
            margin-bottom: var(--space-8);
        }
        
        .stat-card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--space-6);
            text-align: center;
        }
        
        .stat-value {
            font-size: var(--font-size-4xl);
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: var(--space-2);
        }
        
        .stat-label {
            color: var(--color-neutral-600);
        }
        
        .admin-panel {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-6);
            overflow: hidden;
        }
        
        .panel-header {
            background-color: var(--color-neutral-100);
            padding: var(--space-4) var(--space-6);
            border-bottom: 1px solid var(--color-neutral-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-title {
            font-size: var(--font-size-xl);
            font-weight: 500;
            margin: 0;
        }
        
        .panel-action {
            font-size: var(--font-size-sm);
        }
        
        .panel-content {
            padding: var(--space-4) var(--space-6);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th, 
        .admin-table td {
            padding: var(--space-3) var(--space-4);
            text-align: left;
            border-bottom: 1px solid var(--color-neutral-200);
        }
        
        .admin-table th {
            background-color: var(--color-neutral-100);
            font-weight: 500;
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .admin-table tr:hover td {
            background-color: var(--color-neutral-100);
        }
        
        .admin-table .actions {
            display: flex;
            gap: var(--space-2);
        }
        
        .tag {
            display: inline-block;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
            font-weight: 500;
        }
        
        .tag-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--color-success);
        }
        
        .tag-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--color-error);
        }
        
        .tag-primary {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--color-primary);
        }
        
        .tag-warning {
            background-color: rgba(241, 196, 15, 0.2);
            color: var(--color-warning);
        }
        
        .btn-sm {
            padding: var(--space-1) var(--space-2);
            font-size: var(--font-size-xs);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                position: static;
                min-height: auto;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include '../components/header.php'; ?>
        
        <div class="admin-container">
            <div class="admin-sidebar">
                <div class="admin-nav">
                    <div class="admin-nav-title">Admin Dashboard</div>
                    <ul class="admin-nav-menu">
                        <li><a href="index.php" class="admin-nav-link active">Overview</a></li>
                        <li><a href="users.php" class="admin-nav-link">Users</a></li>
                        <li><a href="quizzes.php" class="admin-nav-link">Quizzes</a></li>
                        <li><a href="questions.php" class="admin-nav-link">Questions</a></li>
                        <li><a href="categories.php" class="admin-nav-link">Categories</a></li>
                        <li><a href="results.php" class="admin-nav-link">Results</a></li>
                        <li><a href="../index.php" class="admin-nav-link">Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="admin-content">
                <h2>Admin Dashboard</h2>
                
                <?php displayFlashMessage(); ?>
                
                <div class="stats-grid slide-in-up">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['users']; ?></div>
                        <div class="stat-label">Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['quizzes']; ?></div>
                        <div class="stat-label">Quizzes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['questions']; ?></div>
                        <div class="stat-label">Questions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['results']; ?></div>
                        <div class="stat-label">Quiz Results</div>
                    </div>
                </div>
                
                <div class="admin-panels">
                    <div class="admin-panel slide-in-up" style="animation-delay: 0.2s;">
                        <div class="panel-header">
                            <h3 class="panel-title">Recent Quizzes</h3>
                            <a href="quizzes.php" class="panel-action">View All</a>
                        </div>
                        <div class="panel-content">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Questions</th>
                                        <th>Created By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($recent_quizzes) > 0): ?>
                                        <?php while ($quiz = mysqli_fetch_assoc($recent_quizzes)): ?>
                                            <tr>
                                                <td><?php echo $quiz['title']; ?></td>
                                                <td>
                                                    <?php
                                                    if ($quiz['category_id']) {
                                                        $query = "SELECT name FROM categories WHERE id = " . $quiz['category_id'];
                                                        $category_result = mysqli_query($conn, $query);
                                                        $category = mysqli_fetch_assoc($category_result);
                                                        echo $category['name'];
                                                    } else {
                                                        echo 'Uncategorized';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $quiz['question_count']; ?></td>
                                                <td><?php echo $quiz['username'] ?? 'System'; ?></td>
                                                <td>
                                                    <?php if ($quiz['is_public']): ?>
                                                        <span class="tag tag-success">Public</span>
                                                    <?php else: ?>
                                                        <span class="tag tag-warning">Private</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($quiz['is_featured']): ?>
                                                        <span class="tag tag-primary">Featured</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="actions">
                                                    <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                                    <a href="quiz_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-secondary btn-sm">Questions</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center;">No quizzes found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="admin-panel slide-in-up" style="animation-delay: 0.4s;">
                        <div class="panel-header">
                            <h3 class="panel-title">Recent Results</h3>
                            <a href="results.php" class="panel-action">View All</a>
                        </div>
                        <div class="panel-content">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Quiz</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($recent_results) > 0): ?>
                                        <?php while ($result = mysqli_fetch_assoc($recent_results)): ?>
                                            <tr>
                                                <td><?php echo $result['username']; ?></td>
                                                <td><?php echo $result['quiz_title']; ?></td>
                                                <td><?php echo $result['score']; ?> / <?php echo $result['total_points']; ?></td>
                                                <td><?php echo round($result['percentage']); ?>%</td>
                                                <td>
                                                    <?php if ($result['passed']): ?>
                                                        <span class="tag tag-success">Passed</span>
                                                    <?php else: ?>
                                                        <span class="tag tag-error">Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y, g:i a', strtotime($result['completed_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center;">No results found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include '../components/footer.php'; ?>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>