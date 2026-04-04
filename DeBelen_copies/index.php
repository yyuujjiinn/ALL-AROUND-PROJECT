<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main</title>
</head>
<body>
    
<h1> Library Management System </h1>
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

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. Check if the user exists
    $sql = "SELECT * FROM user WHERE Email='$email' AND Password='$password'";
    $result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $uID = $row['RoleID'];
    
    $_SESSION['user_id'] = $uID;
    
    // PAGSAMAHIN ANG FIRST, MIDDLE, AT LAST NAME DAHIL WALANG "NAME" COLUMN
    $_SESSION['user_name'] = trim($row['FirstName'] . ' ' . (!empty($row['MiddleName']) ? $row['MiddleName'] . ' ' : '') . $row['LastName']);

    // Check the user_roles table
    $roleCheck = $conn->query("SELECT * FROM user_roles WHERE RoleID = '$uID'");
    $roleData = $roleCheck->fetch_assoc();

    // I-set ang Role Name sa session para rekta na sa dashboard
    if ($roleData['AdminID'] != 0) {
        $_SESSION['user_role'] = "Administrator";
        header("Location: admindashboard.php");
    } else {
        $_SESSION['user_role'] = "Student"; // O i-check ang ibang IDs (Faculty, Staff)
        header("Location: userdashboard.php");
    }
    exit();
} else {
    echo "<script>alert('Invalid email or password!');</script>";
}
}

?>
     
</body>
</html>
