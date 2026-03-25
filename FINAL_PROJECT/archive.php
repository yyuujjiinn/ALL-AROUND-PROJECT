<?php
session_start();
include 'connect.php';

// 1. Restore Logic
if (isset($_GET['restore_id'])) {
    $archID = $_GET['restore_id'];
    $data = $conn->query("SELECT * FROM book_archive WHERE ArchiveID = '$archID'")->fetch_assoc();
    
    if ($data) {
        $bID = $data['BookID'];
        $title = mysqli_real_escape_string($conn, $data['BookTitle']);
        $cat = $data['CategoryID'];
        $pub = $data['PublisherId'];
        $qty = $data['Quantity'];

        // Move back to main books table
        $restore = "INSERT INTO books (BookID, BookTitle, CategoryID, PublisherId, Quantity) 
                    VALUES ('$bID', '$title', '$cat', '$pub', '$qty')";
        
        if ($conn->query($restore)) {
            $conn->query("DELETE FROM book_archive WHERE ArchiveID = '$archID'");
            echo "<script>alert('Book Restored Successfully!'); window.location='archive.php';</script>";
        }
    }
}

$archivedBooks = $conn->query("SELECT * FROM book_archive ORDER BY DeletedAt DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recently Deleted Books</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #fdfdfd; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background: #333; color: white; }
        .btn-restore { background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Recently Deleted (Archive)</h1>
    <a href="book.php">← Back to Manage Books</a>
    <br><br>
    <table>
        <tr>
            <th>Title</th>
            <th>Deleted On</th>
            <th>Action</th>
        </tr>
        <?php while($row = $archivedBooks->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['BookTitle']) ?></td>
            <td><?= $row['DeletedAt'] ?></td>
            <td>
                <a href="archive.php?restore_id=<?= $row['ArchiveID'] ?>" class="btn-restore">Restore</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>