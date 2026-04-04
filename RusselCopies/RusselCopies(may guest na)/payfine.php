<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $fineID = intval($_GET['id']);

    // Update fine status to Paid
    $sql = "UPDATE fines SET PaidStatus='Paid' WHERE FineID='$fineID'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Fine paid successfully!'); window.location='userdashboard.php';</script>";
    } else {
        echo "Error updating fine: " . $conn->error;
    }
}
?>
