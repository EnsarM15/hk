<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$email = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set flash message
                setFlashMessage('success', 'Login successful!');
                
                // Redirect to dashboard
                redirect('dashboard.php');
            } else {
                $errors['login_failed'] = 'Invalid email or password';
            }
        } else {
            $errors['login_failed'] = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizMaster</title>
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
            <div class="form-container slide-in-up">
                <h2 class="form-title">Login to Your Account</h2>
                
                <?php if (isset($errors['login_failed'])): ?>
                    <div class="alert alert-error"><?php echo $errors['login_failed']; ?></div>
                <?php endif; ?>
                
                <?php displayFlashMessage(); ?>
                
                <form id="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input <?php echo isset($errors['email']) ? 'input-error' : ''; ?>" value="<?php echo $email; ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input <?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-error"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary form-submit">Login</button>
                </form>
                
                <p style="text-align: center; margin-top: 1rem;">Don't have an account? <a href="register.php" class="form-link">Register</a></p>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Form validation
        validateForm('login-form', {
            email: { required: true, email: true },
            password: { required: true }
        });
    </script>
</body>
</html>