<?php
// Start the session
session_start();

// Check if form data is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the data from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection details
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "car_rental";

    // Create database connection
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Check the database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to check if the user exists
    $sql = "SELECT * FROM customers WHERE cust_email = ?";

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // Bind the email parameter
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the user data
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['cust_password'])) {
            // Successful login
            $_SESSION['user_id'] = $row['customer_id']; // Store customer_id in the session
            $_SESSION['user_name'] = $row['cust_name']; // Optionally store the user's name
            $_SESSION['success_message'] = "Login successful!";
            header("Location: show.html"); // Redirect to dashboard or home page
            exit;
        } else {
            $_SESSION['error_message'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error_message'] = "No user found with that email.";
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="welcome.css">
</head>
<body>
    <div class="main">
        <h1>Log in</h1>
        <br>

        <!-- Login form -->
        <form action="index.php" method="POST">
            <br>
            <input type="email" name="email" id="email" placeholder="Enter your email" required><br><br>

            <input type="password" name="password" id="password" placeholder="Enter your password" required><br><br>

            <input type="submit" name="submit" id="login" value="Log in"><br>
        </form>

        <!-- Display success or error messages -->
        <?php
        if (isset($_SESSION['error_message'])) {
            echo "<p style='color: red;'>" . $_SESSION['error_message'] . "</p>";
            unset($_SESSION['error_message']); // Remove the message after displaying it
        }
        if (isset($_SESSION['success_message'])) {
            echo "<p style='color: green;'>" . $_SESSION['success_message'] . "</p>";
            unset($_SESSION['success_message']); // Remove the message after displaying it
        }
        ?>

        <br><h4>Don't have an account?</h4>

        <!-- Registration link -->
        <a id="register-link" href="register.php">
            <button type="button" id="register">Register</button>
        </a>
        <br><br>

        <h4>Admin</h4>
        <!-- Admin login link -->
        <a id="login-admin" href="adminlogin.php">Login as Admin</a>
    </div>
</body>
</html>
