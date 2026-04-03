<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) exit();

$borrowID = intval($_GET['id']);

// Update status to Approved
$conn->query("UPDATE borrow SET Status='Approved' WHERE BorrowID='$borrowID'");

// Notify visitor
$borrowInfo = $conn->query("SELECT UserID FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc();
$userID = $borrowInfo['UserID'];
$conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', 'Your borrow request has been approved!', 'Unread')");

echo "<script>alert('Request Approved!'); window.location='borrow_records.php';</script>";
?>
