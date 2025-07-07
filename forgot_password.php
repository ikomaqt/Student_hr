<?php
// Start session at the very beginning
session_start();

// Include database connection and PHPMailer
require 'sqlconnection.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function generateOTP() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Clear any previous reset session data
if (!isset($_POST['reset_password'])) {
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_expiry']);
    unset($_SESSION['otp_attempts']);
    unset($_SESSION['reset_success']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $_SESSION['reset_error'] = "Please enter your email address.";
        header("Location: forgot_password.php");
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows != 1) {
        $_SESSION['reset_error'] = "No account found with that email address.";
        header("Location: forgot_password.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $otp = generateOTP();
    $expiry = time() + 60; // 1 minute expiry
    
    // Store OTP in session
    $_SESSION['reset_otp'] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_expiry'] = $expiry;
    $_SESSION['otp_attempts'] = 0; // Track OTP attempts
    $_SESSION['reset_success'] = "OTP has been sent to your email address."; // Success message

    // Send OTP email using PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nesrac22@gmail.com';
        $mail->Password   = 'cegq qqrk jjdw xwbs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Recipients
        $mail->setFrom('no-reply@yourdomain.com', 'Health Record System');
        $mail->addAddress($email, $user['first_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body    = "
            <h2>Password Reset OTP</h2>
            <p>Hello {$user['first_name']},</p>
            <p>Your OTP for password reset is: <strong>$otp</strong></p>
            <p>This OTP is valid for 1 minute.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $mail->send();
        // Don't redirect here - let the JavaScript handle it after showing the success message
    } catch (Exception $e) {
        // Clear session variables if email fails
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_expiry']);
        unset($_SESSION['otp_attempts']);
        
        error_log("Mailer Error: " . $mail->ErrorInfo);
        $_SESSION['reset_error'] = "Failed to send OTP. Please try again later.";
        header("Location: forgot_password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/user_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="login-form">
        <img src="img/aski_logo.jpg" class="header" alt="header">
        <h2>Forgot Password</h2>
        <form method="post" id="forgotForm">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <button type="submit" name="reset_password">Send OTP</button>
        </form>
        <div class="form-footer">
            <a href="user_login.php">Back to Login</a>
        </div>
    </div>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Display alerts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_SESSION['reset_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: <?= json_encode($_SESSION['reset_error']) ?>,
                confirmButtonColor: '#3085d6'
            });
            <?php unset($_SESSION['reset_error']); ?>
        <?php elseif (!empty($_SESSION['reset_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?= json_encode($_SESSION['reset_success']) ?>,
                confirmButtonColor: '#3085d6',
                showCancelButton: false,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'verify_otp.php';
                }
            });
            <?php unset($_SESSION['reset_success']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>