<?php
session_start();
include 'connect.php';

// 1. Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $bookID = $_GET['id'];
    $userID = $_SESSION['user_id'];
    
    // Get user role
    $roleQuery = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$userID'");
    $role = $roleQuery->fetch_assoc();
    $isVisitor = ($role && $role['VisitorID'] != 0);

    // Dates
    $requestDate = date('Y-m-d'); // always set
    $borrowDate = $isVisitor ? NULL : date('Y-m-d'); // NULL for visitors
    $dueDate = $isVisitor ? NULL : date('Y-m-d', strtotime('+7 days')); // NULL for visitors

    // Status
    $status = $isVisitor ? 'Pending' : 'Borrowed';

    // 2. Check if book is in stock
    $checkStock = $conn->query("SELECT Quantity FROM books WHERE BookID = '$bookID'");
    $book = $checkStock->fetch_assoc();

    if ($book && $book['Quantity'] > 0) {

        // 3. Insert into borrow table
        $sqlBorrow = "INSERT INTO borrow (UserID, BookID, RequestDate, BorrowDate, DueDate, Status) 
                      VALUES ('$userID', '$bookID', '$requestDate', ".($borrowDate ? "'$borrowDate'" : "NULL").", ".($dueDate ? "'$dueDate'" : "NULL").", '$status')";

        if ($conn->query($sqlBorrow)) {

            // 4. Only decrement book quantity if not a visitor
            if (!$isVisitor) {
                $conn->query("UPDATE books SET Quantity = Quantity - 1 WHERE BookID = '$bookID'");
            }

            // 5. Notification
            if ($isVisitor) {
                $msg = "Your borrow request for the book has been submitted and is pending approval.";
                $redirect = "visitordashboard.php";
            } else {
                $msg = "You successfully borrowed a book. Please return it by " . $dueDate;
                $redirect = "userdashboard.php";
            }
            $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', '$msg', 'Unread')");

            echo "<script>alert('Success!'); window.location='$redirect';</script>";
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