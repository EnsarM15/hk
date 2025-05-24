<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Get all categories
$query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $query);

// Handle category filter
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query for quizzes
$query = "SELECT q.*, c.name as category_name FROM quizzes q 
          LEFT JOIN categories c ON q.category_id = c.id 
          WHERE q.is_public = 1";

if ($category_id > 0) {
    $query .= " AND q.category_id = " . $category_id;
}

$query .= " ORDER BY q.title";
$quizzes_result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - QuizMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
            margin-bottom: var(--space-6);
        }
        
        .category-filter {
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .category-filter.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .category-filter:not(.active) {
            background-color: var(--color-neutral-200);
            color: var(--color-neutral-700);
        }
        
        .category-filter:hover:not(.active) {
            background-color: var(--color-neutral-300);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--space-12);
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: var(--space-4);
            color: var(--color-neutral-400);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'components/header.php'; ?>
        
        <main class="main-content">
            <h2>All Quizzes</h2>
            
            <?php displayFlashMessage(); ?>
            
            <div class="category-filters slide-in-left">
                <a href="quizzes.php" class="category-filter <?php echo $category_id === 0 ? 'active' : ''; ?>">All Categories</a>
                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                    <a href="quizzes.php?category=<?php echo $category['id']; ?>" class="category-filter <?php echo $category_id === intval($category['id']) ? 'active' : ''; ?>">
                        <?php echo $category['name']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
            
            <?php if (mysqli_num_rows($quizzes_result) > 0): ?>
                <div class="quiz-grid">
                    <?php while ($quiz = mysqli_fetch_assoc($quizzes_result)): ?>
                        <div class="quiz-card stagger-item">
                            <img src="<?php echo $quiz['image_url'] ?? 'https://images.pexels.com/photos/356079/pexels-photo-356079.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'; ?>" alt="<?php echo $quiz['title']; ?>" class="quiz-image">
                            <div class="quiz-content">
                                <h3 class="quiz-title"><?php echo $quiz['title']; ?></h3>
                                <p class="quiz-description"><?php echo $quiz['description']; ?></p>
                                <div class="quiz-meta">
                                    <span><?php echo $quiz['question_count']; ?> questions</span>
                                    <span><?php echo $quiz['time_limit']; ?> minutes</span>
                                    <?php if (!empty($quiz['category_name'])): ?>
                                        <span><?php echo $quiz['category_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Start Quiz</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login to Take Quiz</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state slide-in-up">
                    <div class="empty-state-icon">📚</div>
                    <h3>No Quizzes Found</h3>
                    <p>There are no quizzes available in this category yet.</p>
                    <?php if ($category_id > 0): ?>
                        <a href="quizzes.php" class="btn btn-primary" style="margin-top: var(--space-4);">View All Quizzes</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>