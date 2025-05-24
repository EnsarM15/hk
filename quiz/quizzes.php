<?php
require_once '/db.php';

// Get featured quizzes
$query = "SELECT * FROM quizzes WHERE is_featured = 1 LIMIT 3";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($quiz = mysqli_fetch_assoc($result)) {
        ?>
        <div class="quiz-card stagger-item">
            <img src="<?php echo $quiz['image_url']; ?>" alt="<?php echo $quiz['title']; ?>" class="quiz-image">
            <div class="quiz-content">
                <h3 class="quiz-title"><?php echo $quiz['title']; ?></h3>
                <p class="quiz-description"><?php echo $quiz['description']; ?></p>
                <div class="quiz-meta">
                    <span><?php echo $quiz['question_count']; ?> questions</span>
                    <span><?php echo $quiz['time_limit']; ?> minutes</span>
                </div>
                <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Start Quiz</a>
            </div>
        </div>
        <?php
    }
} else {
    // Fallback for when no quizzes are in the database yet
    $sample_quizzes = [
        [
            'title' => 'General Knowledge',
            'description' => 'Test your knowledge on various topics from history to science.',
            'image_url' => 'https://images.pexels.com/photos/356079/pexels-photo-356079.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'questions' => 10,
            'time' => 15
        ],
        [
            'title' => 'Science & Technology',
            'description' => 'Challenge yourself with questions about science, technology, and innovations.',
            'image_url' => 'https://images.pexels.com/photos/2280571/pexels-photo-2280571.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'questions' => 15,
            'time' => 20
        ],
        [
            'title' => 'Pop Culture',
            'description' => 'How well do you know movies, music, and celebrity trivia?',
            'image_url' => 'https://images.pexels.com/photos/1190298/pexels-photo-1190298.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'questions' => 12,
            'time' => 15
        ]
    ];
    
    foreach ($sample_quizzes as $index => $quiz) {
        ?>
        <div class="quiz-card stagger-item">
            <img src="<?php echo $quiz['image_url']; ?>" alt="<?php echo $quiz['title']; ?>" class="quiz-image">
            <div class="quiz-content">
                <h3 class="quiz-title"><?php echo $quiz['title']; ?></h3>
                <p class="quiz-description"><?php echo $quiz['description']; ?></p>
                <div class="quiz-meta">
                    <span><?php echo $quiz['questions']; ?> questions</span>
                    <span><?php echo $quiz['time']; ?> minutes</span>
                </div>
                <a href="register.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register to Play</a>
            </div>
        </div>
        <?php
    }
}
?>