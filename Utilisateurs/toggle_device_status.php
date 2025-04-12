<?php
session_start();  // Démarre la session 

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();  // Arrête l'exécution du script
}

// Récupérer les données envoyées par AJAX (en JSON)
$data = json_decode(file_get_contents('php://input'), true);  // Décoder les données JSON envoyées via AJAX

// Si le device_id ou le status n'est pas fourni, renvoyer une erreur
if (empty($data['device_id']) || empty($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();  // Sortir du script si les données nécessaires sont absentes
}

$deviceId = $data['device_id'];  // Récupère l'ID de l'appareil
$status = $data['status'];  // Récupère le nouvel état 

// Maj de l'état de l'objet dans la base de données
$sql = "UPDATE devices SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);  // Prépare la requête SQL pour exécution
$stmt->bind_param("si", $status, $deviceId);  // Lie les paramètres à la requête 

if ($stmt->execute()) {
    // Si l'exécution de la requête réussit -> renvoyer une réponse JSON de succès
    echo json_encode(['success' => true, 'status' => $status]);
} else {
    // Si une erreur survient lors de la maj -> renvoyer une réponse JSON d'erreur
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}
?>
