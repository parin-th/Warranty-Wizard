<?php
session_start();

include('connect/connection.php');
// Send OTP via PHPMailer
require 'vendor/autoload.php'; // Ensure this is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$mymail = $_SESSION['mymail'];
$mypw = $_SESSION['mypw'];
// Check if the resend OTP button was pressed
if (isset($_POST['resend_otp'])) {
    // Generate a new OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $email = $_SESSION['mail']; // Make sure email is already stored in the session

    // Send OTP via PHPMailer
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';

    $mail->Username =$mymail;
    $mail->Password =$mypw;

    $mail->setFrom($mymail, 'OTP Verification');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your verify code";
    $mail->Body = "<p>Dear user, </p> <h3>Your new OTP code is $otp <br></h3><br><br><p>With regards,</p><b>Parin</b>";

    if (!$mail->send()) {
        echo "<script>alert('Failed to resend OTP. Please try again later.');</script>";
    } else {
        echo "<script>
            alert('A new OTP has been sent to your email.');
            window.location.replace('verification.php');
        </script>";
    }
}

// Verification logic
if (isset($_POST["verify"])) {
    $otp = $_SESSION['otp'];
    $email = $_SESSION['mail'];
    $otp_code = $_POST['otp_code'];

    if ($otp != $otp_code) {
        echo "<script>alert('Invalid OTP code');</script>";
    } else {
        // Update the user's status in the SQLite database
        $stmt = $conn->prepare("UPDATE login SET status = 1 WHERE email = :email");
        $stmt->bindParam(':email', $email);
        if ($stmt->execute()) {
            echo "<script>
                alert('Verification successful. Please log in now.');
                window.location.replace('index.php');
            </script>";
        } else {
            echo "<script>alert('Failed to update status.');</script>";
        }
    }
}
?>

<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="Favicon.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Verification</title>
</head>
<body>
<nav class="navbar">
    <div class="logo">
        <a href="#">Warranty Tracker</a>
    </div>
    <ul class="nav-links">
        <li><a href="https://github.com/parin-th/Warranty-Tracker" target="_blank">Github</a></li>
        <li><a href="register.php" target="_blank">Video Demo</a></li>
        <li><a href=""></a></li>
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
                    <div class="card-header">Verification Account</div>
                    <div class="card-body">
                        <form action="#" method="POST">
                            <div class="form-group row">
                                <label for="email_address" class="col-md-4 col-form-label text-md-right">OTP Code</label>
                                <div class="col-md-6">
                                    <input type="text" id="otp" class="form-control" name="otp_code" required autofocus>
                                </div>
                            </div>
                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Verify" name="verify">
                                <input type="submit" value="Resend OTP" name="resend_otp" class="btn btn-link" onclick="disableOTPValidation()">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function disableOTPValidation() {
        document.getElementById('otp').removeAttribute('required');
    }
</script>
</body>
</html>
