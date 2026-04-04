<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login first.";
    exit();
}

if (isset($_POST['book_id'])) {
    $bookID = intval($_POST['book_id']);
    $userID = $_SESSION['user_id'];
    $date = date('Y-m-d H:i:s');

    // 1. Check kung may existing pending reservation na ang user sa librong ito
    $check = $conn->query("SELECT * FROM reservations WHERE BookID = '$bookID' AND UserID = '$userID' AND Status = 'Pending'");
    
    if ($check->num_rows > 0) {
        echo "You are already in the waiting list for this book.";
    } else {
        // Kunin ang title ng libro para sa notification message
        $bookQuery = $conn->query("SELECT BookTitle FROM books WHERE BookID = '$bookID'");
        $bookData = $bookQuery->fetch_assoc();
        $bookTitle = $bookData['BookTitle'];

        // 2. Insert sa reservations table
        $sql = "INSERT INTO reservations (BookID, UserID, ReservationDate, Status) 
                VALUES ('$bookID', '$userID', '$date', 'Pending')";
        
        if ($conn->query($sql)) {
            // --- A. NOTIFICATION PARA SA USER ---
            $msgUser = "You have successfully reserved the book: $bookTitle. We'll notify you once it's returned.";
            $safeMsgUser = mysqli_real_escape_string($conn, $msgUser);
            $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', '$safeMsgUser', 'Unread')");

            // --- B. NOTIFICATION PARA SA ADMIN (Idinagdag) ---
            // Hinahanap ang RoleID ng account na may AdminID (hindi 0)
            $adminFinder = $conn->query("SELECT RoleID FROM user_roles WHERE AdminID != 0 LIMIT 1");
            if ($adminFinder->num_rows > 0) {
                $admin = $adminFinder->fetch_assoc();
                $adminID = $admin['RoleID'];
                
                // Kunin ang pangalan ng user na nag-reserve para sa admin alert
                $userQuery = $conn->query("SELECT firstname, lastname FROM user WHERE RoleID = '$userID'");
                $userData = $userQuery->fetch_assoc();
                $userName = $userData['firstname'] . " " . $userData['lastname'];

                $msgAdmin = "New Reservation Alert: $userName has reserved '$bookTitle'.";
                $safeMsgAdmin = mysqli_real_escape_string($conn, $msgAdmin);
                
                $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$adminID', '$safeMsgAdmin', 'Unread')");
            }

            echo "Reservation successful! Admin has been notified.";
        } else {
            echo "Database Error: " . $conn->error;
        }
    }
}
?>