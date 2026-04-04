<?php
session_start();
include 'connect.php';

// 1. Admin Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$uID = $_SESSION['user_id'];
$checkAdmin = $conn->query("SELECT AdminID FROM user_roles WHERE RoleID = '$uID'");
$roleData = $checkAdmin->fetch_assoc();

if (!$roleData || $roleData['AdminID'] == 0) {
    header("Location: userdashboard.php");
    exit();
}

// 2. HANDLE SENDING
if (isset($_POST['send_notif'])) {
    $targetUser = $_POST['user_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if (!empty($targetUser) && !empty($message)) {
        // Status starts as 'Unread'
        $sql = "INSERT INTO notifications (UserID, Message, Status) VALUES ('$targetUser', '$message', 'Unread')";
        if ($conn->query($sql)) {
            echo "<script>alert('Message Sent!'); window.location='admin_notifications.php';</script>";
        }
    }
}

// 3. FETCH DATA
$users = $conn->query("SELECT RoleID, CONCAT(firstname, ' ', middlename, ' ', lastname) AS Name FROM user WHERE RoleID != '$uID'");
$history = $conn->query("SELECT n.*, CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS Name FROM notifications n JOIN user u ON n.UserID = u.RoleID ORDER BY n.CreatedAt DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Notifications</title>
    <style>
        body { font-family: 'Segoe UI', Arial; background: #f4f7f6; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-send { background: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #343a40; color: white; }
        .badge-unread { background: #ffc107; color: black; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-read { background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <a href="admindashboard.php" style="text-decoration:none; color:#666;">← Back to Dashboard</a>
    <h2>📣 Send Library Notification</h2>

    <div class="form-section">
        <form method="POST">
            <label>Select Recipient:</label>
            <select name="user_id" required>
                <option value="">-- Choose a Student/User --</option>
                <?php while($u = $users->fetch_assoc()): ?>
                    <option value="<?= $u['RoleID'] ?>"><?= htmlspecialchars($u['Name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label>Message:</label>
            <textarea name="message" rows="4" placeholder="Enter your message (e.g. Overdue book reminder...)" required></textarea>
            
            <button type="submit" name="send_notif" class="btn-send">Send Notice</button>
        </form>
    </div>

    <h3>Sent History & Read Receipts</h3>
    <table>
        <thead>
            <tr>
                <th>Recipient</th>
                <th>Message</th>
                <th>Sent At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if($history && $history->num_rows > 0): ?>
                <?php while($h = $history->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($h['Name']) ?></strong></td>
                    <td><?= htmlspecialchars($h['Message']) ?></td>
                    <td><small><?= date('M d, g:i A', strtotime($h['CreatedAt'])) ?></small></td>
                    <td>
                        <?php if($h['Status'] == 'Read'): ?>
                            <span class="badge-read">✔ Read</span>
                        <?php else: ?>
                            <span class="badge-unread">● Unread</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No history found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
