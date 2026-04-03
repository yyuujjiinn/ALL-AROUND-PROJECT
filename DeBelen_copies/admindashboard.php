<?php
session_start();
include 'connect.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uID = $_SESSION['user_id'];
$uName = $_SESSION['user_name'];

$adminCheck = $conn->query("SELECT AdminID FROM user_roles WHERE RoleID = '$uID'");
$isAdmin = $adminCheck->fetch_assoc();

if (!$isAdmin || $isAdmin['AdminID'] == 0) {
    header("Location: userdashboard.php");
    exit();
}

// Default stats
$totalBooks = $conn->query("SELECT SUM(Quantity) as total FROM books")->fetch_assoc();
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM user")->fetch_assoc();
$pendingReturns = $conn->query("SELECT COUNT(*) as total FROM borrow WHERE Status = 'Borrowed'")->fetch_assoc();
$unreadNotifs = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE Status = 'Unread'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Library System</title>
    <style>
         :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --dark: #343a40;
            --light: #f8f9fa;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 20px; 
            color: #333;
        }

         .container { max-width: 1200px; margin: auto; }

        .header { 
            background: var(--dark); 
            color: white; 
            padding: 25px; 
            border-radius: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: var(--shadow);
        }

        .header h1 { margin: 0; font-size: 1.8em; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; opacity: 0.8; }

        .logout-btn { 
            background: var(--danger); 
            color: white; 
            padding: 10px 20px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: bold; 
            transition: 0.3s;
        }

        .logout-btn:hover { background: #bd2130; transform: scale(1.05); }

        /* Stats Section */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin: 25px 0; 
        }

        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center; 
            box-shadow: var(--shadow);
            border-bottom: 4px solid var(--primary);
        }

          .stat-card p { margin: 0; color: #666; font-weight: 600; text-transform: uppercase; font-size: 0.8em; }
        .stat-card h2 { margin: 10px 0 0; font-size: 2.2em; color: var(--dark); }

        /* Menu Section */
        .menu-grid {  
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
        }

        .menu-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: var(--shadow); 
            transition: all 0.3s ease; 
            text-decoration: none; 
            color: #333; 
            border-left: 6px solid var(--primary);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .menu-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 12px 20px rgba(0,0,0,0.15); 
        }

        .menu-card h3 { margin: 0 0 10px; font-size: 1.3em; display: flex; align-items: center; }
        .menu-card p { margin: 0; color: #777; font-size: 0.95em; line-height: 1.5; }

        /* Responsive Tags for Menu Colors */
        .card-books { border-left-color: #007bff; }
        .card-materials { border-left-color: #6610f2; }
        .card-notif { border-left-color: #ffc107; }
        .card-archive { border-left-color: #6c757d; }
        .card-records { border-left-color: #28a745; }
        .card-users { border-left-color: #17a2b8; }
        .card-fines { border-left-color: #dc3545; }

        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; gap: 15px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Library Admin Panel</h1>
            <p>Welcome, <strong><?php echo htmlspecialchars($uName); ?></strong> (System Administrator)</p>
        </div>
        <a href="logout.php" class="logout-btn">Logout System</a>
    </div>

    <!-- Search Bar with dropdown -->
    <div style="margin:20px 0; position:relative; max-width:1176px;">
        <input type="text" id="searchBox" placeholder="Search across dashboard..." 
               style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
               
        <!-- Dropdown results -->
        <div id="results" 
             style="position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ccc; border-radius:0 0 8px 8px; max-height:500px; overflow-y:auto; display:none; z-index:1000;">
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card"><p>Total Books</p><h2><?php echo number_format($totalBooks['total'] ?? 0); ?></h2></div>
        <div class="stat-card" style="border-bottom-color: #17a2b8;"><p>Registered Users</p><h2><?php echo $totalUsers['total']; ?></h2></div>
        <div class="stat-card" style="border-bottom-color: var(--success);"><p>Active Borrows</p><h2><?php echo $pendingReturns['total']; ?></h2></div>
        <div class="stat-card" style="border-bottom-color: var(--warning);"><p>Unread Notices</p><h2><?php echo $unreadNotifs['total']; ?></h2></div>
    </div>

    <!-- Menu -->
    <div class="menu-grid">
        <a href="book.php" class="menu-card card-books"><h3>📚 Book Management</h3><p>Inventory control...</p></a>
        <a href="materials_catalog.php" class="menu-card card-materials"><h3>🧰 Materials Management</h3><p>Handle non-book...</p></a>
        <a href="admin_notifications.php" class="menu-card card-notif"><h3>📣 Notification Center</h3><p>Send announcements...</p></a>
        <a href="borrow_records.php" class="menu-card card-records"><h3>📝 Borrowing Records</h3><p>Track borrows...</p></a>
        <a href="manage_users.php" class="menu-card card-users"><h3>👥 User Management</h3><p>Monitor accounts...</p></a>
        <a href="admin_fines.php" class="menu-card card-fines"><h3>💰 Fine Management</h3><p>View penalties...</p></a>
        <a href="archive.php" class="menu-card card-archive"><h3>📂 Archive & Recovery</h3><p>Restore deleted...</p></a>
    </div>
</div>

<script>
const searchBox = document.getElementById("searchBox");
const resultsDiv = document.getElementById("results");

searchBox.addEventListener("keyup", function() {
    let query = this.value;

    if(query.length === 0){
        resultsDiv.style.display = "none";
        resultsDiv.innerHTML = "";
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "search_dashboard.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            resultsDiv.innerHTML = xhr.responseText;
            resultsDiv.style.display = "block";
        }
    };
    xhr.send("search=" + encodeURIComponent(query));
});

// Hide dropdown when clicking outside
document.addEventListener("click", function(e){
    if(!searchBox.contains(e.target) && !resultsDiv.contains(e.target)){
        resultsDiv.style.display = "none";
    }
});
</script>
</body>
</html>
