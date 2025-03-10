<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['status'])) {
    $deviceId = intval($_POST['id']);
    $newStatus = ($_POST['status'] === 'actif') ? 'actif' : 'inactif';

    if (updateDeviceStatus($deviceId, $newStatus)) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>
