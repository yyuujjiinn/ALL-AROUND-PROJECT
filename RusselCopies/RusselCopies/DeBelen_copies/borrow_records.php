<?php
session_start();
include 'connect.php';

// 1. Admin Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// ✅ APPROVE
if (isset($_GET['approve_id'])) {
    $id = $_GET['approve_id'];

    // Get BookID + UserID
    $info = $conn->query("SELECT BookID, UserID FROM borrow WHERE BorrowID='$id'")->fetch_assoc();
    $bookID = $info['BookID'];
    $userID = $info['UserID'];

    // Get Book Title (for better message)
    $book = $conn->query("SELECT BookTitle FROM books WHERE BookID='$bookID'")->fetch_assoc();
    $title = $book['BookTitle'];

    // Update status
    $conn->query("UPDATE borrow SET Status='Borrowed' WHERE BorrowID='$id'");

    // Deduct quantity
    $conn->query("UPDATE books SET Quantity = Quantity - 1 WHERE BookID='$bookID'");

    // ✅ Notify USER
    $msg = "Your request for \"$title\" has been APPROVED.";
    $conn->query("INSERT INTO notifications (UserID, Message, Status) 
                  VALUES ('$userID', '$msg', 'Unread')");

    echo "<script>alert('Request Approved!'); window.location='borrow_records.php';</script>";
}

// ❌ REJECT
if (isset($_GET['reject_id'])) {
    $id = $_GET['reject_id'];

    // Get UserID + BookID
    $info = $conn->query("SELECT BookID, UserID FROM borrow WHERE BorrowID='$id'")->fetch_assoc();
    $bookID = $info['BookID'];
    $userID = $info['UserID'];

    // Get Book Title
    $book = $conn->query("SELECT BookTitle FROM books WHERE BookID='$bookID'")->fetch_assoc();
    $title = $book['BookTitle'];

    // Update status
    $conn->query("UPDATE borrow SET Status='Rejected' WHERE BorrowID='$id'");

    // ✅ Notify USER
    $msg = "Your request for \"$title\" has been REJECTED.";
    $conn->query("INSERT INTO notifications (UserID, Message, Status) 
                  VALUES ('$userID', '$msg', 'Unread')");

    echo "<script>alert('Request Rejected!'); window.location='borrow_records.php';</script>";
}

// 2. Handle Return Action (Kapag isinauli na ang libro)
if (isset($_GET['return_id'])) {
    $borrowID = $_GET['return_id'];

    $info = $conn->query("SELECT BookID, DueDate FROM borrow WHERE BorrowID = '$borrowID'")->fetch_assoc();
    $bookID = $info['BookID'];
    $dueDate = $info['DueDate'];

    // ✅ Set ReturnDate properly
    $conn->query("UPDATE borrow 
                  SET Status='Returned', ReturnDate=CURDATE() 
                  WHERE BorrowID='$borrowID'");

    // ✅ Restore quantity
    $conn->query("UPDATE books SET Quantity = Quantity + 1 WHERE BookID='$bookID'");

    // ✅ Fine calculation (restore your original logic)
    $dates = $conn->query("SELECT DueDate, ReturnDate FROM borrow WHERE BorrowID='$borrowID'")->fetch_assoc();
    $returnDate = $dates['ReturnDate'];

    $daysLate = (strtotime($returnDate) - strtotime($dueDate)) / (60*60*24);

    if ($daysLate > 0) {
        $amount = $daysLate * 20;

        $conn->query("INSERT INTO fines (BorrowID, Amount, Status, Type) 
                      VALUES ('$borrowID', '$amount', 'Unpaid', 'Late')");
    }

    echo "<script>alert('Book marked as Returned!'); window.location='borrow_records.php';</script>";
}

// 3. Fetch All Borrowing Records
$sql = "SELECT b.BorrowID, u.Name as UserName, bk.BookTitle, b.BorrowDate, b.DueDate, b.Status 
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
        .status-borrowed { color: #d9534f; font-weight: bold; }
        .status-returned { color: #5cb85c; font-weight: bold; }
        .btn-return { background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.9em; }
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
                <th>Borrower Name</th>
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
                    <td><strong><?= htmlspecialchars($row['UserName']) ?></strong></td>
                    <td><?= htmlspecialchars($row['BookTitle']) ?></td>
                    <td><?= $row['BorrowDate'] ?></td>
                    <td><?= $row['DueDate'] ?></td>
                    <td>
                        <span class="<?= ($row['Status'] == 'Borrowed') ? 'status-borrowed' : 'status-returned' ?>">
                            <?= $row['Status'] ?>
                        </span>
                    </td>
                    <td>
    <?php if($row['Status'] == 'Pending'): ?>
        <a href="borrow_records.php?approve_id=<?= $row['BorrowID'] ?>" 
           class="btn-return" 
           onclick="return confirm('Approve this request?')">
           Approve
        </a>

        <a href="borrow_records.php?reject_id=<?= $row['BorrowID'] ?>" 
           class="btn-return" 
           style="background:#dc3545;"
           onclick="return confirm('Reject this request?')">
           Reject
        </a>

    <?php elseif($row['Status'] == 'Borrowed'): ?>
        <a href="borrow_records.php?return_id=<?= $row['BorrowID'] ?>" 
           class="btn-return" 
           onclick="return confirm('Mark this book as returned?')">
           Return Book
        </a>

    <?php else: ?>
        <small>Completed</small>
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
