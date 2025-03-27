<?php
session_start();
include '../BDD-Gestion/config.php';

if (!isset($_GET['token'])) {
    die("Erreur : Aucun token dans l'URL.");
}

$token = $_GET['token'];
// echo "Token reçu : " . htmlspecialchars($token) . "<br>";

// Vérifier si le token correspond à un utilisateur
$stmt = $conn->prepare("SELECT id, email FROM users WHERE invitation_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $userId = $user['id']; // Récupère l'ID de l'utilisateur invité

    // Récupérer la `family_id` du chef de famille (l'invitant)
    $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?"); // préparation de la requête sql (évite les injonctions)
    $stmt->bind_param("i", $_SESSION['user_id']); 
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc(); // donne accès aux lignes renvoyées par la requête

    if ($family && $family['family_id']) {
        $familyId = $family['family_id'];

        // Mettre à jour `family_id` de l'invité et supprimer le `token`
        $stmt = $conn->prepare("UPDATE users SET family_id = ?, invitation_token = NULL WHERE id = ?");
        $stmt->bind_param("ii", $familyId, $userId);
        if ($stmt->execute()) {
            echo "Vous avez rejoint la famille avec l'ID $familyId.";
            exit();
        } else {
            echo "Erreur lors de la mise à jour du `family_id`.";
        }
    } else {
        echo "Impossible de récupérer la famille du chef.";
    }
} else {
    echo "Token invalide ou expiré.";
}
?>
