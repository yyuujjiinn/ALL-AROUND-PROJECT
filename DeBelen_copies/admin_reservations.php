<?php
session_start();
include 'connect.php';

// Security Check para sa Admin
$uID = $_SESSION['user_id'];
$adminCheck = $conn->query("SELECT AdminID FROM user_roles WHERE RoleID = '$uID'");
$isAdmin = $adminCheck->fetch_assoc();
if (!$isAdmin || $isAdmin['AdminID'] == 0) { header("Location: userdashboard.php"); exit(); }

$sql = "SELECT r.ReservationID, r.ReservationDate, r.Status, b.BookTitle, u.FirstName, u.LastName 
        FROM reservations r 
        JOIN books b ON r.BookID = b.BookID 
        JOIN user u ON r.UserID = u.RoleID 
        ORDER BY r.ReservationDate DESC";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reservations</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-pending { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Book Reservations List</h1>
    <a href="admindashboard.php">Back to Dashboard</a>
    <table>
        <tr>
            <th>Date</th>
            <th>User Name</th>
            <th>Book Title</th>
            <th>Status</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['ReservationDate'] ?></td>
            <td><?= $row['FirstName'] . " " . $row['LastName'] ?></td>
            <td><?= $row['BookTitle'] ?></td>
            <td class="status-<?= strtolower($row['Status']) ?>"><?= $row['Status'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>