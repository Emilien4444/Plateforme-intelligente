<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php"); // Si l'utilisateur n'est pas connecté -> redirige vers la page de login
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur
$message = ""; // Initialisation d'une variable pour afficher les différents messages

// Vérification si le formulaire a été soumis via la méthode POST ( + sécurisé que GET )
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

    // Requête SQL pour insérer un nouvel objet dans la BDD
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

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <h1 class="text-center mb-4">Ajouter un Nouvel Objet Connecté</h1>

    <!-- Afficher les messages si il y en a -->
    <?php if ($message != "") echo $message; ?>

    <!-- Formulaire d'ajout d'objet connecté -->
    <form method="POST" action="" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Nom de l'Objet</label>
            <input type="text" name="name" id="name" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer le nom de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type de l'Objet</label>
            <input type="text" name="type" id="type" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer le type de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Marque</label>
            <input type="text" name="brand" id="brand" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer la marque de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="connectivity" class="form-label">Connectivité</label>
            <input type="text" name="connectivity" id="connectivity" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer la connectivité de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="battery_status" class="form-label">Niveau de Batterie (%)</label>
            <input type="number" name="battery_status" id="battery_status" class="form-control" required min="0" max="100">
            <div class="invalid-feedback">Veuillez entrer le niveau de batterie de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="target_temperature" class="form-label">Température Cible (°C)</label>
            <input type="number" name="target_temperature" id="target_temperature" class="form-control" required step="0.1">
            <div class="invalid-feedback">Veuillez entrer la température cible de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="mode" class="form-label">Mode de Fonctionnement</label>
            <input type="text" name="mode" id="mode" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer le mode de fonctionnement de l'objet.</div>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Statut de l'Objet</label>
            <select name="status" id="status" class="form-control" required>
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Lieu d'Installation</label>
            <input type="text" name="location" id="location" class="form-control" required>
            <div class="invalid-feedback">Veuillez entrer le lieu d'installation de l'objet.</div>
        </div>

        <button type="submit" name="add_device" class="btn btn-success w-100">Ajouter l'Objet</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>

