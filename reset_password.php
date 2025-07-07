<?php
// Include database connection
require_once 'sqlconnection.php';

session_start();

// Redirect if OTP not verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    $_SESSION['reset_error'] = "Please verify your OTP first.";
    header("Location: forgot_password.php");
    exit();
}

// Clear previous messages
unset($_SESSION['reset_error']);
unset($_SESSION['reset_success']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate passwords
    if (empty($password) || empty($confirm_password)) {
        $_SESSION['reset_error'] = "Please fill in both password fields.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['reset_error'] = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $_SESSION['reset_error'] = "Password must be at least 8 characters long.";
    } else {
        // Update password in database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['reset_success'] = "Password has been reset successfully! You can now login with your new password.";
            
            // Clear OTP verification flag but keep success message
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            
            // Don't redirect - we'll show success on this page
        } else {
            $_SESSION['reset_error'] = "Failed to reset password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/user_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-field {
            position: relative;
            margin-bottom: 5px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        .password-rules {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 15px;
        }
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding-right: 30px; /* Space for the eye icon */
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <img src="img/aski_logo.jpg" class="header" alt="header">
        <h2>Reset Password</h2>
        
        <?php if (!isset($_SESSION['reset_success'])): ?>
        <form method="post" id="resetForm">
            <label for="password">New Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            
            <label for="confirm_password">Confirm Password</label>
            <div class="password-field">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
            </div>
            
            <div class="password-rules">Password must be at least 8 characters long</div>
            
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php else: ?>
            <div class="success-message" style="text-align: center; margin: 20px 0;">
                <p style="color: green; font-weight: bold;"><?= htmlspecialchars($_SESSION['reset_success']) ?></p>
                <div class="login-link">
                    <a href="user_login.php">Click here to login</a>
                </div>
            </div>
            <?php unset($_SESSION['reset_success']); ?>
        <?php endif; ?>
    </div>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        if (togglePassword && toggleConfirmPassword) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPassword.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        }

        // Form submission validation
        const form = document.getElementById('resetForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Passwords do not match',
                        confirmButtonColor: '#3085d6'
                    });
                    confirmPassword.focus();
                }
            });
        }

        // Show success message if exists
        <?php if (isset($_SESSION['reset_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?= json_encode($_SESSION['reset_success']) ?>,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Go to Login'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'user_login.php';
                }
            });
            <?php unset($_SESSION['reset_success']); ?>
        <?php endif; ?>

        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
    </script>
</body>
</html>