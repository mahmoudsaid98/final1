<?php
session_start();

  
// Database connection
$servername = "localhost"; // Your server
$username = "root";        // Your username
$password = "";            // Your password
$dbname = "car_rental";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$locationFilter = isset($_GET['location']) ? $_GET['location'] : '';  // Use empty string if location is not set

// SQL query to fetch active cars and their locations (city from office table)
$sql = "SELECT car_id, company, model, year, fuel_type, no_seats, price_per_day, o.city AS location 
        FROM cars c
        JOIN office o ON c.office_id = o.office_id
        WHERE c.status = 'active'";

// Add location filter to SQL query if a location is selected
if (!empty($locationFilter)) {
    $sql .= " AND o.city = '" . $conn->real_escape_string($locationFilter) . "'";
}

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental</title>
    <link rel="stylesheet" href="search1.css">
</head>
<body>
    <div class="container">
        <!-- Car Table Section -->
        <div class="table-section">
            <h1>Car Rental Table</h1>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchBar" placeholder="Search...">
            </div>

            <!-- Filter Button -->
            <button id="filterButton">Apply Filters</button>

            <!-- Car Table -->
            <table id="carTable">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Model</th>
                        <th>Year</th>
                        <th>Fuel Type</th>
                        <th>No. Seats</th>
                        <th>Price per Day</th>
                        <th>Location</th> <!-- Location Column -->
                        <th>Book</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cars will be dynamically loaded here -->
                    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["company"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["model"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["year"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["fuel_type"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["no_seats"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["price_per_day"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["location"]) . "</td>";  // Display location (city)
            echo "<td><a href='reserve.php?company=" . urlencode($row["company"]) . 
                "&model=" . urlencode($row["model"]) . 
                "&year=" . urlencode($row["year"]) . 
                "&fuel_type=" . urlencode($row["fuel_type"]) . 
                "&no_seats=" . urlencode($row["no_seats"]) . 
                "&price_per_day=" . urlencode($row["price_per_day"]) . 
                "&location=" . urlencode($row["location"]) . 
                "&car_id=" . urlencode($row["car_id"]) . "'>
                <button class='book-btn'>Book</button></a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No cars available in the selected city</td></tr>";
    }
    ?>
                </tbody>
            </table>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <h2>Filter</h2>

            <!-- Fuel Type Filter -->
            <div>
                <h3>Fuel Type</h3>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox fuel" value="Electric">
                    <div class="checkmark"></div>
                    <span class="label">Electric</span>
                </label>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox fuel" value="Diesel">
                    <div class="checkmark"></div>
                    <span class="label">Diesel</span>
                </label>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox fuel" value="Hybrid">
                    <div class="checkmark"></div>
                    <span class="label">Hybrid</span>
                </label>
            </div>

            <!-- Location Filter -->
            <div>
                <h3>Location</h3>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox location" value="New York">
                    <div class="checkmark"></div>
                    <span class="label">New York</span>
                </label>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox location" value="Los Angeles">
                    <div class="checkmark"></div>
                    <span class="label">Los Angeles</span>
                </label>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox location" value="Chicago">
                    <div class="checkmark"></div>
                    <span class="label">Chicago</span>
                </label>
                <label class="checkbox-wrapper">
                    <input type="checkbox" class="filter-checkbox location" value="Miami">
                    <div class="checkmark"></div>
                    <span class="label">Miami</span>
                </label>
            </div>
        </div>
    </div>

    <!-- External JS -->
    <script src="search.js"></script> <!-- Link to external JS -->
</body>
</html>

<?php
// Close the connection
$conn->close();
?>