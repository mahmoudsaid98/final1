<?php
// Database connection
session_start();
if (!isset($_SESSION['admin_id'])) {
    // إذا لم يكن المستخدم قد سجل الدخول، إعادة التوجيه إلى صفحة تسجيل الدخول
    header("Location: adminlogin.php");
    exit;
  }
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check the action parameter
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'reservations_period':
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];

            $sql = "SELECT r.*, c.company, c.model, c.plate_id, cu.fname, cu.lname, cu.phone 
                    FROM reservations r
                    JOIN cars c ON r.car_id = c.car_id
                    JOIN customers cu ON r.customer_id = cu.customer_id
                    WHERE r.reservation_date BETWEEN ? AND ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h1>Reservations from $start_date to $end_date</h1>";
            echo generateStyledTable($result);
            break;

        case 'car_status':
            $status_date = $_GET['status_date'];

            $sql = "SELECT c.car_id, c.company, c.model, c.plate_id, 
                           CASE 
                               WHEN c.car_id IN (SELECT r.car_id FROM reservations r WHERE ? BETWEEN r.pickup_date AND r.return_date) THEN 'Rented' 
                               ELSE c.status 
                           END AS status
                    FROM cars c";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $status_date);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h1>Car Status on $status_date</h1>";
            echo generateStyledTable($result);
            break;

        case 'customer_reservations':
            $customer_id = $_GET['customer_id'];

            $sql = "SELECT r.*, c.model, c.plate_id, cu.fname, cu.lname, cu.phone 
                    FROM reservations r
                    JOIN cars c ON r.car_id = c.car_id
                    JOIN customers cu ON r.customer_id = cu.customer_id
                    WHERE cu.customer_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h1>Reservations for Customer ID $customer_id</h1>";
            echo generateStyledTable($result);
            break;

        case 'daily_payments':
            $payment_start_date = $_GET['payment_start_date'];
            $payment_end_date = $_GET['payment_end_date'];

            $sql = "SELECT p.*, r.pickup_date, r.return_date, cu.fname, cu.lname 
                    FROM payments p
                    JOIN reservations r ON p.reservation_id = r.reservation_id
                    JOIN customers cu ON r.customer_id = cu.customer_id
                    WHERE p.payment_date BETWEEN ? AND ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $payment_start_date, $payment_end_date);
            $stmt->execute();
            $result = $stmt->get_result();

            echo "<h1>Payments from $payment_start_date to $payment_end_date</h1>";
            echo generateStyledTable($result);
            break;

        default:
            echo "<h1>Invalid Action</h1>";
    }
}

$conn->close();

// Function to generate styled HTML table
function generateStyledTable($result) {
    $styles = "<style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        h1 {
            font-family: Arial, sans-serif;
            color: #333;
        }
    </style>";

    if ($result->num_rows > 0) {
        $output = $styles . "<table><tr>";

        // Table headers
        while ($field = $result->fetch_field()) {
            $output .= "<th>" . htmlspecialchars($field->name) . "</th>";
        }

        $output .= "</tr>";

        // Table rows
        while ($row = $result->fetch_assoc()) {
            $output .= "<tr>";
            foreach ($row as $value) {
                $output .= "<td>" . htmlspecialchars($value) . "</td>";
            }
            $output .= "</tr>";
        }

        $output .= "</table>";
    } else {
        $output = $styles . "<p>No records found.</p>";
    }

    return $output;
}
?>