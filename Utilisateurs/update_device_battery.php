<?php
include '../BDD-Gestion/functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$deviceId = $data['device_id'];
$batteryStatus = $data['battery_status'];

// Assurez-vous que la batterie ne dépasse pas 100% ni ne soit inférieure à 0%
if ($batteryStatus < 0) {
    $batteryStatus = 0;
} elseif ($batteryStatus > 100) {
    $batteryStatus = 100;
}

// Préparer la mise à jour du niveau de batterie dans la base de données
$stmt = $conn->prepare("UPDATE devices SET battery_status = ? WHERE id = ?");
$stmt->bind_param("ii", $batteryStatus, $deviceId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
