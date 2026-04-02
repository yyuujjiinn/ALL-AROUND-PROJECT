<?php
include 'connect.php';
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    // Update the Status to 'Read' for this specific notification
    $conn->query("UPDATE notifications SET Status = 'Read' WHERE ID = '$id'");
}
?>