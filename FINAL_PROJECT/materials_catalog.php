<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Gamitan natin ng COALESCE sa SQL para safe kahit anong column name
$sql = "SELECT *, IFNULL(CategoryName, 'General') as Cat FROM materials ORDER BY Cat ASC, MaterialName ASC";
$result = $conn->query($sql);

$categories = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Kunin natin yung category, kung empty, 'General' ang default
        $catName = !empty($row['Cat']) ? $row['Cat'] : 'General';
        $categories[$catName][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Materials Catalog</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 30px; }
        .container { max-width: 900px; margin: auto; }
        .category-section { background: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .category-header { border-left: 5px solid #007bff; padding-left: 15px; margin-bottom: 15px; color: #007bff; text-transform: uppercase; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .badge-in { color: #28a745; background: #e6ffed; padding: 4px 8px; border-radius: 5px; font-weight: bold; font-size: 0.85em; }
        .badge-out { color: #dc3545; background: #ffeef0; padding: 4px 8px; border-radius: 5px; font-weight: bold; font-size: 0.85em; }
        .back-btn { text-decoration: none; color: #666; font-weight: bold; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <a href="userdashboard.php" class="back-btn">← Back to Dashboard</a>
    <h1>📚 Library Resources</h1>

    <?php if (empty($categories)): ?>
        <div class="category-section" style="text-align: center; padding: 50px;">
            <h2 style="color: #888;">No Materials Found</h2>
            <p>Make sure your <strong>materials</strong> table has rows and a <strong>CategoryName</strong> column.</p>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $catName => $items): ?>
            <div class="category-section">
                <h2 class="category-header"><?= htmlspecialchars($catName) ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Status</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['MaterialName']) ?></strong></td>
                            <td>
                                <?php if($item['Quantity'] > 0): ?>
                                    <span class="badge-in">Available</span>
                                <?php else: ?>
                                    <span class="badge-out">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $item['Quantity'] ?> copies</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>