<?php
session_start(); // Start a session to manage user login state
include 'sqlconnection.php'; // Include your database connection file

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Clear any previous error messages
unset($_SESSION['login_error']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
    } else {
        // Check if the user exists in the database
        $sql = "SELECT id, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                // Redirect to the landing page
                header("Location: landing.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid email or password.";
            }
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/user_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="login-form">
        <img src="img/aski_logo.jpg" class="header" alt="header">
        <h2>Login</h2>
        <form action="user_login.php" method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>

            <button type="submit" name="login">Login</button>
        </form>
        <div class="form-footer">
            Don't have an account? <a href="user_reg.php">Sign Up</a>
            <br>
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>

    <!-- Include SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Check for login error and show SweetAlert if exists
        <?php if (isset($_SESSION['login_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '<?php echo $_SESSION['login_error']; ?>',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>