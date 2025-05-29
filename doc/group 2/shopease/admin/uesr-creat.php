<?php
declare(strict_types=1);

// Initialize variables
$message = '';
$username = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    require "conn.php";
    
    // Validate and sanitize inputs
    $username = $conn->real_escape_string(trim($_POST['username']));
    $pass1 = $_POST['pass1'] ?? '';
    $pass2 = $_POST['pass2'] ?? '';
    
    // Input validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters";
    }
    
    if (empty($pass1) || empty($pass2)) {
        $errors[] = "Both password fields are required";
    } elseif ($pass1 !== $pass2) {
        $errors[] = "Passwords do not match";
    } elseif (strlen($pass1) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    // Check if username exists
    $check_query = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $stmt->close();
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        $pass = password_hash($pass1, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (username, password, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $username, $pass);
        
        if ($stmt->execute()) {
            $message = "User '".htmlspecialchars($username)."' registered successfully. ID: ".$conn->insert_id;
            // Clear form on success
            $username = '';
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User registration page">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-custom {
            background: linear-gradient(to right, rgba(106, 17, 203, 0.5), rgba(37, 117, 252, 0.5));
        }
        .form-outline input:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body class="gradient-custom">
    <div class="container py-5">
        <section class="vh-100">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-lg-12 col-xl-11">
                        <div class="card shadow-lg" style="border-radius: 25px;">
                            <div class="card-body p-md-5">
                                <div class="row justify-content-center">
                                    <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                                        <h1 class="text-center mb-4">Create Account</h1>
                                        
                                        <!-- Error Messages -->
                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>Error!</strong>
                                                <ul class="mb-0">
                                                    <?php foreach ($errors as $error): ?>
                                                        <li><?= htmlspecialchars($error) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Success Message -->
                                        <?php if ($message): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Success!</strong> <?= htmlspecialchars($message) ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="needs-validation" novalidate>
                                            <div class="mb-4">
                                                <div class="form-floating">
                                                    <input type="text" name="username" id="username" 
                                                           class="form-control" required 
                                                           value="<?= htmlspecialchars($username) ?>"
                                                           minlength="4" maxlength="50">
                                                    <label for="username" class="form-label">Username</label>
                                                    <div class="invalid-feedback">
                                                        Please enter a valid username (4-50 characters)
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <div class="form-floating">
                                                    <input type="password" name="pass1" id="pass1" 
                                                           class="form-control" required
                                                           minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                                                    <label for="pass1" class="form-label">Password</label>
                                                    <div class="invalid-feedback">
                                                        Password must be at least 8 characters with letters and numbers
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Minimum 8 characters with at least one letter and one number
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <div class="form-floating">
                                                    <input type="password" name="pass2" id="pass2" 
                                                           class="form-control" required
                                                           minlength="8">
                                                    <label for="pass2" class="form-label">Confirm Password</label>
                                                    <div class="invalid-feedback">
                                                        Passwords must match
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-check mb-4">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="terms" required>
                                                <label class="form-check-label" for="terms">
                                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a>
                                                </label>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-user-plus me-2"></i> Register
                                                </button>
                                            </div>

                                            <div class="text-center mt-3">
                                                <p>Already have an account? <a href="login.php">Login here</a></p>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">
                                        <img src="assets/images/register.webp" 
                                             class="img-fluid rounded-4 shadow" 
                                             alt="Registration illustration"
                                             loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Terms content here -->
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form Validation -->
    <script>
        (function () {
            'use strict'
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
            
            // Password confirmation validation
            const password = document.getElementById('pass1')
            const confirmPassword = document.getElementById('pass2')
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match")
                } else {
                    confirmPassword.setCustomValidity('')
                }
            }
            
            password.onchange = validatePassword
            confirmPassword.onkeyup = validatePassword
        })()
    </script>
</body>
</html>