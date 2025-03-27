<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

if ($userLevel != 'expert') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $userIdToDelete = $_GET['id'];

    // Supprimer l'utilisateur de la base de données
    $sqlDelete = "DELETE FROM users WHERE id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $userIdToDelete);
    $stmtDelete->execute();

    header("Location: adminPannel.php");
    exit();
} else {
    die("ID utilisateur non spécifié.");
}
