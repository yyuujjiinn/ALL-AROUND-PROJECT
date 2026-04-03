<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) exit();

$borrowID = intval($_GET['id']);
$conn->query("UPDATE borrow SET Status='Rejected' WHERE BorrowID='$borrowID'");

$borrowInfo = $conn->query("SELECT UserID FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc();
$userID = $borrowInfo['UserID'];
$conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', 'Your borrow request has been rejected.', 'Unread')");

echo "<script>alert('Request Rejected!'); window.location='borrow_records.php';</script>";
?>
