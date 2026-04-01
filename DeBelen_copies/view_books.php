<?php
session_start();
include 'connect.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];

// Get User Role to determine if they can see the "Borrow" button
$roleQuery = "SELECT * FROM user_roles WHERE RoleID = '$uID'";
$role = $conn->query($roleQuery)->fetch_assoc();

// Fetch books with Category and Publisher
$sql = "SELECT b.BookID, b.BookTitle, c.CategoryName, p.PublisherName, b.Quantity 
        FROM books b
        JOIN categories c ON b.CategoryID = c.CategoryID
        JOIN publisher p ON b.PublisherId = p.PublisherID";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Catalog</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .status-available { color: green; font-weight: bold; }
        .status-out { color: red; font-weight: bold; }
        .btn-borrow { background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.8em; }
    </style>
</head>
<body>

<div class="container">
    <a href="userdashboard.php">← Back to Dashboard</a>
    <h1>Library Catalog</h1>
    <p>Browse available books and materials.</p>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author(s)</th>
                <th>Category</th>
                <th>Publisher</th>
                <th>Availability</th>
                <th>Action</th>
                <th>QR Code</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['BookTitle']) ?></strong></td>
                <td>
                    <?php
                    $bid = $row['BookID'];
                    $authList = $conn->query("SELECT a.AuthorName FROM authors a JOIN book_authors ba ON a.AuthorID = ba.AuthorID WHERE ba.BookID = '$bid'");
                    $names = [];
                    while($aname = $authList->fetch_assoc()) $names[] = $aname['AuthorName'];
                    echo htmlspecialchars(implode(", ", $names));
                    ?>
                </td>
                <td><?= htmlspecialchars($row['CategoryName']) ?></td>
                <td><?= htmlspecialchars($row['PublisherName']) ?></td>
                <td>
                    <?php if($row['Quantity'] > 0): ?>
                        <span class="status-available">Available (<?= $row['Quantity'] ?>)</span>
                    <?php else: ?>
                        <span class="status-out">Out of Stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($role['VisitorID'] != 0): ?>
                        <small>View Only</small>
                    <?php elseif($row['Quantity'] > 0): ?>
                        <a href="borrow_process.php?id=<?= $row['BookID'] ?>" class="btn-borrow">Request Borrow</a>
                    <?php else: ?>
                        <button disabled>Unavailable</button>
                    <?php endif; ?>
                </td>
                <td>
                    <img src="generate_qr.php?id=<?= $row['BookID'] ?>" width="80" height="80" alt="QR Code">
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
