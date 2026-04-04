<?php
session_start();
include 'connect.php';

// 1. Admin Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];
$adminCheck = $conn->query("SELECT AdminID FROM user_roles WHERE RoleID = '$uID'");
$isAdmin = $adminCheck->fetch_assoc();

if (!$isAdmin || $isAdmin['AdminID'] == 0) {
    header("Location: userdashboard.php");
    exit();
}

// 2. HANDLE DELETE LOGIC
if (isset($_GET['delete_id'])) {
    $idToDelete = $_GET['delete_id'];
    
    // Iwasan na mabura ang sariling account habang naka-login
    if ($idToDelete == $uID) {
        echo "<script>alert('You cannot delete your own admin account while logged in!'); window.location='manage_users.php';</script>";
    } else {
        // Burahin muna sa user_roles dahil sa foreign key constraint
        $conn->query("DELETE FROM user_roles WHERE RoleID = '$idToDelete'");
        $conn->query("DELETE FROM user WHERE RoleID = '$idToDelete'");
        echo "<script>alert('User deleted successfully!'); window.location='manage_users.php';</script>";
    }
}

// 3. HANDLE UPDATE LOGIC
if (isset($_POST['update_user'])) {
    $roleID = $_POST['role_id'];
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $updateSql = "UPDATE user SET FirstName='$fname', MiddleName='$mname', LastName='$lname', Email='$email' WHERE RoleID='$roleID'";
    
    if ($conn->query($updateSql)) {
        echo "<script>alert('User details updated!'); window.location='manage_users.php';</script>";
    }
}

// 4. FETCH DATA PARA SA EDIT FORM
$editData = null;
if (isset($_GET['edit_id'])) {
    $idToEdit = $_GET['edit_id'];
    $res = $conn->query("SELECT * FROM user WHERE RoleID = '$idToEdit'");
    $editData = $res->fetch_assoc();
}

// 5. FETCH ALL USERS WITH ROLES (Gamit ang LEFT JOIN)
$query = "SELECT u.*, ur.AdminID, ur.StudentID, ur.FacultyID, ur.StaffID, ur.VisitorID 
          FROM user u
          LEFT JOIN user_roles ur ON u.RoleID = ur.RoleID 
          ORDER BY u.RoleID DESC";
$users = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #0097a7; margin-bottom: 20px; border-bottom: 2px solid #e0f2f1; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #0097a7; color: white; }
        tr:hover { background: #f1f8e9; }

        /* Badge Styles */
        .badge { padding: 4px 10px; border-radius: 15px; font-size: 0.75em; font-weight: bold; color: white; }
        .bg-admin { background: #d32f2f; }
        .bg-student { background: #1976d2; }
        .bg-faculty { background: #388e3c; }
        .bg-staff { background: #f57c00; }
        .bg-visitor { background: #7b1fa2; }

        /* Form Styling */
        .edit-box { background: #e0f7fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #0097a7; }
        .input-group { margin-bottom: 15px; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        
        .btn-save { background: #2e7d32; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-edit { background: #4a148c; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.85em; }
        .btn-delete { background: #c62828; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="container">
    <a href="admindashboard.php" style="text-decoration:none; color:#0097a7; font-weight:bold;">← Back to Dashboard</a>
    <h2>User Management</h2>

    <?php if ($editData): ?>
    <div class="edit-box">
        <h3>Edit User: <?php echo htmlspecialchars($editData['FirstName']); ?></h3>
        <form method="POST" action="manage_users.php">
            <input type="hidden" name="role_id" value="<?php echo $editData['RoleID']; ?>">
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>First Name</label>
                    <input type="text" name="fname" value="<?php echo $editData['FirstName']; ?>" required>
                </div>
                <div style="flex: 1;">
                    <label>Middle Name</label>
                    <input type="text" name="mname" value="<?php echo $editData['MiddleName']; ?>">
                </div>
                <div style="flex: 1;">
                    <label>Last Name</label>
                    <input type="text" name="lname" value="<?php echo $editData['LastName']; ?>" required>
                </div>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $editData['Email']; ?>" required>
            </div>
            <button type="submit" name="update_user" class="btn-save">Save Changes</button>
            <a href="manage_users.php" style="margin-left:10px; color:red;">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th> 
            </tr>
        </thead>
        <tbody>
            <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['RoleID']; ?></td>
                <td><?php echo htmlspecialchars($row['FirstName'] . " " . $row['LastName']); ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td>
                    <?php 
                        // Logic para i-display ang Role base sa user_roles table
                        if ($row['AdminID'] > 0) {
                            echo '<span class="badge bg-admin">ADMIN</span>';
                        } elseif ($row['FacultyID'] > 0) {
                            echo '<span class="badge bg-faculty">FACULTY</span>';
                        } elseif ($row['StaffID'] > 0) {
                            echo '<span class="badge bg-staff">STAFF</span>';
                        } elseif ($row['VisitorID'] > 0) {
                            echo '<span class="badge bg-visitor">VISITOR</span>';
                        } else {
                            echo '<span class="badge bg-student">STUDENT</span>';
                        }
                    ?>
                </td>
                <td>
                    <a href="manage_users.php?edit_id=<?php echo $row['RoleID']; ?>" class="btn-edit">Edit</a>
                    <a href="manage_users.php?delete_id=<?php echo $row['RoleID']; ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>