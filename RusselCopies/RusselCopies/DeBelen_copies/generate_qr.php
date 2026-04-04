<?php
include 'phpqrcode/qrlib.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $url = "http://localhost/DeBelen_copies/borrow_process.php?id=" . $id;

    header('Content-Type: image/png');
    QRcode::png($url);
    exit();
}
?>