<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/config.php';

// Vérifier si le token est présent dans l'URL
if (!isset($_GET['token'])) {
    die("Erreur : Aucun token dans l'URL."); // Si le token n'est pas présent, une erreur est affichée
}

$token = $_GET['token'];  // Récupère le token envoyé dans l'URL

// Vérifier si le token correspond à un utilisateur dans la base de données
$stmt = $conn->prepare("SELECT id, email FROM users WHERE invitation_token = ?"); 
$stmt->bind_param("s", $token);  // Lie le token à la requête
$stmt->execute(); 
$result = $stmt->get_result();  // Récupère le résultat de la requête

// Si un utilisateur correspondant au token trouvé
if ($user = $result->fetch_assoc()) {
    $userId = $user['id'];  // Récupère l'ID de l'utilisateur invité

    // Récupérer le `family_id` du chef de famille 
    $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?"); 
    $stmt->bind_param("i", $_SESSION['user_id']);  // Lie l'ID de l'utilisateur connecté 
    $stmt->execute();  
    $result = $stmt->get_result();  // Récupère le résultat de la requête
    $family = $result->fetch_assoc();  // Récupère les informations de la famille du chef

    // Si une famille est trouvée pour le chef de famille
    if ($family && $family['family_id']) {
        $familyId = $family['family_id'];  // Récupère l'ID de la famille

        // Maj le `family_id` de l'utilisateur invité et supprime le token
        $stmt = $conn->prepare("UPDATE users SET family_id = ?, invitation_token = NULL WHERE id = ?");  // Requête pour associer l'invité à la famille et supprimer le token
        $stmt->bind_param("ii", $familyId, $userId);  // Lie l'ID de la famille et l'ID de l'utilisateur invité
        if ($stmt->execute()) {
            echo "Vous avez rejoint la famille avec l'ID $familyId.";  // Message de succès
            exit();  // Arrête l'exécution du script
        } else {
            echo "Erreur lors de la mise à jour du `family_id`.";  // Message d'erreur si la maj échoue
        }
    } else {
        echo "Impossible de récupérer la famille du chef.";  // Message d'erreur si le chef de famille n'a pas de famille associée
    }
} else {
    echo "Token invalide ou expiré.";  // Message d'erreur si le token est invalide ou expiré
}
?>
