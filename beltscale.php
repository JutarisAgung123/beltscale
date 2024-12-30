<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: login.php");
    exit; // Stop further execution of the script
}

$servername = "127.0.0.1";  // or your server IP
$username = "root";         // default XAMPP username is 'root'
$password = "";             // default XAMPP password is empty
$dbname = "arduino_belt";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id_timbangan']) && isset($_GET['cum1']) && isset($_GET['cum2'])) {
    $id_mesin = $_GET['id_timbangan'];
    $cumu_1 = $_GET['cum1'];
    $cumu_2 = $_GET['cum2'];

    // Insert the data into the database
    $sql = "INSERT INTO belt (id_timbangan, cum1, cum2) VALUES ('$id_mesin', '$cumu_1', '$cumu_2')";
    $conn->query($sql);
}

// Fetch filter inputs
$id_filter = isset($_GET['filter_id']) ? $_GET['filter_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Pagination variables
$rowsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $rowsPerPage;

// Build SQL query with filters
$sql = "SELECT * FROM belt WHERE 1=1";
if ($id_filter != '') {
    $sql .= " AND id_timbangan = '$id_filter'";
}
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND timestamp BETWEEN '" . $conn->real_escape_string($start_date) . " 00:00:00' 
             AND '" . $conn->real_escape_string($end_date) . " 23:59:59'";
} elseif (!empty($start_date)) {
    $sql .= " AND timestamp >= '" . $conn->real_escape_string($start_date) . " 00:00:00'";
} elseif (!empty($end_date)) {
    $sql .= " AND timestamp <= '" . $conn->real_escape_string($end_date) . " 23:59:59'";
}
$sql .= " LIMIT $rowsPerPage OFFSET $offset";
$result = $conn->query($sql);

// Fetch unique ID Mesin for dropdown
$idOptions = $conn->query("SELECT DISTINCT id_timbangan FROM belt");

// Total rows for pagination
$totalRowsQuery = "SELECT COUNT(*) as count FROM belt WHERE 1=1";
if ($id_filter != '') {
    $totalRowsQuery .= " AND id_timbangan = '$id_filter'";
}
if ($start_date != '' && $end_date != '') {
    $totalRowsQuery .= " AND timestamp BETWEEN '$start_date' AND '$end_date'";
}
$totalRows = $conn->query($totalRowsQuery)->fetch_assoc()['count'];
$totalPages = ceil($totalRows / $rowsPerPage);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Data Belt Scale</title>";
echo "<link rel='stylesheet' type='text/css' href='home.css'>"; // Link the CSS file
echo "</head>";
echo "<body>";
echo "<div class='header'>";
echo "<img src='Sungebudi.png' alt='Logo' class='logo'>"; // Replace 'logo.png' with your logo file path
echo "<h1>DATA BELT SCALES</h1>";
echo "</div>";

echo "<div class='username'>Welcome, " . $_SESSION['username'] ;
echo "<br></br>";
echo "<a href = 'logout.php'>Log Out</a></div>";

echo "<form method='GET' action='' style='margin-bottom: 20px; text-align: center;'>";
echo "<label for='filter_id'>Filter by ID Mesin:</label>";
echo "<select name='filter_id' id='filter_id'>";
echo "<option value=''>All</option>";
while ($row = $idOptions->fetch_assoc()) {
    $selected = isset($_GET['filter_id']) && $_GET['filter_id'] == $row['id_timbangan'] ? 'selected' : '';
    echo "<option value='" . $row['id_timbangan'] . "' $selected>" . $row['id_timbangan'] . "</option>";
}
echo "</select>";
echo "<label for='start_date'>Start Date:</label>";
echo "<input type='date' name='start_date' id='start_date' value='$start_date'>";
echo "<label for='end_date'>End Date:</label>";
echo "<input type='date' name='end_date' id='end_date' value='$end_date'>";
echo "<input type='submit' value='Filter'>";
echo "</form>";

if ($result->num_rows > 0) {
    echo "<table border='1' style='margin: 0 auto;'>";
    echo "<tr>";
    echo "<th>ID Mesin</th>";
    echo "<th>Cumulative 1</th>";
    echo "<th>Cumulative 2</th>";
    echo "<th>Timestamp</th>";
    echo "</tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id_timbangan"] . "</td><td>" . $row["cum1"] . "</td><td>" . $row["cum2"] . "</td><td>" . $row["timestamp"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align: center;'>No results found.</p>";
}

// Pagination links
echo "<div style='text-align: center; margin-top: 20px;'>";
if ($page > 1) {
    echo "<a href='?page=" . ($page - 1) . "&filter_id=$id_filter&start_date=$start_date&end_date=$end_date' style='margin-right: 10px;'>Previous</a>";
}
for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == $page) {
        echo "<strong style='margin-right: 10px;'>$i</strong>";
    } else {
        echo "<a href='?page=$i&filter_id=$id_filter&start_date=$start_date&end_date=$end_date' style='margin-right: 10px;'>$i</a>";
    }
}
if ($page < $totalPages) {
    echo "<a href='?page=" . ($page + 1) . "&filter_id=$id_filter&start_date=$start_date&end_date=$end_date'>Next</a>";
}
echo "</div>";

echo "<div class='export'>";
echo "<form method='GET' action='export.php' onsubmit = 'return confirmExport();'>";
echo "<input type='hidden' name='id_timbangan' value='" . htmlspecialchars($id_filter) . "'>";
echo "<input type='hidden' name='start_date' value='" . htmlspecialchars($start_date) . "'>";
echo "<input type='hidden' name='end_date' value='" . htmlspecialchars($end_date) . "'>";
echo "<button type='submit'>Export to Excel</button>";
echo "</form>";
echo "</div>";

echo "</body>";
echo "</html>";

echo "<script>
function confirmExport() {
    alert('Your data is being exported...');
    return true; // Proceed with the form submission
}
</script>";

// Close connection
$conn->close();
?>