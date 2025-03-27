<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Récupérer les données envoyées par AJAX
$data = json_decode(file_get_contents('php://input'), true);

// Vérification de l'existence des données nécessaires
if (empty($data['device_id']) || empty($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();  // Sortir du script si les données sont manquantes
}

$deviceId = $data['device_id'];
$status = $data['status'];  // 'active' ou 'inactive'

// Mise à jour de l'état de l'objet dans la base de données
$sql = "UPDATE devices SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $deviceId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}
?>
