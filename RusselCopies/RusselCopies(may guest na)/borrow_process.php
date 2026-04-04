<?php
session_start();
include 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ✅ BLOCK GUESTS (NEW)
if (isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true) {
    echo "<script>alert('Guests cannot borrow books.'); window.location='view_books.php';</script>";
    exit();
}

if (isset($_GET['id'])) {
    $bookID = $_GET['id'];
    $userID = $_SESSION['user_id'];
    $borrowDate = date('Y-m-d');
    $dueDate = date('Y-m-d', strtotime('+7 days')); // 1 week
    $status = 'Pending'; // ✅ CHANGED

    // 2. Check stock
    $checkStock = $conn->query("SELECT Quantity FROM books WHERE BookID = '$bookID'");
    $book = $checkStock->fetch_assoc();

    if ($book && $book['Quantity'] > 0) {

        // 3. Insert as PENDING only
        $sqlBorrow = "INSERT INTO borrow (UserID, BookID, BorrowDate, DueDate, Status) 
                      VALUES ('$userID', '$bookID', '$borrowDate', '$dueDate', '$status')";

        if ($conn->query($sqlBorrow)) {

            // ✅ Notify ADMIN
            $msg = "New borrow request for Book ID: $bookID";

            // adjust if your admin ID is different
            $adminID = 1;

            $conn->query("INSERT INTO notifications (UserID, Message, Status) 
                          VALUES ('$adminID', '$msg', 'Unread')");

            echo "<script>alert('Request sent! Waiting for admin approval.'); window.location='view_books.php';</script>";

        } else {
            echo "Error: " . $conn->error;
        }

    } else {
        echo "<script>alert('Sorry, this book is no longer available.'); window.location='view_books.php';</script>";
    }

} else {
    header("Location: view_books.php");
}
?>