<?php
    session_start();
    include('connect/connection.php'); // Ensure this file is updated to use PDO with SQLite
    // Send OTP via PHPMailer
    require 'vendor/autoload.php'; // Ensure this is correct

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }

    // Save the user id in a variable
    $user_id = $_SESSION['user_id'];

    // Save the users current items in a variable
    // Prepare the SQL statement with a placeholder
    $stmt = $conn->prepare("SELECT * FROM items WHERE user_id = ?");

    // Bind the value of $user_id to the placeholder
    $stmt->execute([$user_id]);

    // Fetch the results
    $items = $stmt->fetchAll();

    // Get today's date
    $today = date("Y-m-d");

    // Query to get the last email sent date
    $last_email_query = "SELECT last_email_sent FROM login WHERE user_id = :user_id";
    $last_email_stmt = $conn->prepare($last_email_query);
    $last_email_stmt->bindParam(':user_id', $user_id);
    $last_email_stmt->execute();
    $last_email_sent = $last_email_stmt->fetchColumn();

    // Only send the email if it was not sent today
    if ($last_email_sent != $today) {
        // Calculate the date one week from now
        $one_week_from_now = date('Y-m-d', strtotime('+1 week'));
        $six_days_from_now = date('Y-m-d', strtotime('+6 days'));
        $five_days_from_now = date('Y-m-d', strtotime('+5 days'));
        $four_days_from_now = date('Y-m-d', strtotime('+4 days'));
        $three_days_from_now = date('Y-m-d', strtotime('+3 days'));
        $two_days_from_now = date('Y-m-d', strtotime('+2 days'));
        $one_days_from_now = date('Y-m-d', strtotime('+1 day'));

        // Query to find items with end dates one week from today for all users
        $sql = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :one_week_from_now";

        /***
        $sqlb = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :six_days_from_now";

        $sqlc = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :five_days_from_now";
        $sqld = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :four_days_from_now";
        
        $sqle = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :three_days_from_now";
        $sqlf = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :two_days_from_now";
        $sqlg = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :one_days_from_now";
        $sqlh = "SELECT items.iname, items.end_date, login.email, login.name 
                FROM items 
                JOIN login ON items.user_id = login.user_id 
                WHERE items.end_date = :today";
        */
                $stmt = $conn->prepare($sql);
        $stmt->bindParam(':one_week_from_now', $one_week_from_now);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {

            foreach ($results as $row) {
                $item_name = $row['iname'];
                $end_date = $row['end_date'];
                $user_email = $row['email'];
                $user_name = $row['name'];

                // Set up email parameters (same as before)
                $mymail = 'parinthakkar23@gmail.com';
                $mypw = 'uept jqrw oojg yfmf';

                // Initialize the email
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';

                // Your email credentials
                $mail->Username = $mymail;
                $mail->Password = $mypw;

                // Email content
                $mail->setFrom($mymail, 'Warranty Tracker');
                $mail->addAddress($user_email);

                $mail->isHTML(true);
                $mail->Subject = "Reminder: Warranty Ending Soon for $item_name";
                $mail->Body = "
                    <p>Dear $user_name,</p>
                    <p>This is a reminder that the warranty for <strong>$item_name</strong> is set to expire on <strong>$end_date</strong>.</p>
                    <p>Don't forget to make use of your warranty before it ends.</p>
                    <p>Best regards,<br>Warranty Tracker Team</p>";

                // Attempt to send the email
                if ($mail->send()) {
                    // Update the last email sent date for this user
                    $update_sql = "UPDATE login SET last_email_sent = :today WHERE user_id = :user_id";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bindParam(':today', $today);
                    $update_stmt->bindParam(':user_id', $user_id);
                    $update_stmt->execute();
                } else {
                    echo "Error sending email: " . $mail->ErrorInfo;
                }
            }
        }
    }

    // Handle delete action
    if (isset($_GET['delete'])) {
        $item_id = $_GET['delete'];
        if (!empty($item_id)) {
            $sql = "DELETE FROM items WHERE id = :item_id AND user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':item_id', $item_id);
            $stmt->bindParam(':user_id', $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('Item deleted successfully!'); window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
            } else {
                echo "<script>alert('Error deleting item.');</script>";
            }
        }
    }

    // Handle form submission to update the item
    if (isset($_POST['update_item'])) {
        $item_id = $_POST['id'];
        $name = $_POST['iname'];
        $location = $_POST['location'];
        $start_date = $_POST['date'];
        $period = $_POST['period'];
        $link = $_POST['link'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if (!empty($item_id) && !empty($name) && !empty($location) && !empty($start_date) && !empty($period)) {
            // Calculate new end date
            $end_date = date('Y-m-d', strtotime("+$period months", strtotime($start_date)));

            $sql = "UPDATE items 
                    SET iname = :name, location = :location, start_date = :start_date, 
                        period = :period, end_date = :end_date, link = :link, notes = :notes 
                    WHERE id = :item_id AND user_id = :user_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':period', $period);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':item_id', $item_id);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                echo "<script>alert('Item updated successfully!'); window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
            } else {
                echo "<script>alert('Error updating item.');</script>";
            }
        }
    }

    // Handle form submission to add a new item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_item'])) {
        if (isset($_POST['iname'], $_POST['location'], $_POST['date'], $_POST['period'])) {
            $name = $_POST['iname'];
            $location = $_POST['location'];
            $start_date = $_POST['date'];
            $period = $_POST['period'];
            $link = $_POST['link'] ?? '';
            $notes = $_POST['notes'] ?? '';

            $current_date = date('Y-m-d');
            if (strtotime($start_date) === false || $start_date > $current_date) {
                echo "<script>alert('Invalid date format or date is in the future.');</script>";
            } else {
                $end_date = date('Y-m-d', strtotime("+$period months", strtotime($start_date)));

                $sql = "INSERT INTO items (user_id, iname, location, start_date, period, end_date, link, notes)
                        VALUES (:user_id, :iname, :location, :start_date, :period, :end_date, :link, :notes)";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':iname', $name);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':period', $period);
                $stmt->bindParam(':end_date', $end_date);
                $stmt->bindParam(':link', $link);
                $stmt->bindParam(':notes', $notes);

                if ($stmt->execute()) {
                    echo "<script>alert('Item added successfully!'); window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
                } else {
                    echo "<script>alert('Error adding item.');</script>";
                }
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="styler.css" rel="stylesheet">
    <script src="tutorial.js"></script>
    <title>My Items</title>
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

    <div class="container">
        <!-- Column 1: My Items -->
        <div class="column">
            <h2>My Items</h2>
            <button class="btn" onclick="toggleForm()">Add Item</button>

            <!-- Add item form -->
            <div id="addItemForm" class="hidden">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <label>Product Name<span class="asterisk">*</span>: <input type="text" name="iname" required></label>
                <label>Purchase Location<span class="asterisk">*</span>: <input type="text" name="location" required></label>
                <label>Purchase Date<span class="asterisk">*</span>: <input type="date" name="date" required></label>
                <label>Warranty Period (Months)<span class="asterisk">*</span>: <input type="number" name="period" required></label>
                <label>Claim Link: <input type="url" name="link"></label>
                <label>Notes: <textarea name="notes"></textarea></label>

                    <button type="submit" class="btn">Submit</button>
                </form>
            </div>

            <!-- Display items -->
                <?php foreach ($items as $row): ?>
                <div class="item" onclick="toggleDetails(<?php echo $row['id']; ?>)">
                    <a href="?edit=<?php echo $row['id']; ?>"><?php echo $row['iname']; ?></a> <!-- Clickable name for editing -->
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');" style="color:red; text-decoration:none;">[Delete]</a> <!-- Link for deletion -->

                    <div id="details-<?php echo $row['id']; ?>" class="hidden">
                        <p><strong>Location:</strong> <?php echo $row['location']; ?></p>
                        <p><strong>Start Date:</strong> <?php echo $row['start_date']; ?></p>
                        <p><strong>End Date:</strong> <?php echo $row['end_date']; ?></p>
                        <p><strong>Link:</strong> <a href="<?php echo $row['link']; ?>" target="_blank">Link</a></p>
                        <p><strong>Notes:</strong> <?php echo $row['notes']; ?></p>
                    </div>
                    
                    

                </div>
            <?php endforeach; ?>

            <!-- Edit item form (only shown if editing) -->
            <?php if (isset($item)): ?> <!-- If editing an item -->
            <div id="editItemForm">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <label>Product Name<span class="asterisk">*</span>: <input type="text" name="iname" required></label>
                    <label>Purchase Location<span class="asterisk">*</span>: <input type="text" name="location" required></label>
                    <label>Purchase Date<span class="asterisk">*</span>: <input type="date" name="date" required></label>
                    <label>Warranty Period (Months)<span class="asterisk">*</span>: <input type="number" name="period" required></label>
                    <label>Claim Link: <input type="url" name="link"></label>
                    <label>Notes: <textarea name="notes"></textarea></label>
                    <button type="submit" name="update_item" class="btn">Update</button>
                </form>
            </div>
            <?php endif; ?>

        </div>

        <!-- Column 2: Calendar -->
        <div class="column calendar">
            <h2>Warranty Calendar</h2>
            <div id="calendar"></div>
        </div>
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById('addItemForm');
            form.classList.toggle('hidden');
        }

        function toggleDetails(id) {
            var details = document.getElementById('details-' + id);
            details.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php
                    foreach ($items as $row) {
                        echo "{ title: '".addslashes($row['iname'])."', start: '".$row['end_date']."', end: '".$row['end_date']."' },";
                    }
                    ?>
                ]

            });
            calendar.render();
        });




    </script>
</body>
</html>