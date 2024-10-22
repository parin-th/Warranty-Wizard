<?php
session_start();
include('connect/connection.php');

// Send OTP via PHPMailer
require 'vendor/autoload.php'; // Ensure this is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

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

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

    <title>Password Recovery</title>
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
    <div class="cotainer">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Password Recover</div>
                    <div class="card-body">
                        <form action="#" method="POST" name="recover_psw">
                            <div class="form-group row">
                                <label for="email_address" class="col-md-4 col-form-label text-md-right">E-Mail Address</label>
                                <div class="col-md-6">
                                    <input type="text" id="email_address" class="form-control" name="email" required autofocus>
                                </div>
                            </div>

                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Recover" name="recover">
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>

<?php 
if (isset($_POST["recover"])) {
    include('connect/connection.php');
    $email = trim($_POST["email"]);

    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT * FROM login WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fetch) {
        ?>
        <script>
            alert("Sorry, no email exists.");
        </script>
        <?php
    } else if ($fetch["status"] == 0) {
        ?>
        <script>
            alert("Sorry, your account must be verified first before you can recover your password!");
            window.location.replace("index.php");
        </script>
        <?php
    } else {
        // Generate a token
        $token = bin2hex(random_bytes(50));
        $mymail = $_SESSION['mymail'];
        $mypw = $_SESSION['mypw'];

        // Store token in session
        $_SESSION['token'] = $token;
        $_SESSION['email'] = $email;

        // Send the email with PHPMailer
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';

        $mail->Username = $mymail;
        $mail->Password = $mypw;

        $mail->setFrom($mymail, 'Password Reset');
        $mail->addAddress($_POST["email"]);

        // HTML email body
        $mail->isHTML(true);
        $mail->Subject = "Recover your password";
        $mail->Body = "<b>Dear User</b>
        <h3>We received a request to reset your password.</h3>
        <p>Kindly click the below link to reset your password</p>
        <a href='http://localhost/login-System/reset_psw.php?token=$token'>Reset Password</a>
        <br><br>
        <p>With regards,</p>
        <b>Your Website</b>";

        if (!$mail->send()) {
            ?>
            <script>
                alert("Invalid Email");
            </script>
            <?php
        } else {
            ?>
            <script>
                window.location.replace("notification.html");
            </script>
            <?php
        }
    }
}
?>
