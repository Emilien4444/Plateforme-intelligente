<?php
include '../BDD-Gestion/functions.php';  

// Vérifier si l'utilisateur est authentifié
session_start();  // Démarre une session 
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas authentifié, renvoyer un message JSON d'erreur
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit();  // Sortir du script si l'utilisateur n'est pas authentifié
}

// Récupérer les données envoyées via AJAX (en JSON)
$data = json_decode(file_get_contents('php://input'), true);  // Décoder les données JSON envoyées en POST
$deviceId = $data['device_id'];  // Récupère l'ID du périphérique
$batteryStatus = $data['battery_status'];  // Récupère le niveau de batterie du périphérique

// Assurez-vous que le niveau de batterie ne dépasse pas 100% ni ne soit inférieur à 0%
if ($batteryStatus < 0) {
    $batteryStatus = 0;  // Si la batterie est inférieure à 0, on la définit à 0%
} elseif ($batteryStatus > 100) {
    $batteryStatus = 100;  // Si la batterie dépasse 100, on la limite à 100%
}

// Préparer la requête pour maj le niveau de batterie dans la BDD
$stmt = $conn->prepare("UPDATE devices SET battery_status = ? WHERE id = ?");  
$stmt->bind_param("ii", $batteryStatus, $deviceId);  // Lie les paramètres à la requête (niveau de batterie et ID du périphérique)

// Exécuter la requête SQL et vérifier si l'exécution a réussi
if ($stmt->execute()) {
    // Si l'exécution réussit -> renvoyer une réponse JSON avec succès
    echo json_encode(['success' => true]);
} else {
    // Si une erreur survient lors de l'exécution -> renvoyer une réponse JSON d'échec
    echo json_encode(['success' => false]);
}
?>