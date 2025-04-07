<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté -> redirige vers la page de login
    exit(); 
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Récupère le niveau de l'utilisateur 

// Vérifie si l'utilisateur a le niveau d'accès
if ($userLevel != 'expert') {
    header("Location: index.php"); // Si l'utilisateur n'a pas les droits -> redirige vers la page principale
    exit(); 
}

// Vérification si l'ID de l'utilisateur à supprimer est passé en paramètre dans l'URL
if (isset($_GET['id'])) {
    $userIdToDelete = $_GET['id']; // Récupère l'ID de l'utilisateur à supprimer

    // Requête SQL pour supprimer l'utilisateur de la base de données
    $sqlDelete = "DELETE FROM users WHERE id = ?"; // Requête pour supprimer un utilisateur basé sur son ID
    $stmtDelete = $conn->prepare($sqlDelete); 
    $stmtDelete->bind_param("i", $userIdToDelete); // Lie l'ID de l'utilisateur à la requête
    $stmtDelete->execute(); 

    header("Location: adminPannel.php"); // Redirige l'administrateur vers le panneau d'administration après la suppression
    exit(); 
} else {
    die("ID utilisateur non spécifié."); // Si l'ID de l'utilisateur n'est pas passé en paramètre -> afficher un message d'erreur
}
?>
