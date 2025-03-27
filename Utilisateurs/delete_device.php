<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

// Vérifier si l'utilisateur est "expert" ou "admin"
$userlevel = getUserLevel($_SESSION['user_id']);
if ($userlevel != 'expert') {
    die("Accès refusé.");
}

// Vérifier si l'ID de l'objet est passé en paramètre dans l'URL
if (!isset($_GET['id'])) {
    die("Aucun objet sélectionné.");
}

$deviceId = $_GET['id'];
$device = getDeviceById($deviceId);

// Vérifier si l'objet existe
if (!$device) {
    die("Objet introuvable.");
}

// Supprimer l'objet si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (deleteDevice($deviceId)) {
        addLog($_SESSION['user_id'], "Suppression de l'objet ID $deviceId");
        // Redirection après suppression avec message de succès
        header("Location: dashboard.php?message=Objet supprimé avec succès !");
        exit();
    } else {
        // Afficher un message d'erreur en cas de problème
        echo '<div class="alert alert-danger">❌ Erreur lors de la suppression de l\'objet.</div>';
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="form-container">
    <h2>Supprimer l'objet connecté</h2>
    <p>Voulez-vous vraiment supprimer cet objet : <strong><?= htmlspecialchars($device['name']) ?></strong> ?</p>
    
    <form method="POST">
        <button type="submit" style="background-color: red; color: white;" class="btn btn-danger">Oui, supprimer</button>
        <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>
