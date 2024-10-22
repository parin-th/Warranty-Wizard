<?php
session_start(); // Start the session

include('connect/connection.php');

// Send OTP via PHPMailer
require 'vendor/autoload.php'; // Ensure this is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted
if (isset($_POST["login"])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare a statement to avoid SQL injection
    $stmt = $conn->prepare("
        SELECT login.*, items.end_date 
        FROM login 
        LEFT JOIN items ON login.user_id = items.user_id 
        WHERE login.email = :email
    ");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

    // Execute the statement
    if ($stmt->execute() === false) {
        echo "<script>alert('Database error.');</script>";
        exit;
    }

    // Fetch the row
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fetch) {
        $hashpassword = $fetch["password"];

        // Check if the account is verified
        if ($fetch["status"] == 0) {
            // Generate a new OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['mail'] = $email;

            $mail = new PHPMailer(true); // true enables exceptions

            try {
                // Server settings
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
                $mail->Username   = $_SESSION['mymail'];                 // SMTP username
                $mail->Password   = $_SESSION['mypw'];                   // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;      // Enable TLS encryption
                $mail->Port       = 587;                                  // TCP port to connect to

                // Recipients
                $mail->setFrom($_SESSION['mymail'], 'OTP Verification');
                $mail->addAddress($email);                                 // Add a recipient

                // Content
                $mail->isHTML(true);                                      // Set email format to HTML
                $mail->Subject = "Your verification code";
                $mail->Body    = "<p>Dear user,</p> <h3>Your new OTP code is $otp</h3><br><p>With regards,</p><b>Parin</b>";

                // Send the email
                if (!$mail->send()) {
                    echo "<script>alert('Failed to resend OTP. Please try again later.');</script>";
                } else {
                    echo "<script>alert('A new OTP has been sent to your email.'); window.location.replace('verification.php');</script>";
                }

            } catch (Exception $e) {
                echo "<script>alert('Mailer Error: {$mail->ErrorInfo}');</script>";
            }

        } elseif (password_verify($password, $hashpassword)) {
            // Store user_id in the session after successful login
            $_SESSION['user_id'] = $fetch['user_id'];

            // Check for expiry date and send email if within 7 days
            $expiryDate = new DateTime($fetch['end_date']);
            $currentDate = new DateTime();
            $interval = $currentDate->diff($expiryDate);

            if ($interval->days <= 7 && $interval->invert == 0) { // Check if the expiry is within the next 7 days
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_SESSION['mymail'];
                    $mail->Password   = $_SESSION['mypw'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Recipients
                    $mail->setFrom($_SESSION['mymail'], 'Expiry Reminder');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Expiry Reminder";
                    $mail->Body    = "<p>Dear user,</p><p>Your warranty is expiring on " . $expiryDate->format('Y-m-d') . ". Please take action.</p><br><p>With regards,</p><b>Parin</b>";

                    // Send the email
                    if (!$mail->send()) {
                        echo "<script>alert('Failed to send expiry reminder. Please try again later.');</script>";
                    }

                } catch (Exception $e) {
                    echo "<script>alert('Mailer Error: {$mail->ErrorInfo}');</script>";
                }
            }

            // Redirect to the tracker page
            header('Location: tracker.php');
            exit; // Ensure no further code is executed after the redirect
        } else {
            echo "<script>alert('Email or password is invalid, please try again.');</script>";
        }        
    } else {
        echo "<script>alert('Email not found.');</script>";
    }

}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Login Form</title>
</head>
<body>
<nav class="navbar">
    <div class="logo"><a href="#">Warranty Tracker</a></div>
    <ul class="nav-links">
        <li><a href="https://github.com/parin-th/Warranty-Tracker" target="_blank">Github</a></li>
        <li><a href="register.php">Video Demo</a></li>
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
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form action="index.php" method="POST" name="login">
                            <div class="form-group row">
                                <label for="email_address" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                                <div class="col-md-6">
                                    <input type="text" id="email_address" class="form-control" name="email" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required>
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember"> Remember Me
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Login" name="login" class="btn btn-primary">
                                <a href="recover_psw.php" class="btn btn-link">Forgot Your Password?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('click', function() {
        if (password.type === "password") {
            password.type = 'text';
        } else {
            password.type = 'password';
        }
        this.classList.toggle('bi-eye');
    });
</script>
</body>
</html>
