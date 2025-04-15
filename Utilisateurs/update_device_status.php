<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
    exit();
}

$userId = $_SESSION['user_id'];

// Récupérer les données envoyées par Vue.js
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les paramètres nécessaires sont présents
if (isset($data['device_id'], $data['status'])) {
    $deviceId = $data['device_id'];
    $status = $data['status'];

    // Vérification si l'utilisateur est bien le propriétaire du device
    $sql = "SELECT user_id FROM devices WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deviceId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Permission refusée']);
        exit();
    }

    // Mettre à jour le statut de l'appareil dans la base de données
    $sqlUpdate = "UPDATE devices SET status = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("si", $status, $deviceId);

    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour du statut.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
}
?>
