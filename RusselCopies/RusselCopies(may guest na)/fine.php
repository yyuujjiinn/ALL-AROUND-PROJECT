<?php
session_start();
include 'connect.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];

// Fetch fines for this user
$fineQuery = $conn->query("
    SELECT f.FineID, f.Amount, f.PaidStatus, f.FineType, b.DueDate, b.ReturnDate, bk.BookTitle
    FROM fine f
    JOIN borrow b ON f.BorrowID = b.BorrowID
    JOIN books bk ON b.BookID = bk.BookID
    WHERE b.UserID = '$uID'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Fines</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .unpaid { color: red; font-weight: bold; }
        .paid { color: green; font-weight: bold; }
        .pending { color: orange; font-weight: bold; }
        a.back { text-decoration: none; color: #007bff; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="userdashboard.php" class="back">← Back to Dashboard</a>
    <h1>📑 My Fine Records</h1>
    <table>
        <tr>
            <th>Book</th>
            <th>Due Date</th>
            <th>Returned</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>
        <?php if ($fineQuery && $fineQuery->num_rows > 0): ?>
            <?php while($row = $fineQuery->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['BookTitle']) ?></td>
                <td><?= $row['DueDate'] ?></td>
                <td><?= $row['ReturnDate'] ?></td>
                <td>
                  <?php if($row['FineType'] == 'Lost-Replace'): ?>
                      <span class="pending">Pending Replacement</span>
                  <?php else: ?>
                      ₱<?= number_format($row['Amount'], 2) ?>
                  <?php endif; ?>
                </td>
                <td class="<?= strtolower($row['PaidStatus']) ?>">
                    <?= $row['PaidStatus'] ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; color:#888;">No fines found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
