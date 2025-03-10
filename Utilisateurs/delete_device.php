<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

// Vérifie que l'utilisateur est bien "complexe" ou "admin"
$userRole = getUserRole($_SESSION['user_id']);
if ($userRole != 'complexe' && $userRole != 'admin') {
    die("Accès refusé.");
}

// Vérifie si un ID d'objet est présent
if (!isset($_GET['id'])) {
    die("Aucun objet sélectionné.");
}

$deviceId = $_GET['id'];
$device = getDeviceById($deviceId);

if (!$device) {
    die("Objet introuvable.");
}

// Supprime l'objet si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (deleteDevice($deviceId)) {
        addLog($_SESSION['user_id'], "Suppression de l'objet ID $deviceId");
        header("Location: dashboard.php?message=Objet supprimé !");
        exit();
    } else {
        echo "Erreur lors de la suppression.";
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Supprimer l'objet connecté</h2>
    <p>Voulez-vous vraiment supprimer cet objet : <strong><?= htmlspecialchars($device['name']) ?></strong> ?</p>
    
    <form method="POST">
        <button type="submit" style="background-color: red;">Oui, supprimer</button>
        <a href="dashboard.php" class="btn">Annuler</a>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
