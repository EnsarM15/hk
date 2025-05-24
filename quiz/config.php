<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'QuizMaster');
define('SITE_URL', 'http://localhost/quiz-master');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect user
function redirect($location) {
    header('Location: ' . $location);
    exit();
}

// Function to sanitize user input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to display flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to display flash message HTML
function displayFlashMessage() {
    $flashMessage = getFlashMessage();
    if ($flashMessage) {
        $type = $flashMessage['type'];
        $message = $flashMessage['message'];
        $class = $type === 'success' ? 'success' : 'error';
        
        echo "<div class='alert alert-{$class}'>{$message}</div>";
    }
}

