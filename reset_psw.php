<?php 
session_start();
include('connect/connection.php'); // Ensure this file is updated to use PDO with SQLite
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />

    <title>Login Form</title>
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
    <div class="cotainer">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset Your Password</div>
                    <div class="card-body">
                        <form action="#" method="POST" name="login">

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">New Password</label>
                                <div class="col-md-6">
                                    <input type="password" id="password" class="form-control" name="password" required autofocus>
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                </div>
                            </div>

                            <div class="col-md-6 offset-md-4">
                                <input type="submit" value="Reset" name="reset">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>

<?php
if(isset($_POST["reset"])){
    include('connect/connection.php');
    $psw = $_POST["password"];
    $token = $_SESSION['token'];
    $Email = $_SESSION['email'];
    $hash = password_hash($psw, PASSWORD_DEFAULT); // Hash the new password

    // Fetch user from SQLite database
    $stmt = $conn->prepare("SELECT * FROM login WHERE email = :email");
    $stmt->bindParam(':email', $Email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user) {
        // Update password in the database
        $stmt = $conn->prepare("UPDATE login SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':email', $Email);
        if($stmt->execute()) {
            ?>
            <script>
                window.location.replace("tracker.php");
                alert("Your password has been successfully reset");
            </script>
            <?php
        } else {
            ?>
            <script>
                alert("Password reset failed. Please try again.");
            </script>
            <?php
        }
    } else {
        ?>
        <script>
            alert("User not found. Please try again.");
        </script>
        <?php
    }
}
?>

<script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('click', function() {
        const type = password.type === "password" ? "text" : "password";
        password.type = type;
        this.classList.toggle('bi-eye');
    });
</script>
