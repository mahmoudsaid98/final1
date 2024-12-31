<?php

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

// Get car details from the query parameters
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];

    // Fetch car details
    //$stmt = $conn->prepare("SELECT company, model, year, fuel_type, no_seats, price_per_day FROM cars WHERE car_id = ?");
    $stmt = $conn->prepare("SELECT company, model, year, fuel_type, no_seats, price_per_day , city , location FROM cars c ,office o WHERE c.office_id = o.office_id AND car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
} else {
    die("Car not specified.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Car</title>
    <link rel="stylesheet" href="reserve.css">
</head>
<body>
    <div class="container">
        <h1>Reserve a Car</h1>

        <!-- Display Car Details -->
        <div class="car-details">
            <h2>Car Details</h2>
            <p><strong>Company:</strong> <?= htmlspecialchars($car['company']) ?></p>
            <p><strong>Model:</strong> <?= htmlspecialchars($car['model']) ?></p>
            <p><strong>Year:</strong> <?= htmlspecialchars($car['year']) ?></p>
            <p><strong>Fuel Type:</strong> <?= htmlspecialchars($car['fuel_type']) ?></p>
            <p><strong>No. of Seats:</strong> <?= htmlspecialchars($car['no_seats']) ?></p>
            <p><strong>Price per Day:</strong> $<?= number_format($car['price_per_day'], 2) ?></p>
            <p><strong>city:</strong> <?= htmlspecialchars($car['city']) ?></p>
            <p><strong>office location:</strong> <?= htmlspecialchars($car['location']) ?></p>
        </div>

        <!-- Reservation Form -->
        <form id="reserveForm" method="POST" action="confirm_reservation.php">
            <input type="hidden" name="car_id" value="<?= htmlspecialchars($car_id) ?>">
            <div>
                <label for="pickupDate">Pick-Up Date:</label>
                <input type="date" id="pickupDate" name="pickup_date" required>
            </div>
            <div>
                <label for="returnDate">Return Date:</label>
                <input type="date" id="returnDate" name="return_date" required>
            </div>
            <div>
                <label for="paymentMethod">Payment Method:</label>
                <select id="paymentMethod" name="payment_method" required>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Cash">Cash</option>
                    <option value="Online Transfer">Online Transfer</option>
                </select>
            </div>
            <div>
                <p><strong>Total Payment:</strong> $<span id="totalPayment">0.00</span></p>
            </div>
            <button type="submit">Confirm Reservation</button>
        </form>
    </div>

    
    <script>
    const pricePerDay = <?= $car['price_per_day'] ?>;
    const pickupDateInput = document.getElementById('pickupDate');
    const returnDateInput = document.getElementById('returnDate');
    const totalPaymentDisplay = document.getElementById('totalPayment');

    // Set minimum dates to today's date
    const today = new Date().toISOString().split('T')[0];
    pickupDateInput.setAttribute('min', today);
    returnDateInput.setAttribute('min', today);

    function calculateTotal() {
        const pickupDate = new Date(pickupDateInput.value);
        const returnDate = new Date(returnDateInput.value);

        if (pickupDate && returnDate) {
            if (returnDate < pickupDate) {
                alert("Return Date cannot be earlier than Pick-Up Date.");
                returnDateInput.value = ""; // Reset return date field
                totalPaymentDisplay.textContent = "0.00";
                return;
            }

            if (returnDate > pickupDate) {
                const diffTime = returnDate - pickupDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const totalPayment = diffDays * pricePerDay;
                totalPaymentDisplay.textContent = totalPayment.toFixed(2);
            }
        }
    }

    pickupDateInput.addEventListener('change', () => {
        // Update the minimum return date to match the selected pickup date
        const pickupDate = pickupDateInput.value;
        returnDateInput.setAttribute('min', pickupDate);
        calculateTotal();
    });

    returnDateInput.addEventListener('change', calculateTotal);
</script>

</body>
</html>

<?php
$conn->close();
?>