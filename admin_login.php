<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/admin_login.css">
    <!-- Include SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Login</h2>

            <!-- Display error message if passed as a query parameter -->
            <?php if (isset($_GET['error'])): ?>
                <p class="auth-error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <form id="loginForm" action="admin.php" method="post">
                <div class="auth-input-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" required>
                </div>
                <div class="auth-input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-btn">Login</button>
            </form>

            <!-- Link to create an account -->
            <p style="text-align: center; margin-top: 15px;">
                Don't have an account? <a href="admin_registration.php" style="color: #1e3a8a; text-decoration: none;">Create an account</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent immediate form submission

            // Show SweetAlert loading
            Swal.fire({
                title: 'Logging in...',
                text: 'Please wait.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Delay form submission for 4 seconds
            setTimeout(() => {
                this.submit(); // Submit the form after 4 seconds
            }, 4000);
        });
    </script>
</body>
</html>