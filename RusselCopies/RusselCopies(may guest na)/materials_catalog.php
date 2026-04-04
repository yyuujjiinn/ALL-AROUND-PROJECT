<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// --- LOGIC PARA SA DELETE ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM materials WHERE MaterialID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: materials.php?msg=deleted");
        exit();
    }
}

// --- LOGIC PARA SA UPDATE (EDIT) ---
if (isset($_POST['update_material'])) {
    $id = $_POST['mid'];
    $name = $_POST['mname'];
    $qty = $_POST['mqty'];
    $cat = $_POST['mcat'];

    $stmt = $conn->prepare("UPDATE materials SET MaterialName=?, Quantity=?, CategoryName=? WHERE MaterialID=?");
    $stmt->bind_param("sisi", $name, $qty, $cat, $id);
    if ($stmt->execute()) {
        header("Location: materials.php?msg=updated");
        exit();
    }
}

// --- FETCH DATA ---
$sql = "SELECT *, IFNULL(CategoryName, 'General') as Cat FROM materials ORDER BY Cat ASC, MaterialName ASC";
$result = $conn->query($sql);
$categories = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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
        .container { max-width: 1000px; margin: auto; }
        .category-section { background: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .category-header { border-left: 5px solid #007bff; padding-left: 15px; margin-bottom: 15px; color: #007bff; text-transform: uppercase; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .badge-in { color: #28a745; background: #e6ffed; padding: 4px 8px; border-radius: 5px; font-weight: bold; font-size: 0.85em; }
        .badge-out { color: #dc3545; background: #ffeef0; padding: 4px 8px; border-radius: 5px; font-weight: bold; font-size: 0.85em; }
        .btn-edit { color: #007bff; cursor: pointer; text-decoration: underline; background: none; border: none; font-size: 1em; }
        .btn-delete { color: #dc3545; text-decoration: none; margin-left: 10px; }
        .back-btn { text-decoration: none; color: #666; font-weight: bold; margin-bottom: 20px; display: inline-block; }
        
        /* Modal Style */
        #editModal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 20px; border-radius: 8px; width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        .btn-save { background: #007bff; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <a href="userdashboard.php" class="back-btn">← Back to Dashboard</a>
    <h1>📚 Library Resources</h1>

    <?php if (empty($categories)): ?>
        <div class="category-section" style="text-align: center; padding: 50px;">
            <h2 style="color: #888;">No Materials Found</h2>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['MaterialName']) ?></strong></td>
                            <td>
                                <?= ($item['Quantity'] > 0) ? '<span class="badge-in">Available</span>' : '<span class="badge-out">Out of Stock</span>' ?>
                            </td>
                            <td><?= $item['Quantity'] ?> copies</td>
                            <td>
                                <button class="btn-edit" onclick='openEditModal(<?= json_encode($item) ?>)'>Edit</button>
                                <a href="materials.php?delete_id=<?= $item['MaterialID'] ?>" class="btn-delete" onclick="return confirm('Delete this item?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="editModal">
    <div class="modal-content">
        <h3>Edit Material</h3>
        <form method="POST">
            <input type="hidden" name="mid" id="mid">
            <label>Name:</label>
            <input type="text" name="mname" id="mname" required>
            <label>Quantity:</label>
            <input type="number" name="mqty" id="mqty" required>
            <label>Category:</label>
            <input type="text" name="mcat" id="mcat">
            <button type="submit" name="update_material" class="btn-save">Save Changes</button>
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:gray; width:100%; margin-top:10px; cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('mid').value = data.MaterialID;
    document.getElementById('mname').value = data.MaterialName;
    document.getElementById('mqty').value = data.Quantity;
    document.getElementById('mcat').value = data.CategoryName || data.Category;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Isara ang modal pag clinick sa labas
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) closeModal();
}
</script>

</body>
</html>
