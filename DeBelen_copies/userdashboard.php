<?php
session_start();
include 'connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];
$uName = $_SESSION['user_name'] ?? '';

// ✅ HANDLE PROFILE UPDATE
if (isset($_POST['update_profile'])) {
    $newFirst = $_POST['firstName'];
    $newMiddle = $_POST['middleName'];
    $newLast = $_POST['lastName'];

    // Update query (Siguraduhin na 'RoleID' ang tamang unique identifier sa table mo)
    $stmt = $conn->prepare("UPDATE user SET FirstName=?, MiddleName=?, LastName=? WHERE RoleID=?");
    $stmt->bind_param("ssss", $newFirst, $newMiddle, $newLast, $uID);
    
    if ($stmt->execute()) {
        $uName = trim($newFirst . ' ' . (!empty($newMiddle) ? $newMiddle . ' ' : '') . $newLast);
        $_SESSION['user_name'] = $uName;
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=profile_updated");
        exit();
    }
}

// Re-fetch current name if session is empty
if (empty($uName)) {
    $nameQuery = $conn->query("SELECT FirstName, MiddleName, LastName FROM user WHERE RoleID = '$uID'");
    if ($nameQuery && $nameQuery->num_rows > 0) {
        $nameRow = $nameQuery->fetch_assoc();
        $uName = trim($nameRow['FirstName'] . ' ' . (!empty($nameRow['MiddleName']) ? $nameRow['MiddleName'] . ' ' : '') . $nameRow['LastName']);
        $_SESSION['user_name'] = $uName;
    }
}

// Get raw data for placeholders
$rawNameQuery = $conn->query("SELECT FirstName, MiddleName, LastName FROM user WHERE RoleID = '$uID'");
$userData = $rawNameQuery->fetch_assoc();

// Query fines
$fineQuery = $conn->query("SELECT f.FineID, f.Type, f.Amount, f.Status
                           FROM fines f
                           JOIN borrow b ON f.BorrowID = b.BorrowID
                           WHERE b.UserID = '$uID'");

// ✅ HANDLE AJAX NOTIFICATIONS
if (isset($_POST['mark_read_id'])) {
    $id = intval($_POST['mark_read_id']);
    $conn->query("UPDATE notifications SET Status='Read' WHERE ID='$id' AND UserID='$uID'");
    echo "success"; exit();
}

if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM notifications WHERE ID='$id' AND UserID='$uID'");
    echo "deleted"; exit();
}

if (isset($_POST['delete_all'])) {
    $conn->query("DELETE FROM notifications WHERE UserID='$uID'");
    echo "deleted_all"; exit();
}

// 2. FETCH Notifications
$notifQuery = $conn->query("SELECT * FROM notifications WHERE UserID = '$uID' ORDER BY CreatedAt DESC LIMIT 5");

