<?php
include 'connect.php';

echo '<a href="admindashboard.php">
        <button type="button">Back to Dashboard</button>
      </a>';

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql = "SELECT f.FineID, f.BorrowID, f.Type, f.Amount, f.Status,
               u.Name AS UserName, b.BookID
        FROM fines f
        JOIN borrow b ON f.BorrowID = b.BorrowID
        JOIN user u ON b.UserID = u.RoleID";

if (!empty($search)) {
    $sql .= " WHERE u.Name LIKE '%$search%' OR b.BorrowID LIKE '%$search%'";
}

$sql .= " ORDER BY f.FineID DESC";

$fines = $conn->query($sql);
?>


<form method="get" action="admin_fines.php">
    <input type="text" name="search" placeholder="Search by User or Borrow ID">
    <button type="submit">Search</button>
</form>

<h3>Fine Management</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>Borrow ID</th>
        <th>User</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
if ($fines->num_rows > 0) {
    while($f = $fines->fetch_assoc()) {
        echo "<tr>
                <td>{$f['BorrowID']}</td>
                <td>{$f['UserName']}</td>
                <td>{$f['Type']}</td>
                <td>₱".number_format($f['Amount'],2)."</td>
                <td>{$f['Status']}</td>
                <td>";

        // dito na lang diretsong HTML forms
        ?>
            <!-- Update form -->
            <form method="post" action="update_fine.php" style="display:inline;">
            <input type="hidden" name="fineID" value="<?php echo $f['FineID']; ?>">
            <select name="status">
            <option value="update">-Update-</option>
            <?php
            if ($f['Type'] === "Overdue") {
                echo '<option value="Unpaid">Unpaid</option>';
                echo '<option value="Paid">Paid</option>';
            } elseif ($f['Type'] === "Missing") {
                echo '<option value="Unpaid">Unpaid</option>';
                echo '<option value="Pending Replacement">Pending Replacement</option>';
                echo '<option value="Paid">Paid</option>';
                echo '<option value="Replaced">Replaced</option>';
            }
        ?>
    </select>
    <button type="submit">Update</button>
    </form>



            <!-- Delete form -->
            <form method="post" action="delete_fine.php" style="display:inline; margin-left:5px;">
                <input type="hidden" name="fineID" value="<?php echo $f['FineID']; ?>">
                <button type="submit" onclick="return confirm('Are you sure you want to delete this fine?')">Delete</button>
            </form>
        <?php

        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center;'>No fines found.</td></tr>";
}
?>

</table>
