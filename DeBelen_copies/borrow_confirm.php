<?php
session_start();
include 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];

// 2. Verify user is a visitor
$roleQuery = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$uID'");
$role = $roleQuery->fetch_assoc();
if (!$role || $role['VisitorID'] == 0) {
    header("Location: userdashboard.php");
    exit();
}

// 3. Check if BorrowID is provided
if (!isset($_GET['id'])) {
    header("Location: visitordashboard.php");
    exit();
}

$borrowID = intval($_GET['id']);

// 4. Fetch borrow request to verify
$borrowQuery = $conn->query("
    SELECT b.BookID, b.Status 
    FROM borrow b 
    WHERE b.BorrowID = '$borrowID' AND b.UserID = '$uID'
");
$borrow = $borrowQuery->fetch_assoc();

if (!$borrow) {
    echo "<script>alert('Borrow request not found.'); window.location='visitordashboard.php';</script>";
    exit();
}

// 5. Only allow Confirm if status is Approved
if ($borrow['Status'] !== 'Approved') {
    echo "<script>alert('This borrow request cannot be confirmed.'); window.location='visitordashboard.php';</script>";
    exit();
}

// 6. Set BorrowDate and DueDate
$borrowDate = date('Y-m-d');
$dueDate = date('Y-m-d', strtotime('+7 days'));
$bookID = $borrow['BookID'];

// 7. Update borrow record
$conn->query("
    UPDATE borrow 
    SET BorrowDate='$borrowDate', DueDate='$dueDate', Status='Borrowed' 
    WHERE BorrowID='$borrowID'
");

// 8. Decrement book quantity
$conn->query("UPDATE books SET Quantity = Quantity - 1 WHERE BookID='$bookID'");

// 9. Insert notification
$msg = "You successfully borrowed the book. Please return it by $dueDate";
$conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$uID', '$msg', 'Unread')");

// 10. Redirect back
echo "<script>alert('Borrow confirmed successfully!'); window.location='visitordashboard.php';</script>";
exit();
?>
