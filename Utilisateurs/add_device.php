<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/functions.php'; 

// Vérifier si l'utilisateur est connecté 
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php"); // Si l'utilisateur n'est pas connecté, redirige vers la page de login
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur
$message = ""; // Initialisation d'une variable pour afficher les différents messages

// Vérification si le formulaire a été soumis via la méthode POST ( + sécrurisé que GET )
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_device'])) {
    // Récupération des données 
    $name = trim($_POST['name']);  // Nom de l'objet connecté
    $type = trim($_POST['type']);  // Type de l'objet 
    $brand = trim($_POST['brand']);  // Marque de l'objet
    $connectivity = trim($_POST['connectivity']);  // Connectivité 
    $battery_status = intval($_POST['battery_status']);  // Niveau de batterie de l'objet
    $target_temperature = floatval($_POST['target_temperature']);  // Température cible 
    $mode = trim($_POST['mode']);  // Mode de fonctionnement 
    $status = trim($_POST['status']);  // Statut de l'objet
    $location = trim($_POST['location']);  // Lieu où l'objet est installé 

    // Requête SQL pour insérer un nouvel objet ds la BDD
    $sql = "INSERT INTO devices (user_id, name, type, brand, connectivity, battery_status, target_temperature, mode, status, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Préparer la requête SQL et lie les paramètres
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssdsss", $userId, $name, $type, $brand, $connectivity, $battery_status, $target_temperature, $mode, $status, $location);

    // Exécuter la requête et vérifier si l'insertion a réussi
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">Objet connecté ajouté avec succès.</div>';  // Message de succès
    } else {
        $message = '<div class="alert alert-danger text-center">Erreur lors de l\'ajout de l\'objet connecté.</div>';  // Message d'erreur si l'insertion échoue
    }
}
?>
