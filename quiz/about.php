<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - QuizMaster</title>
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
            <div class="about-container slide-in-up">
                <h2>About QuizMaster</h2>
                
                <div class="about-section">
                    <h3>Our Mission</h3>
                    <p>QuizMaster is dedicated to making learning fun and engaging through interactive quizzes. Our platform allows users to test their knowledge across a wide range of topics, track their progress, and improve their understanding of various subjects.</p>
                </div>
                
                <div class="about-section">
                    <h3>How It Works</h3>
                    <p>Create an account to access our library of quizzes. Take quizzes at your own pace, receive instant feedback on your answers, and track your progress over time. Our platform makes it easy to identify areas where you excel and areas where you might need more practice.</p>
                </div>
                
                <div class="about-section">
                    <h3>Our Team</h3>
                    <p>QuizMaster was created by a team of educators and developers passionate about making learning accessible and enjoyable for everyone. We continuously work to improve our platform and add new content to help you expand your knowledge.</p>
                </div>
                
                <div class="about-section">
                    <h3>Contact Us</h3>
                    <p>Have questions or suggestions? We'd love to hear from you! Reach out to us at <a href="mailto:contact@quizmaster.com">contact@quizmaster.com</a> or visit our <a href="contact.php">Contact page</a> for more ways to get in touch.</p>
                </div>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>