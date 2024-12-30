<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'arduino_belt';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Initialize filters
$id_filter = isset($_GET['id_timbangan']) ? $_GET['id_timbangan'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Base SQL query
$sql = "SELECT * FROM belt WHERE 1=1";

// Apply filters if provided
if (!empty($id_filter)) {
    $sql .= " AND id_timbangan = '" . $conn->real_escape_string($id_filter) . "'";
}
if (!empty($start_date)) {
    $sql .= " AND DATE(timestamp) >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $sql .= " AND DATE(timestamp) <= '" . $conn->real_escape_string($end_date) . "'";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=belt_scale_data.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Start Excel output
    echo "ID Mesin\tCumulative 1\tCumulative 2\tTimestamp\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['id_timbangan'] . "\t" . $row['cum1'] . "\t" . $row['cum2'] . "\t" . $row['timestamp'] . "\n";
    }
} else {
    echo "No data found for the given filters.";
}

$conn->close();
?>