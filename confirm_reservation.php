<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['user_id']; // Get customer_id from the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $payment_method = $_POST['payment_method'];

    // Calculate the total payment
    $sql_price = "SELECT price_per_day FROM cars WHERE car_id = ?";
    $stmt = $conn->prepare($sql_price);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $stmt->bind_result($price_per_day);
    $stmt->fetch();
    $stmt->close();

    $days = (strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24);
    $total_payment = $price_per_day * $days;

    // Insert reservation
    $sql_reservation = "INSERT INTO reservations (customer_id, car_id, pickup_date, return_date, total_payment)
                        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_reservation);
    $stmt->bind_param("iissd", $customer_id, $car_id, $pickup_date, $return_date, $total_payment);

    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id;

        // Insert payment
        $sql_payment = "INSERT INTO payments (reservation_id, cash, PaymentMethod, PaymentStatus)
                        VALUES (?, ?, ?, 'Pending')";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("ids", $reservation_id, $total_payment, $payment_method);

        if ($stmt_payment->execute()) {
            echo "Reservation confirmed! Total Payment: $" . $total_payment;
        } else {
            echo "Error in payment: " . $stmt_payment->error;
        }
        $stmt_payment->close();
    } else {
        echo "Error in reservation: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
