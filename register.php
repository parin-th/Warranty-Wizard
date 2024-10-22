<?php 

    // Start the session to use session variables
    session_start();

    // Step 1: Full path to the SQLite database file
    include('connect/connection.php');
    // Send OTP via PHPMailer
    require 'vendor/autoload.php'; // Ensure this is correct

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;


    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('max_execution_time', 60); // Increase execution time limit to 60 seconds

    if (isset($_POST["register"])) {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $mymail = 'warrantytracker1@gmail.com';
        $mypw = 'ftrr qbtv wkjk sacs';

        // Initialize error array
        $errors = [];

        // Validate fields are not empty
        if (empty($name) || empty($email) || empty($password)) {
            $errors[] = "All fields are required.";
        }

        // Password validation
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors[] = "Password must contain at least one letter.";
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }

        // Validate the email using PHP's built-in filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid Email format.";
        }

        if (empty($errors)) {
            // Check if the email already exists in the database
            $stmt = $conn->prepare("SELECT * FROM login WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetch()) {
                echo "<script>alert('User with email already exists!');</script>";
            } else {
                // Hash the password before inserting
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Get the next user_id
                $stmt = $conn->prepare("SELECT IFNULL(MAX(user_id), 9999) + 1 AS new_user_id FROM login");
                $stmt->execute();
                $new_user_id = $stmt->fetch(PDO::FETCH_ASSOC)['new_user_id'];

                // Insert new user
                try {
                    $stmt = $conn->prepare("INSERT INTO login (user_id, name, email, password, status) VALUES (:user_id, :name, :email, :pw, 0);");
                    $stmt->bindParam(':user_id', $new_user_id);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':pw', $password_hash);
                    
                    // Execute the statement and check for success
                    if ($stmt->execute()) {
                        // Generate and send OTP
                        $otp = rand(100000, 999999);
                        $_SESSION['otp'] = $otp;
                        $_SESSION['mail'] = $email;
                        $mail = new PHPMailer(true);

                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->Port = 587;
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = 'tls';

                        $mail->Username = $mymail;
                        $mail->Password = $mypw;

                        $mail->setFrom($mymail, 'OTP Verification');
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = "Your verify code";
                        $mail->Body = "<p>Dear " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ",</p>
                        <h3>Your verify OTP code is $otp <br></h3>
                        <p>With regards,</p>
                        <b>Warranty Tracker</b>";

                        if (!$mail->send()) {
                            echo "<script>alert('Registration failed. Could not send OTP.');</script>";
                        } else {
                            echo "<script>alert('Registered successfully, OTP sent to " . $email . "'); window.location.replace('verification.php');</script>";
                        }
                    } else {
                        print_r($stmt->errorInfo()); // Log any errors for troubleshooting
                    }
                } catch (PDOException $e) {
                    echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
                }
            }
        } else {
            // Show validation errors
            $errorMsg = implode("\\n", $errors);
            echo "<script>alert('" . $errorMsg . "');</script>";
        }

        // Close the database connection
        $conn = null;
    }
?>



<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Favicon.png">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <title>Register Form</title>
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <a href="#">Warranty Tracker</a>
    </div>
    <ul class="nav-links">
        <li><a href="https://github.com/parin-th/Warranty-Tracker" target="_blank">Github</a></li>
        <li><a href="register.php" target="_blank">Video Demo</a></li>
        <li><a href="index.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<main class="login-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Register</div>
                    <div class="card-body">
                        <form action="#" method="POST" name="register">
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
                                <div class="col-md-6">
                                    <input type="text" id="name" class="form-control" name="name" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email_address" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                                <div class="col-md-6">
                                    <input type="text" id="email_address" class="form-control" name="email" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required onkeyup="validatePassword()">
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                </div>
                            </div>

                            <!-- Password strength indicators -->
                            <div class="validation">
                                <div>
                                    <img id="length-img" src="img/invalid.png" alt="length validation">
                                    <span id="length-text">8 minimum characters</span>
                                </div>
                                <div>
                                    <img id="number-img" src="img/invalid.png" alt="number validation">
                                    <span id="number-text">1 number</span>
                                </div>
                                <div>
                                    <img id="letter-img" src="img/invalid.png" alt="letter validation">
                                    <span id="letter-text">1 letter</span>
                                </div>
                                <div>
                                    <img id="special-img" src="img/invalid.png" alt="special character validation">
                                    <span id="special-text">1 special character</span>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary" name="register">
                                        Register
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
    });

    // Password validation
    function validatePassword() {
        const password = passwordInput.value;

        const lengthValid = password.length >= 8;
        const numberValid = /\d/.test(password);
        const letterValid = /[A-Za-z]/.test(password);
        const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        document.getElementById('length-img').src = lengthValid ? "img/valid.png" : "img/invalid.png";
        document.getElementById('number-img').src = numberValid ? "img/valid.png" : "img/invalid.png";
        document.getElementById('letter-img').src = letterValid ? "img/valid.png" : "img/invalid.png";
        document.getElementById('special-img').src = specialValid ? "img/valid.png" : "img/invalid.png";
    }
</script>
</body>
</html>
