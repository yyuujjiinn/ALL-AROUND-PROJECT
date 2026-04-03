<?php
include 'connect.php';

if (isset($_POST['fineID'])) {
    $fineID = $_POST['fineID'];

    // query para i-delete ang fine
    $sql = "DELETE FROM fines WHERE FineID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fineID);

    if ($stmt->execute()) {
        // balik sa admin_fines.php pagkatapos mag-delete
        header("Location: admin_fines.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No fine ID provided.";
}
?>
