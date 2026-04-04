<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fineID = intval($_POST['fineID']);   // ensure integer
    $status = $_POST['status'];
    $borrowId = intval($_POST['borrowId']); // galing sa hidden field sa form
    $status   = $_POST['status'];

    // Allowed status values
    $allowedStatus = ['Unpaid', 'Paid', 'Cleared', 'Pending Replacement'];
    if (!in_array($status, $allowedStatus)) {
        header("Location: admin_fines.php?msg=error&detail=Invalid+status");
        exit();
    }

    // --- LOGIC: decide Type ---
    $bookMissing = isset($_POST['missing']) && $_POST['missing'] == '1'; 
    $returnDate  = !empty($_POST['returnDate']) ? $_POST['returnDate'] : null;
    $dueDate     = !empty($_POST['dueDate']) ? $_POST['dueDate'] : null;

    if ($bookMissing) {
        $type = 'Missing';

        // Update din ang borrow table para malinaw na Missing
        $stmt2 = $conn->prepare("UPDATE borrow SET Status = 'Missing' WHERE BorrowID = ?");
        $stmt2->bind_param("i", $borrowId);
        $stmt2->execute();
        $stmt2->close();

    } elseif ($returnDate && $dueDate && $returnDate > $dueDate) {
        $type = 'Overdue';
    } else {
        $type = 'Overdue'; // fallback kung walang condition
    }

    // Update query (Status + Type)
    $stmt = $conn->prepare("UPDATE fines SET Status = ?, Type = ? WHERE FineID = ?");
    $stmt->bind_param("ssi", $status, $type, $fineID);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: admin_fines.php?msg=success");
        exit();
    } else {
        $error = $conn->error;
        $stmt->close();
        $conn->close();
        header("Location: admin_fines.php?msg=error&detail=" . urlencode($error));
        exit();
    }
}
?>
