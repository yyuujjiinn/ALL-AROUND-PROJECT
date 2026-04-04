<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main</title>
</head>
<body>
    
<h1>Library Management System</h1>
<h1>Welcome</h1>

<form method="POST" action="index.php">
    <label for="email">Email: </label>
    <input type="text" name="email" id="email" required><br><br>

    <label for="password">Password: </label>
    <input type="password" name="password" id="pword_field" required><br>

    <br><input type="checkbox" onclick="togglePassword()"> Show Password
    <br>
    <a href="usersignup.php"><p>No Account?</p></a>
    
    <input type="submit" name="login" value="Log-in" id="login">
</form>

<!-- New Guest Button -->
<form method="POST" action="index.php" style="margin-top:15px;">
    <input type="submit" name="guest_login" value="Sign in as Guest">
</form>

<script>
function togglePassword() {
    var x = document.getElementById("pword_field");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>

<?php
session_start();
include 'connect.php';

// Regular login
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM user WHERE Email='$email' AND Password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $uID = $row['RoleID'];
        
        $_SESSION['user_id'] = $uID; 
        $_SESSION['user_name'] = $row['Name'];

        // Check admin role
        $roleCheck = $conn->query("SELECT AdminID FROM user_roles WHERE RoleID = '$uID'");
        $roleData = $roleCheck->fetch_assoc();

        if ($roleData && $roleData['AdminID'] != 0) {
            header("Location: admindashboard.php");
        } else {
            // Check if Visitor 
            $visitorCheck = $conn->query("SELECT VisitorID FROM user_roles WHERE RoleID='$uID'");
            $visitorData = $visitorCheck->fetch_assoc();
            if ($visitorData && $visitorData['VisitorID'] != 0) {
                header("Location: visitordashboard.php");
            } else {
                header("Location: userdashboard.php");
            }
        }
        exit();
    } else {
        echo "<script>alert('Invalid email or password'); window.location='index.php';</script>";
    }
}

// Guest login
if (isset($_POST['guest_login'])) {
    $_SESSION['user_id'] = 0; // No DB user
    $_SESSION['user_name'] = "Guest";
    $_SESSION['is_guest'] = true;

    header("Location: visitordashboard.php");
    exit();
}
?>
     
</body>
</html>