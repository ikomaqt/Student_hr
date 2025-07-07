<?php
session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    header("Location: forgot_password.php");
    exit();
}

// Check if OTP has expired
if (time() > $_SESSION['reset_expiry']) {
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_expiry']);
    $_SESSION['reset_error'] = "OTP has expired. Please request a new one.";
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $user_otp = $_POST['otp'];
    
    // Verify OTP
    if (password_verify($user_otp, $_SESSION['reset_otp'])) {
        $_SESSION['otp_verified'] = true;
        $_SESSION['otp_success'] = "OTP verified successfully!";
        // Don't redirect here - let JavaScript handle it after showing success message
    } else {
        $_SESSION['otp_attempts']++;
        
        if ($_SESSION['otp_attempts'] >= 3) {
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_expiry']);
            $_SESSION['reset_error'] = "Too many failed attempts. Please request a new OTP.";
            header("Location: forgot_password.php");
            exit();
        }
        
        $_SESSION['otp_error'] = "Invalid OTP. You have " . (3 - $_SESSION['otp_attempts']) . " attempts remaining.";
    }
}

if (isset($_GET['resend_otp'])) {
    // Generate a new OTP
    $new_otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = password_hash($new_otp, PASSWORD_DEFAULT);
    $_SESSION['reset_expiry'] = time() + 300; // OTP valid for 5 minutes

    // Send the new OTP to the user's email
    if (isset($_SESSION['reset_email'])) {
        $to = $_SESSION['reset_email'];
        $subject = "Your New OTP";
        $message = "Your new OTP is: $new_otp";
        $headers = "From: no-reply@yourdomain.com";

        mail($to, $subject, $message, $headers);

        $_SESSION['otp_success'] = "A new OTP has been sent to your email.";
    } else {
        $_SESSION['reset_error'] = "Email not found in session. Please try again.";
    }

    header("Location: verify_otp.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/user_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="login-form">
        <img src="img/aski_logo.jpg" class="header" alt="header">
        <h2>Verify OTP</h2>
        <form method="post">
            <label for="otp">Enter OTP</label>
            <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" required maxlength="6" pattern="\d{6}">
            
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
        <div class="form-footer">
            <a href="verify_otp.php?resend_otp=1">Request new OTP</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_SESSION['otp_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: <?= json_encode($_SESSION['otp_error']) ?>,
                confirmButtonColor: '#3085d6'
            });
            <?php unset($_SESSION['otp_error']); ?>
        <?php elseif (!empty($_SESSION['otp_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?= json_encode($_SESSION['otp_success']) ?>,
                confirmButtonColor: '#3085d6',
                showCancelButton: false,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    <?php if (isset($_SESSION['otp_verified'])): ?>
                        window.location.href = 'reset_password.php';
                    <?php endif; ?>
                }
            });
            <?php unset($_SESSION['otp_success']); ?>
        <?php endif; ?>
    });

    document.addEventListener('DOMContentLoaded', function() {
        let expiryTime = <?= json_encode($_SESSION['reset_expiry'] ?? 0) ?>;
        let timerElement = document.createElement('div');
        timerElement.style.marginTop = '10px';
        timerElement.style.fontWeight = 'bold';
        document.querySelector('.form-footer').appendChild(timerElement);

        function updateTimer() {
            let currentTime = Math.floor(Date.now() / 1000);
            let remainingTime = expiryTime - currentTime;

            if (remainingTime > 0) {
                let minutes = Math.floor(remainingTime / 60);
                let seconds = remainingTime % 60;
                timerElement.textContent = `OTP expires in: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            } else {
                timerElement.textContent = 'OTP has expired. Please request a new one.';
                document.querySelector('button[name="verify_otp"]').disabled = true;
            }
        }

        setInterval(updateTimer, 1000);
        updateTimer();
    });
    </script>
</body>
</html>