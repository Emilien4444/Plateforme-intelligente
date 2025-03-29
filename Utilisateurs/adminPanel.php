<?php
session_start();// Démarre la session
include '../BDD-Gestion/functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupère le niveau de l'utilisateur à partir de l'ID
$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

// Vérification si l'utilisateur est administrateur
if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Vérifier si l'ID de l'utilisateur à activer est passé dans l'URL
if (isset($_GET['id'])) {
    $userIdToActivate = $_GET['id'];

    // Maj du champ `is_active` à 1 pour activer l'utilisateur
    $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $userIdToActivate);

    if ($stmt->execute()) {
        // Si la mak est réussie, rediriger vers la page de gestion des utilisateurs avec un message de succès
        $_SESSION['message'] = "L'utilisateur a été activé avec succès !";
    } else {
        // Si une erreur se produit lors de la mise à jour, rediriger avec un message d'erreur
        $_SESSION['message'] = "Une erreur est survenue lors de l'activation de l'utilisateur.";
    }
}

// Rediriger l'administrateur vers la page précédente
header("Location: adminPanel.php");
exit();
?>