// 3. FETCH Borrowed Books
$borrowQuery = $conn->query("SELECT b.BorrowID, bk.BookTitle, b.BorrowDate, b.DueDate, b.Status 
                             FROM borrow b 
                             JOIN books bk ON b.BookID = bk.BookID 
                             WHERE b.UserID = '$uID' 
                             ORDER BY b.BorrowDate DESC");

// 4. FETCH Total Unpaid Fines
$sumQuery = $conn->query("SELECT SUM(f.Amount) as total 
                          FROM fines f 
                          JOIN borrow b ON f.BorrowID = b.BorrowID 
                          WHERE b.UserID = '$uID' AND f.Status='Unpaid'");
$sumRow = $sumQuery->fetch_assoc();
$outstandingBalance = $sumRow['total'] ?? 0;

// 5. FETCH Role
$roleQuery = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$uID'");
$role = $roleQuery->fetch_assoc();
$displayRole = "Student"; 
if ($role) {
    if ($role['FacultyID'] != 0) $displayRole = "Faculty";
    elseif ($role['StaffID'] != 0) $displayRole = "Library Staff";
    elseif ($role['VisitorID'] != 0) $displayRole = "Visitor";
    elseif ($role['AdminID'] != 0) $displayRole = "Administrator";
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
        
        /* Edit Profile Specific Styles */
        .edit-profile-btn { width: 100%; margin-top: 15px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .edit-profile-btn:hover { background: #e2e6ea; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 25px; border-radius: 10px; width: 350px; }
        .modal-content input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .save-btn { background: #28a745; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; }
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
            <a href="view_books.php" style="background: #007bff; color: white; padding: 12px 35px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;">Open Full Catalog →</a>
        </div>

        <div class="card">
            <h3>My Borrowed Books</h3>
            <table>
                <thead>
                    <tr><th>Book Title</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if ($borrowQuery && $borrowQuery->num_rows > 0): ?>
                        <?php while($b = $borrowQuery->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['BookTitle']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($b['BorrowDate'])) ?></td>
                            <td><?= date('M d, Y', strtotime($b['DueDate'])) ?></td>
                            <td><span class="badge <?= ($b['Status'] == 'Returned') ? 'badge-returned' : 'badge-pending' ?>"><?= $b['Status'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#888;">No books borrowed.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Recent Notifications <span style="float:right; font-size:0.8em; color:red; cursor:pointer;" onclick="deleteAll()">Clear All</span></h3>
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
                                <button class="btn-read" onclick="markAsRead(<?= $n['ID'] ?>)">Mark as Read</button>
                            <?php endif; ?>
                            <span style="color:red; cursor:pointer; font-size:0.8em; margin-left:10px;" onclick="deleteNotif(<?= $n['ID'] ?>)">Delete</span>
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
            
            <button class="edit-profile-btn" onclick="openModal()">Edit Profile Details</button>
        </div>

        <div class="card">
            <h3>My Fines</h3>
            <table>
                <tr><th>ID</th><th>Type</th><th>Amount</th><th>Status</th></tr>
                <?php if ($fineQuery && $fineQuery->num_rows > 0): ?>
                    <?php while($f = $fineQuery->fetch_assoc()): ?>
                        <tr>
                            <td><?= $f['FineID'] ?></td>
                            <td><?= $f['Type'] ?></td>
                            <td>₱<?= number_format($f['Amount'], 2) ?></td>
                            <td style="font-size:0.85em; font-weight:bold; color:<?= $f['Status']=='Paid'?'green':'red'?>"><?= $f['Status'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No fines found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0;">Update Information</h3>
        <form method="POST">
            <label style="font-size:0.85em; color:#555;">First Name</label>
            <input type="text" name="firstName" value="<?= htmlspecialchars($userData['FirstName'] ?? '') ?>" required>
            
            <label style="font-size:0.85em; color:#555;">Middle Name</label>
            <input type="text" name="middleName" value="<?= htmlspecialchars($userData['MiddleName'] ?? '') ?>">
            
            <label style="font-size:0.85em; color:#555;">Last Name</label>
            <input type="text" name="lastName" value="<?= htmlspecialchars($userData['LastName'] ?? '') ?>" required>
            
            <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:gray; cursor:pointer; width:100%; margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<script>
// Modal Controls
function openModal() { document.getElementById('editModal').style.display = 'block'; }
function closeModal() { document.getElementById('editModal').style.display = 'none'; }
window.onclick = function(event) { if (event.target == document.getElementById('editModal')) closeModal(); }

// AJAX NOTIFICATIONS
function markAsRead(id) {
    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "mark_read_id=" + id
    }).then(() => {
        document.getElementById("status-box-" + id).innerHTML = '<span class="status-read-text">✔ Read</span> <span style="color:red; cursor:pointer; font-size:0.8em; margin-left:10px;" onclick="deleteNotif(' + id + ')">Delete</span>';
    });
}

function deleteNotif(id) {
    if (!confirm("Delete this notification?")) return;
    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "delete_id=" + id
    }).then(() => document.getElementById("notif-" + id).remove());
}

function deleteAll() {
    if (!confirm("Delete all notifications?")) return;
    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "delete_all=1"
    }).then(() => location.reload());
}
</script>

</body>
</html>
