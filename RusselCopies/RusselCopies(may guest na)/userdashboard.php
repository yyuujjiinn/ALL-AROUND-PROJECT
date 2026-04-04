<?php
session_start();
include 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];
$uName = $_SESSION['user_name'];

// Query fines ng current student (listahan ng fines)
$fineQuery = $conn->query("SELECT f.FineID, f.Type, f.Amount, f.Status
                           FROM fines f
                           JOIN borrow b ON f.BorrowID = b.BorrowID
                           WHERE b.UserID = '$uID'");

// ✅ HANDLE AJAX MARK AS READ
if (isset($_POST['mark_read_id'])) {
    $id = intval($_POST['mark_read_id']);
    $conn->query("UPDATE notifications SET Status='Read' WHERE ID='$id' AND UserID='$uID'");
    echo "success";
    exit();
}

// ✅ DELETE SINGLE
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM notifications WHERE ID='$id' AND UserID='$uID'");
    echo "deleted";
    exit();
}

// ✅ DELETE ALL
if (isset($_POST['delete_all'])) {
    $conn->query("DELETE FROM notifications WHERE UserID='$uID'");
    echo "deleted_all";
    exit();
}

// 2. FETCH Notifications
$notifQuery = $conn->query("SELECT * FROM notifications WHERE UserID = '$uID' ORDER BY CreatedAt DESC LIMIT 5");

