<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php"); // Si l'utilisateur n'est pas connecté, le redirige vers la page de login
    exit();
}

// Vérifier si l'utilisateur a le niveau d'accès "expert"
$userlevel = getUserLevel($_SESSION['user_id']); // Récupère le niveau de l'utilisateur
if ($userlevel != 'expert') {
    die("Accès refusé."); // Si l'utilisateur n'a pas le niveau requis -> on arrête l'exécution + affiche un message d'erreur
}

// Vérifie si l'ID de l'objet est passé en paramètre dans l'URL
if (!isset($_GET['id'])) {
    die("Aucun objet sélectionné."); // Si l'ID de l'objet n'est pas spécifié  -> arrête le script
}

$deviceId = $_GET['id']; // Récupère l'ID de l'objet à supprimer
$device = getDeviceById($deviceId); // Récupère les informations de l'objet en appelant une fonction

// Vérifier si l'objet existe
if (!$device) {
    die("Objet introuvable."); // Si l'objet n'existe pas -> message d'erreur
}

// Si le formulaire est soumis, supprimer l'objet
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (deleteDevice($deviceId)) { // Si l'objet est supprimé avec succès
        addLog($_SESSION['user_id'], "Suppression de l'objet ID $deviceId"); // Ajouter un log pour suivre l'action dans la BDD
        // Redirection après suppression avec un message de succès
        header("Location: dashboard.php?message=Objet supprimé avec succès !");
        exit();
    } else {
        // Si une erreur survient lors de la suppression, afficher un message d'erreur
        echo '<div class="alert alert-danger">Erreur lors de la suppression de l\'objet.</div>';
    }
}
?>


<?php include '../Principale/header.php'; ?> 

<div class="form-container">
    <h2>Supprimer l'objet connecté</h2>
    <!-- Affiche un message de confirmation avec le nom de l'objet à supprimer -->
    <p>Voulez-vous vraiment supprimer cet objet : <strong><?= htmlspecialchars($device['name']) ?></strong> ?</p>
    
    <!-- Formulaire pour confirmer la suppression -->
    <form method="POST">
        <button type="submit" style="background-color: red; color: white;" class="btn btn-danger">Oui, supprimer</button>
        <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include '../Principale/footer.php'; ?> 
