<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// 1. ADD MATERIAL
if (isset($_POST['add_material'])) {
    $name = mysqli_real_escape_string($conn, $_POST['m_name']);
    $qty = (int)$_POST['m_qty'];
    $cat = mysqli_real_escape_string($conn, $_POST['m_cat']);

    $conn->query("INSERT INTO materials (MaterialName, Quantity, Category) VALUES ('$name', '$qty', '$cat')");
    header("Location: admin_materials.php?msg=added");
}

// 2. DELETE MATERIAL
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM materials WHERE MaterialID = '$id'");
    header("Location: admin_materials.php?msg=deleted");
}

$all_materials = $conn->query("SELECT * FROM materials ORDER BY Category ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Materials - Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 30px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #007bff; color: white; }
        input, select { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; }
        .btn-del { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.8em; }
    </style>
</head>
<body>

<div class="container">
    <a href="admindashboard.php" style="text-decoration: none; color: #007bff;">← Back to Dashboard</a>
    <h2>Manage Library Materials</h2>

    <div class="form-section">
        <h4>Add New Resource</h4>
        <form method="POST">
            <input type="text" name="m_name" placeholder="Item Name (e.g. AI Thesis 2024)" required style="width: 40%;">
            <input type="number" name="m_qty" placeholder="Qty" required style="width: 15%;">
            <select name="m_cat" required>
                <option value="">-- Select Category --</option>
                <option value="Research">Research</option>
                <option value="Magazine">Magazine</option>
                <option value="Journal">Journal</option>
                <option value="Newspaper">Newspaper</option>
            </select>
            <button type="submit" name="add_material" class="btn-save">Add Item</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $all_materials->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['MaterialName']) ?></strong></td>
                <td><span style="background: #e7f3ff; color: #007bff; padding: 2px 8px; border-radius: 10px; font-size: 0.85em;"><?= $row['Category'] ?></span></td>
                <td><?= $row['Quantity'] ?></td>
                <td>
                    <a href="admin_materials.php?delete=<?= $row['MaterialID'] ?>" class="btn-del" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>