// 3. FETCH Borrowed Books (Active & History)
$borrowQuery = $conn->query("SELECT b.BorrowID, bk.BookTitle, b.BorrowDate, b.DueDate, b.Status, b.ReturnDate
                             FROM borrow b 
                             JOIN books bk ON b.BookID = bk.BookID 
                             WHERE b.UserID = '$uID' 
                             ORDER BY b.BorrowDate DESC");

// 4. FETCH Total Unpaid Fines (Outstanding Balance)
$sumQuery = $conn->query("SELECT SUM(f.Amount) as total 
                          FROM fines f 
                          JOIN borrow b ON f.BorrowID = b.BorrowID 
                          WHERE b.UserID = '$uID' AND f.Status='Unpaid'");
$sumRow = $sumQuery->fetch_assoc();
$outstandingBalance = $sumRow['total'] ?? 0;

// 5. FETCH Dynamic Role for Sidebar
$roleQuery = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$uID'");
$role = $roleQuery->fetch_assoc();

$displayRole = "Student"; 
if ($role) {
    if ($role['FacultyID'] != 0) $displayRole = "Faculty";
    elseif ($role['StaffID'] != 0) $displayRole = "Library Staff";
    elseif ($role['VisitorID'] != 0) $displayRole = "Visitor";
    elseif ($role['AdminID'] != 0) $displayRole = "Administrator";
}

// Payment function
function payFine($conn, $fineID, $amountPaid) {
    $fineQuery = $conn->query("SELECT Amount, Status FROM fines WHERE FineID='$fineID'");
    $fine = $fineQuery->fetch_assoc();

    if ($fine && $fine['Status'] == 'Unpaid') {
        if ($amountPaid >= $fine['Amount']) {
            // Full payment
            $conn->query("UPDATE fines SET Amount=0, Status='Paid' WHERE FineID='$fineID'");
        } else {
            // Partial payment
            $newAmount = $fine['Amount'] - $amountPaid;
            $conn->query("UPDATE fines SET Amount='$newAmount' WHERE FineID='$fineID'");
        }
        return true;
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - BulSU Library</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .header { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .notif-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .status-read-text { color: #007bff; font-weight: bold; font-size: 0.9em; }
        .role-badge { background: #e7f3ff; color: #007bff; padding: 3px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .badge { padding: 4px 8px; border-radius: 5px; font-size: 0.8em; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-returned { background: #d4edda; color: #155724; }
        .logout-btn { color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 8px 15px; border-radius: 5px; }
        .btn-read { background:#007bff; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size: 0.85em; }
        .btn-read:hover { background:#0056b3; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1 style="margin:0; color: #007bff;">BulSU Library System</h1>
        <p style="margin:5px 0 0;">Welcome, <strong><?= htmlspecialchars($uName) ?></strong></p>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="dashboard-grid">
    <div class="main-content">
        <div class="card" style="text-align: center; padding: 30px 20px; border-bottom: 5px solid #007bff;">
            <h2 style="margin-top: 0; color: #333;">Library Resources Catalog</h2>
            <p style="color: #666;">Access Research Papers, Magazines, and Journals.</p>
            <a href="view_books.php" class="catalog-link" style="background: #007bff; color: white; padding: 12px 35px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 10px;">
                Open Full Catalog →
            </a>
        </div>

        <div class="card">
            <h3>My Borrowed Books</h3>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($borrowQuery && $borrowQuery->num_rows > 0): ?>
                        <?php while($b = $borrowQuery->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['BookTitle']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($b['BorrowDate'])) ?></td>
                            <td><?= date('M d, Y', strtotime($b['DueDate'])) ?></td>
                            <td> <?= (!empty($b['ReturnDate']) && $b['ReturnDate'] != '0000-00-00') ? date('M d, Y', strtotime($b['ReturnDate'])) : '-' ?></td>
                            <td>
                                <span class="badge <?= ($b['Status'] == 'Returned') ? 'badge-returned' : 'badge-pending' ?>">
                                    <?= $b['Status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#888;">You haven't borrowed any books yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

                <div class="card">
            <h3>Recent Notifications 
                <span style="float:right; font-size:0.8em; color:red; cursor:pointer;" onclick="deleteAll()">Clear All</span>
            </h3>

            <?php if ($notifQuery && $notifQuery->num_rows > 0): ?>
                <?php while($n = $notifQuery->fetch_assoc()): ?>
                    <div class="notif-item" id="notif-<?= $n['ID'] ?>">
                        <div>
                            <p style="margin: 0; font-weight: 500;"><?= htmlspecialchars($n['Message']) ?></p>
                            <small style="color: gray;"><?= date('M d, Y', strtotime($n['CreatedAt'])) ?></small>
                        </div>
                        <div id="status-box-<?= $n['ID'] ?>">
                            <?php if ($n['Status'] == 'Read'): ?>
                                <span class="status-read-text">✔ Read</span>
                            <?php else: ?>
                                <button class="btn-read" onclick="markAsRead(<?= $n['ID'] ?>)">
                                    Mark as Read
                                </button>
                            <?php endif; ?>
                            <span style="color:red; cursor:pointer; font-size:0.8em;" onclick="deleteNotif(<?= $n['ID'] ?>)">Delete</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#888;">No notifications.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar">
        <div class="card" style="text-align: center; border-top: 5px solid #007bff;">
            <p style="color: #666; margin-bottom: 5px;">Outstanding Balance</p>
            <h1 style="margin: 0; font-size: 2.5em;">₱<?= number_format($outstandingBalance, 2) ?></h1>
            <small style="color: <?= ($outstandingBalance > 0) ? '#dc3545' : '#28a745' ?>; font-weight: bold;">
                <?= ($outstandingBalance > 0) ? 'Unpaid Fines Found' : 'Account is Clear' ?>
            </small>
        </div>

        <div class="card">
            <h3>Profile Summary</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($uName) ?></p>
            <p><strong>Role:</strong> <span class="role-badge"><?= $displayRole ?></span></p>
        </div>

        <h3>My Fines</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Fine ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
            <?php
            if ($fineQuery && $fineQuery->num_rows > 0) {
                while($f = $fineQuery->fetch_assoc()) {
                    echo "<tr>
                            <td>{$f['FineID']}</td>
                            <td>{$f['Type']}</td>
                            <td>₱".number_format($f['Amount'],2)."</td>
                            <td>{$f['Status']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No fines found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<script>
// MARK AS READ
function markAsRead(id) {
    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "mark_read_id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data === "success") {
            document.getElementById("status-box-" + id).innerHTML =
                '<span class="status-read-text">✔ Read</span> ' +
                '<span style="color:red; cursor:pointer; font-size:0.8em;" onclick="deleteNotif(' + id + ')">Delete</span>';
        }
    });
}

// DELETE ONE
function deleteNotif(id) {
    if (!confirm("Delete this notification?")) return;

    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "delete_id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data === "deleted") {
            document.getElementById("notif-" + id).remove();
        }
    });
}

// DELETE ALL
function deleteAll() {
    if (!confirm("Delete all notifications?")) return;

    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "delete_all=1"
    })
    .then(res => res.text())
    .then(data => {
        if (data === "deleted_all") {
            location.reload();
        }
    });
}
</script>

</body>
</html>

