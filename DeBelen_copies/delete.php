<?php
include 'connect.php';

// Kunin lahat ng records sa fines table
$result = $conn->query("SELECT * FROM fines");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    // Ipakita lahat ng column names
    while ($fieldinfo = $result->fetch_field()) {
        echo "<th>" . $fieldinfo->name . "</th>";
    }
    echo "</tr>";

    // Ipakita lahat ng rows
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach($row as $col => $val) {
            echo "<td>" . $val . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No records found in fines table.";
}

$conn->close();
?>
