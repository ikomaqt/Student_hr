<?php
include 'sqlconnection.php';

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password
function validatePassword($password, $confirm_password) {
    if (strlen($password) < 8) {
        return false;
    }
    return $password === $confirm_password;
}

// Function to register user
function registerUser($conn, $email, $first_name, $middle_name, $last_name, $password, $lrn, $section_id) {
    try {
        // Check if email already exists
        $sql_check = "SELECT password FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $existing_user = $result_check->fetch_assoc();
            if (!password_verify($password, $existing_user['password'])) {
                return ["success" => false, "message" => "Error: This email is already registered."];
            } else {
                return ["success" => false, "message" => "Error: This email is already registered."];
            }
        }

        // Insert into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, first_name, middle_name, last_name, password, lrn, section_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $email, $first_name, $middle_name, $last_name, $hashed_password, $lrn, $section_id);
        $stmt->execute();
        return ["success" => true, "message" => "Registration successful!"];
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return ["success" => false, "message" => "An error occurred during registration."];
    }
}

// Add this function after other function definitions
function fetchSections($conn) {
    $sql = "SELECT section_id, section_name FROM section ORDER BY section_name";
    $result = $conn->query($sql);
    if (!$result) {
        // Add error logging
        error_log("Error fetching sections: " . $conn->error);
    }
    $sections = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
    }
    return $sections;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email']);
    $first_name = sanitizeInput($_POST['first-name']);
    $middle_name = sanitizeInput($_POST['middle-name']);
    $last_name = sanitizeInput($_POST['last-name']);
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm-password']);
    $lrn = sanitizeInput($_POST['lrn']);
    $section_id = sanitizeInput($_POST['section_id']);

    $message = '';
    $type = '';
    $redirect = false;

    // Validate inputs
    if (!validateEmail($email)) {
        $message = 'Please enter a valid email address';
        $type = 'error';
    } elseif (!validatePassword($password, $confirm_password)) {
        $message = 'Passwords do not match or are less than 8 characters';
        $type = 'error';
    } else {
        // Register the user
        $result = registerUser($conn, $email, $first_name, $middle_name, $last_name, $password, $lrn, $section_id);
        if ($result["success"]) {
            $message = $result["message"];
            $type = 'success';
            $redirect = true;
        } else {
            $message = $result["message"];
            $type = 'error';
        }
    }
}

// Add this before the form HTML
$sections = fetchSections($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="css/user_reg.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <!-- Add Font Awesome for eye icons in the head section -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="registration-form">
        <img src="img/aski_logo.jpg" class="header" alt="header">
        <h2>Create an account</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="lrn">LRN (Learner Reference Number) <span style="color: red;">*</span></label>
            <input type="text" id="lrn" name="lrn" placeholder="Enter your LRN" required>

            <label for="section_id">Class Section <span style="color: red;">*</span></label>
<select id="section_id" name="section_id" required>
    <option value="">Select your section</option>
    
    <!-- Grade 7 Sections -->
    <optgroup label="Grade 7">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '7 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('7 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
    
    <!-- Grade 8 Sections -->
    <optgroup label="Grade 8">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '8 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('8 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
    
    <!-- Grade 9 Sections -->
    <optgroup label="Grade 9">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '9 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('9 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
    
    <!-- Grade 10 Sections -->
    <optgroup label="Grade 10">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '10 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('10 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
    
    <!-- Grade 11 Sections -->
    <optgroup label="Grade 11">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '11 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('11 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
    
    <!-- Grade 12 Sections -->
    <optgroup label="Grade 12">
        <?php foreach($sections as $section): 
            if (strpos($section['section_name'], '12 -') === 0): ?>
            <option value="<?php echo htmlspecialchars($section['section_id']); ?>">
                <?php echo htmlspecialchars(str_replace('12 - ', '', $section['section_name'])); ?>
            </option>
        <?php endif; endforeach; ?>
    </optgroup>
</select>

            <label for="first-name">First Name <span style="color: red;">*</span></label>
            <input type="text" id="first-name" name="first-name" placeholder="Enter your first name" required>

            <label for="middle-name">Middle Name</label>
            <input type="text" id="middle-name" name="middle-name" placeholder="Enter your middle name">

            <label for="last-name">Last Name <span style="color: red;">*</span></label>
            <input type="text" id="last-name" name="last-name" placeholder="Enter your last name" required>

            <div class="password-container">
                <label for="password">Password <span style="color: red;">*</span></label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" placeholder="Create a password (min. 8 characters)" required minlength="8">
                    <i class="fas fa-eye" id="togglePassword"></i>
                </div>
                <span id="password-validation" class="validation-message"></span>
            </div>

            <div class="password-container">
                <label for="confirm-password">Confirm Password <span style="color: red;">*</span></label>
                <div class="password-input-container">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required minlength="8">
                    <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                </div>
                <span id="confirm-password-validation" class="validation-message"></span>
            </div>

            <button type="submit">Register</button>
        </form>
        <div class="form-footer">
            Already have an account? <a href="user_login.php">Sign In</a>
        </div>
    </div>
    
    <?php if(isset($message) && !empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?php echo $type; ?>',
                title: '<?php echo $type === "success" ? "Success!" : "Error"; ?>',
                text: '<?php echo $message; ?>',
                confirmButtonText: '<?php echo $type === "success" ? "OK": "OK"; ?>'
            }).then((result) => {
                <?php if($redirect): ?>
                if (result.isConfirmed) {
                    window.location.href = 'user_login.php';
                }
                <?php endif; ?>
            });
        });
    </script>
    <?php endif; ?>

    <!-- Add this JavaScript before the closing body tag -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const password = document.querySelector('#password');
        const confirmPassword = document.querySelector('#confirm-password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Add password validation
        const passwordInput = document.querySelector('#password');
        const passwordValidation = document.querySelector('#password-validation');
        const confirmPasswordValidation = document.querySelector('#confirm-password-validation');

        passwordInput.addEventListener('input', function() {
            if (this.value.length >= 8) {
                passwordValidation.textContent = 'Password is valid';
                passwordValidation.className = 'validation-message valid';
            } else {
                passwordValidation.textContent = 'Password must be at least 8 characters';
                passwordValidation.className = 'validation-message invalid';
            }
        });

        confirmPassword.addEventListener('input', function() {
            if (this.value === password.value && this.value.length >= 8) {
                confirmPasswordValidation.textContent = 'Passwords match';
                confirmPasswordValidation.className = 'validation-message valid';
            } else {
                confirmPasswordValidation.textContent = 'Passwords do not match';
                confirmPasswordValidation.className = 'validation-message invalid';
            }
        });
    </script>
</body>
</html>