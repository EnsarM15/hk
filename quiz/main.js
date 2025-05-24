document.addEventListener('DOMContentLoaded', function() {
    // Animation for staggered items
    const staggerItems = document.querySelectorAll('.stagger-item');
    
    if (staggerItems.length > 0) {
        // Add animation class with delay based on index
        staggerItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('fade-in');
            }, index * 100);
        });
    }
    
    // Add active class to navigation based on current page
    const currentLocation = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        if (currentLocation === linkPath) {
            link.classList.add('active');
        }
    });
    
    // Flash message auto-dismiss
    const flashMessages = document.querySelectorAll('.alert');
    if (flashMessages.length > 0) {
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.classList.add('fade-out');
                setTimeout(() => {
                    message.remove();
                }, 500);
            }, 5000);
        });
    }
});

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
            
            // Mark the radio/checkbox as checked
            const input = this.querySelector('input');
            input.checked = true;
        });
    });
}

// Quiz progress update
function updateQuizProgress(currentQuestion, totalQuestions) {
    const progressBar = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    
    if (progressBar && progressText) {
        const progressPercentage = (currentQuestion / totalQuestions) * 100;
        progressBar.style.width = `${progressPercentage}%`;
        progressText.textContent = `Question ${currentQuestion} of ${totalQuestions}`;
    }
}

// Results page celebration animation
function initResultsCelebration() {
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

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Clear previous error messages
        form.querySelectorAll('.form-error').forEach(error => error.remove());
        
        for (const fieldName in rules) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            
            if (!field) continue;
            
            const fieldRules = rules[fieldName];
            
            // Required validation
            if (fieldRules.required && field.value.trim() === '') {
                showError(field, 'This field is required');
                isValid = false;
                continue;
            }
            
            // Email validation
            if (fieldRules.email && field.value.trim() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value.trim())) {
                    showError(field, 'Please enter a valid email address');
                    isValid = false;
                }
            }
            
            // Min length validation
            if (fieldRules.minLength && field.value.length < fieldRules.minLength) {
                showError(field, `Must be at least ${fieldRules.minLength} characters`);
                isValid = false;
            }
            
            // Match validation (for password confirmation)
            if (fieldRules.match) {
                const matchField = form.querySelector(`[name="${fieldRules.match}"]`);
                if (matchField && field.value !== matchField.value) {
                    showError(field, 'Passwords do not match');
                    isValid = false;
                }
            }
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });
    
    function showError(field, message) {
        const errorElement = document.createElement('div');
        errorElement.classList.add('form-error');
        errorElement.textContent = message;
        
        const formGroup = field.closest('.form-group');
        formGroup.appendChild(errorElement);
        
        // Add error class to input
        field.classList.add('input-error');
    }
}