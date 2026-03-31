<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['borrowID'])) {
    $borrowID = $_GET['borrowID'];
    $returnDate = date('Y-m-d');

    // 1. Update borrow record
    $conn->query("UPDATE borrow SET ReturnDate='$returnDate', Status='Returned' WHERE BorrowID='$borrowID'");

    // 2. Get borrow info (DueDate + BookID)
    $borrow = $conn->query("SELECT DueDate, BookID FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc();
    $dueDate = $borrow['DueDate'];
    $bookID = $borrow['BookID'];

    // 3. Check overdue → insert fine if late
    $daysLate = (strtotime($returnDate) - strtotime($dueDate)) / (60*60*24);
    if ($daysLate > 0) {
        $amount = $daysLate * 20; // halimbawa ₱20 per day late
        $conn->query("INSERT INTO fines (BorrowID, Amount, PaidStatus, FineType) 
                      VALUES ('$borrowID', '$amount', 'Unpaid', 'Late')");
    }

    // 4. Lost book scenario (kapag may flag na lost=1 sa URL)
    if (isset($_GET['lost']) && $_GET['lost'] == 1) {
        // option 1: pay original price
        $priceRow = $conn->query("SELECT Price FROM books WHERE BookID='$bookID'")->fetch_assoc();
        $bookPrice = $priceRow['Price'];

       // Piliin lang kung pay o replace
        if (isset($_GET['lostOption']) && $_GET['lostOption'] == 'pay') {
            $conn->query("INSERT INTO fines (BorrowID, Amount, PaidStatus, FineType) 
                          VALUES ('$borrowID', '$bookPrice', 'Unpaid', 'Lost-Pay')");
        } else if (isset($_GET['lostOption']) && $_GET['lostOption'] == 'replace') {
            $conn->query("INSERT INTO fines (BorrowID, Amount, PaidStatus, FineType) 
                          VALUES ('$borrowID', 0, 'Pending Replacement', 'Lost-Replace')");
        }

        // Update borrow status to Lost
        $conn->query("UPDATE borrow SET Status='Lost' WHERE BorrowID='$borrowID'");
    }

    // 5. Redirect message
    echo "<script>alert('Book return processed successfully.'); window.location='borrow_records.php';</script>";
}
?>
