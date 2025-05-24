<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster - Test Your Knowledge</title>
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
            <div class="hero fade-in">
                <h1>Welcome to <span class="highlight">QuizMaster</span></h1>
                <p class="subtitle">Challenge yourself with our collection of quizzes</p>
                
                <div class="cta-buttons">
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                </div>
            </div>
            
            <section class="featured-quizzes">
                <h2>Featured Quizzes</h2>
                <div class="quiz-grid">
                    <?php include 'components/featured_quizzes.php'; ?>
                </div>
            </section>
            
            <section class="how-it-works">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step slide-in-left">
                        <div class="step-number">1</div>
                        <h3>Create an Account</h3>
                        <p>Sign up to track your progress and save your scores</p>
                    </div>
                    <div class="step slide-in-left" style="animation-delay: 0.2s;">
                        <div class="step-number">2</div>
                        <h3>Choose a Quiz</h3>
                        <p>Select from our wide range of categories</p>
                    </div>
                    <div class="step slide-in-left" style="animation-delay: 0.4s;">
                        <div class="step-number">3</div>
                        <h3>Test Your Knowledge</h3>
                        <p>Answer questions and see your results instantly</p>
                    </div>
                </div>
            </section>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>