<?php
session_start();
include '../BDD-Gestion/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (addDevice($_SESSION['user_id'], $_POST['name'], $_POST['type'])) {
        addLog($_SESSION['user_id'], "Ajout d'un nouvel objet connecté : $name ($type)");
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Erreur lors de l'ajout.";
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Ajouter un Objet Connecté</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Nom de l'objet" required>
        <input type="text" name="type" placeholder="Type (ex: Thermostat, Caméra)" required>
        <button type="submit">Ajouter</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>
