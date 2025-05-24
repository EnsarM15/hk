<?php
require_once 'config/config.php';
require_once 'config/db.php';

// Check if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$username = $email = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if ($user['username'] === $username) {
                $errors['username'] = 'Username already exists';
            }
            if ($user['email'] === $email) {
                $errors['email'] = 'Email already exists';
            }
        }
    }
    
    // If no errors, create user
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            // Set flash message
            setFlashMessage('success', 'Registration successful! You can now login.');
            
            // Redirect to login page
            redirect('login.php');
        } else {
            $errors['db_error'] = 'Registration failed. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QuizMaster</title>
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
                <h2 class="form-title">Create an Account</h2>
                
                <?php if (isset($errors['db_error'])): ?>
                    <div class="alert alert-error"><?php echo $errors['db_error']; ?></div>
                <?php endif; ?>
                
                <form id="register-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input <?php echo isset($errors['username']) ? 'input-error' : ''; ?>" value="<?php echo $username; ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="form-error"><?php echo $errors['username']; ?></div>
                        <?php endif; ?>
                    </div>
                    
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
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input <?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="form-error"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary form-submit">Register</button>
                </form>
                
                <p style="text-align: center; margin-top: 1rem;">Already have an account? <a href="login.php" class="form-link">Login</a></p>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Form validation
        validateForm('register-form', {
            username: { required: true, minLength: 3 },
            email: { required: true, email: true },
            password: { required: true, minLength: 6 },
            confirm_password: { required: true, match: 'password' }
        });
    </script>
</body>
</html>