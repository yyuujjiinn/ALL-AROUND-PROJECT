<?php
session_start();
include 'connect.php';

// 1. Admin Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Handle Return Action
if (isset($_GET['return_id'])) {
    $borrowID = intval($_GET['return_id']);

    // Get book info
    $info = $conn->query("SELECT BookID, DueDate FROM borrow WHERE BorrowID = '$borrowID'")->fetch_assoc();
    $bookID = $info['BookID'];
    $dueDate = $info['DueDate'];

    // Update borrow status
    $conn->query("UPDATE borrow SET Status = 'Returned', Returndate = CURDATE() WHERE BorrowID = '$borrowID'");

    // Increment book quantity
    $conn->query("UPDATE books SET Quantity = Quantity + 1 WHERE BookID = '$bookID'");

    // Check overdue
    $dates = $conn->query("SELECT DueDate, Returndate FROM borrow WHERE BorrowID = '$borrowID'")->fetch_assoc();
    $returnDate = $dates['Returndate'];
    $daysLate = (strtotime($returnDate) - strtotime($dueDate)) / (60*60*24);

    if ($daysLate > 0) {
        $amount = $daysLate * 20; // ₱20 per day
        $conn->query("INSERT INTO fines (BorrowID, Amount, Status, Type) VALUES ('$borrowID', '$amount', 'Unpaid', 'Late')");
    }

    // ==========================================================
    // BAGONG DAGDAG: RESERVATION NOTIFICATION LOGIC
    // ==========================================================
    
    // Hanapin ang pinaka-unang reservation na 'Pending' para sa librong ito
    $checkRes = $conn->query("SELECT r.ReservationID, r.UserID, b.BookTitle 
                              FROM reservations r 
                              JOIN books b ON r.BookID = b.BookID 
                              WHERE r.BookID = '$bookID' AND r.Status = 'Pending' 
                              ORDER BY r.ReservationDate ASC LIMIT 1");

    if ($checkRes->num_rows > 0) {
        $resData = $checkRes->fetch_assoc();
        $resID = $resData['ReservationID'];
        $resUserID = $resData['UserID'];
        $bookTitle = $resData['BookTitle'];

        // 1. Mag-send ng notification sa user na nag-reserve
        $notifMsg = "Good news! The book '" . mysqli_real_escape_string($conn, $bookTitle) . "' you reserved is now available. Please visit the library to borrow it.";
        $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$resUserID', '$notifMsg', 'Unread')");

        // 2. I-update ang reservation status para hindi na siya ma-notify ulit
        $conn->query("UPDATE reservations SET Status = 'Notified' WHERE ReservationID = '$resID'");
    }
    // ==========================================================

    echo "<script>alert('Book marked as Returned! Notification sent to next in queue (if any).'); window.location='borrow_records.php';</script>";
    exit();
}

// 3. Handle Approve / Reject Actions
if (isset($_GET['approve_id'])) {
    $borrowID = intval($_GET['approve_id']);
    $conn->query("UPDATE borrow SET Status='Approved' WHERE BorrowID='$borrowID'");
    $userID = $conn->query("SELECT UserID FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc()['UserID'];
    $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', 'Your borrow request has been approved!', 'Unread')");
    echo "<script>alert('Request Approved!'); window.location='borrow_records.php';</script>";
    exit();
}

if (isset($_GET['reject_id'])) {
    $borrowID = intval($_GET['reject_id']);
    $conn->query("UPDATE borrow SET Status='Rejected' WHERE BorrowID='$borrowID'");
    $userID = $conn->query("SELECT UserID FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc()['UserID'];
    $conn->query("INSERT INTO notifications (UserID, Message, Status) VALUES ('$userID', 'Your borrow request has been rejected.', 'Unread')");
    echo "<script>alert('Request Rejected!'); window.location='borrow_records.php';</script>";
    exit();
}

// 4. Fetch all borrow records
$sql = "SELECT b.BorrowID, CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS UserName, bk.BookTitle, b.BorrowDate, b.DueDate, b.Status
        FROM borrow b
        JOIN user u ON b.UserID = u.RoleID
        JOIN books bk ON b.BookID = bk.BookID
        ORDER BY b.BorrowDate DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Borrowing Records - Admin</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
.container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
th { background: #007bff; color: white; }
.status-pending { color: #856404; font-weight: bold; }
.status-approved { color: #155724; font-weight: bold; }
.status-borrowed { color: #004085; font-weight: bold; }
.status-rejected { color: #721c24; font-weight: bold; }
.status-returned { color: #5cb85c; font-weight: bold; }
.btn-action { background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.9em; }
.btn-reject { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.9em; }
</style>
</head>
<body>

<div class="container">
<h2>📝 Borrowing Records Management</h2>
<a href="admindashboard.php">← Back to Dashboard</a>

<table>
    <thead>
        <tr>
            <th>Borrow ID</th>
            <th>Name</th>
            <th>Book Title</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['BorrowID'] ?></td>
                <td><?= htmlspecialchars($row['UserName']) ?></td>
                <td><?= htmlspecialchars($row['BookTitle']) ?></td>
                <td><?= $row['BorrowDate'] ? date('M d, Y', strtotime($row['BorrowDate'])) : '-' ?></td>
                <td><?= $row['DueDate'] ? date('M d, Y', strtotime($row['DueDate'])) : '-' ?></td>
                <td>
                    <?php
                        $status = $row['Status'];
                        $badgeClass = 'status-pending';
                        if ($status == 'Approved') $badgeClass = 'status-approved';
                        elseif ($status == 'Borrowed') $badgeClass = 'status-borrowed';
                        elseif ($status == 'Rejected') $badgeClass = 'status-rejected';
                        elseif ($status == 'Returned') $badgeClass = 'status-returned';
                    ?>
                    <span class="<?= $badgeClass ?>"><?= $status ?></span>
                </td>
                <td>
                    <?php if($status == 'Pending'): ?>
                        <a href="borrow_records.php?approve_id=<?= $row['BorrowID'] ?>" class="btn-action" 
                           onclick="return confirm('Approve this request?')">Approve</a>
                        <a href="borrow_records.php?reject_id=<?= $row['BorrowID'] ?>" class="btn-reject"
                           onclick="return confirm('Reject this request?')">Reject</a>
                    <?php elseif($status == 'Borrowed'): ?>
                        <a href="borrow_records.php?return_id=<?= $row['BorrowID'] ?>" class="btn-action"
                           onclick="return confirm('Mark this book as returned?')">Return Book</a>
                    <?php else: ?>
                        <small>-</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No borrowing records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

</body>
</html>
