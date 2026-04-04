<?php
session_start();
include 'connect.php';

// 1. Security Check
if (!isset($_SESSION['is_guest'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];
$uName = $_SESSION['user_name'];

// 2. If NOT guest, validate visitor role
if (!$_SESSION['is_guest']) {
    $roleQuery = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$uID'");
    $role = $roleQuery->fetch_assoc();

    if (!$role || $role['VisitorID'] == 0) {
        header("Location: userdashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Dashboard - BulSU Library</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        .header {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 20px;
            text-align: center;
            border-bottom: 5px solid #007bff;
        }

        .logout-btn {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
            border: 1px solid #dc3545;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .role-badge {
            background: #e7f3ff;
            color: #007bff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            border: 1px solid #007bff;
        }

        .catalog-link {
            background: #007bff;
            color: white;
            padding: 12px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1 style="margin:0; color: #007bff;">BulSU Library System</h1>
        <p style="margin:5px 0 0;">
            Welcome, <strong><?= htmlspecialchars($uName) ?></strong>
        </p>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="card">
    <h2 style="margin-top: 0; color: #333;">Guest Access</h2>
    <p style="color: #666;">
        You are logged in as a <span class="role-badge">Guest</span>.<br><br>
        You can browse the library catalog, but borrowing is restricted.
    </p>

    <a href="view_books.php" class="catalog-link">
        Browse Catalog →
    </a>
</div>

</body>
</html>