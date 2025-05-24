document.addEventListener('DOMContentLoaded', function() {
    // Initialize quiz features if we're on a quiz page
    if (document.getElementById('quiz-form')) {
        initQuizFeatures();
    }
    
    // Initialize results page features if we're on a results page
    if (document.querySelector('.results-container')) {
        initResultsFeatures();
    }
});

function initQuizFeatures() {
    // Initialize option selection
    initQuizOptions();
    
    // Get quiz parameters
    const quizForm = document.getElementById('quiz-form');
    const timerElement = document.getElementById('quiz-timer');
    const timeLimit = timerElement ? parseInt(timerElement.getAttribute('data-time-limit')) : 0;
    
    // Initialize timer if we have a time limit
    if (timeLimit > 0) {
        initQuizTimer(timeLimit);
    }
    
    // Initialize navigation
    const questionContainers = document.querySelectorAll('.question-container');
    const totalQuestions = questionContainers.length;
    let currentQuestion = 1;
    
    const prevButton = document.getElementById('prev-button');
    const nextButton = document.getElementById('next-button');
    const submitButton = document.getElementById('submit-button');
    
    // Update progress initially
    updateQuizProgress(currentQuestion, totalQuestions);
    
    // Next button click
    if (nextButton) {
        nextButton.addEventListener('click', function() {
            // Hide current question
            document.getElementById(`question-${currentQuestion}`).style.display = 'none';
            
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
    }
    
    // Previous button click
    if (prevButton) {
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
    }
    
    // Form submission
    if (quizForm) {
        quizForm.addEventListener('submit', function(event) {
            // Optionally add confirmation before submitting
            if (!confirm('Are you sure you want to submit your answers?')) {
                event.preventDefault();
            }
        });
    }
}

function initResultsFeatures() {
    // Show celebration animation for passing scores
    const scoreElement = document.querySelector('.score-display');
    
    if (scoreElement) {
        const score = parseInt(scoreElement.getAttribute('data-score'));
        
        if (score >= 70) {
            scoreElement.classList.add('celebration');
            setTimeout(() => {
                scoreElement.classList.add('active');
            }, 500);
        }
    }
}

// Quiz option selection
function initQuizOptions() {
    const optionLabels = document.querySelectorAll('.option-label');
    
    optionLabels.forEach(label => {
        label.addEventListener('click', function() {
            // Get the question container
            const questionContainer = this.closest('.question-container');
            
            // Remove selected class from all options in this question
            const optionsInQuestion = questionContainer.querySelectorAll('.option-label');
            optionsInQuestion.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Mark the radio as checked
            const input = this.querySelector('input');
            input.checked = true;
        });
    });
}

// Quiz timer functionality
function initQuizTimer(timeLimit) {
    if (!timeLimit) return;
    
    const timerElement = document.getElementById('quiz-timer');
    if (!timerElement) return;
    
    let timeRemaining = timeLimit * 60; // Convert minutes to seconds
    
    function updateTimer() {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeRemaining <= 300) { // 5 minutes remaining
            timerElement.classList.add('warning');
        }
        
        if (timeRemaining <= 60) { // 1 minute remaining
            timerElement.classList.add('danger');
            timerElement.classList.add('pulse');
        }
        
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            document.getElementById('quiz-form').submit();
        }
        
        timeRemaining--;
    }
    
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
    
    // Store timer in window object to access it later if needed
    window.quizTimer = {
        interval: timerInterval,
        timeRemaining: timeRemaining
    };
}

// Update quiz progress
function updateQuizProgress(currentQuestion, totalQuestions) {
    const progressBar = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    
    if (progressBar && progressText) {
        const progressPercentage = (currentQuestion / totalQuestions) * 100;
        progressBar.style.width = `${progressPercentage}%`;
        progressText.textContent = `Question ${currentQuestion} of ${totalQuestions}`;
    }
}