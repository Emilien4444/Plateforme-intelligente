<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php"); // Si l'utilisateur n'est pas connecté -> le redirige vers la page de login
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$deviceId = intval($_GET['id']); // Récupère l'ID de l'objet connecté à modifier

// Vérifier si l'objet existe
$device = getDeviceById($deviceId); // Appelle la fonction pour récupérer les info de l'objet
if (!$device) {
    die("Objet introuvable."); // Si l'objet n'est pas trouvé -> arrêter le script
}

// Vérification du niveau de l'utilisateur -> seulement les utilisateurs "expert" peuvent accéder à cette page
$userlevel = getUserLevel($userId); // Récupère le niveau de l'utilisateur
if ($userlevel != 'expert') {
    die("Accès refusé."); // Si l'utilisateur n'est pas "expert" -> arrêter l'exécution et afficher un message d'erreur
}

$message = ""; // Initialisation de la var pour stocker le message de succès ou d'erreur

// Traitement du formulaire de maj de l'objet
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_device'])) {
        // Récupérer les données du formulaire pour maj l'objet
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $brand = trim($_POST['brand']);
        $connectivity = trim($_POST['connectivity']);
        $battery_status = intval($_POST['battery_status']);
        $target_temperature = floatval($_POST['target_temperature']);
        $mode = trim($_POST['mode']);
        $status = trim($_POST['status']);
        $location = trim($_POST['location']);

        // Maj de l'objet dans la base de données
        $sql = "UPDATE devices SET name = ?, type = ?, brand = ?, connectivity = ?, battery_status = ?, target_temperature = ?, mode = ?, status = ?, location = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param("sssssssssssi", $name, $type, $brand, $connectivity, $battery_status, $target_temperature, $mode, $status, $location, $deviceId, $userId); // Lier les paramètres à la requête

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success text-center">✅ Objet connecté mis à jour avec succès.</div>'; // Message de succès si la maj réussit
        } else {
            $message = '<div class="alert alert-danger text-center">❌ Erreur lors de la mise à jour de l\'objet connecté.</div>'; // Message d'erreur si la maj échoue
        }
    }

    // Gestion de l'état ON/OFF
    if (isset($device['toggle_status'])) {
        // Envoi de la requête AJAX pour mettre à jour le statut
        $newStatus = ($_POST['status'] == 'active') ? 'inactive' : 'active'; // Alterne le statut (actif/inactif)

        // Appel au fichier toggle_device_status.php pour changer le statut de l'objet
        $url = 'toggle_device_status.php';
        $data = [
            'device_id' => $deviceId,
            'status' => $newStatus
        ];

        $ch = curl_init($url);  // Initialise une nouvelle session cURL avec l'URL spécifiée

        // Définir l'option cURL pour indiquer que la réponse doit être retournée sous forme de chaîne
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

        // Définir l'option pour envoyer une requête HTTP POST
        curl_setopt($ch, CURLOPT_POST, true); 

        // Définir l'option pour spécifier les données à envoyer avec la requête POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // La fct http_build_query() transforme le tableau associatif $data en une chaîne de caractères formatée pour être envoyée dans une requête POST
        // Cette option permet d'envoyer les données que l'on souhaite transmettre au serveur via la requête POST

        // Exécuter la requête cURL et récupérer la réponse
        $response = curl_exec($ch); 

        // Fermer la connexion cURL pour libérer les ressources système
        curl_close($ch); 

        $response = json_decode($response, true); // Décoder la réponse JSON
        if ($response['success']) {
            $message = '<div class="alert alert-success text-center">Statut mis à jour avec succès.</div>'; // Message de succès si la mise à jour du statut réussit
        } else {
            $message = '<div class="alert alert-danger text-center">Erreur lors de la mise à jour du statut.</div>'; // Message d'erreur si la mise à jour du statut échoue
        }
    }
}
?>


<?php include '../Principale/header.php'; ?>

<div class="form-container">
    <h2>Modifier l'état de l'objet</h2>
    <p><strong>Objet :</strong> <?= htmlspecialchars($device['name']) ?></p>

    <!-- Affichage du message -->
    <?php if ($message) echo $message; ?>

    <div class="card shadow p-3 mb-4">
    <!-- Formulaire de modification de l'objet -->
    <form method="POST">
        <input type="hidden" name="device_id" value="<?= $device['id'] ?>">

        <!-- Bouton ON/OFF -->
        <button type="submit" name="toggle_status" class="btn btn-<?= ($device['status'] == 'actif') ? 'success' : 'danger' ?>" 
            data-id="<?= $deviceId ?>" data-status="<?= $device['status'] ?>">
            <?= ($device['status'] == 'actif') ? 'Allumer' : 'Éteindre' ?>
        </button>

        <p id="status-message"></p>
        
        <!-- Champs pour modifier l'objet -->
        <div class="mb-3">
            <label for="name" class="form-label">Nom de l'objet</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($device['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="<?= htmlspecialchars($device['type']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Marque</label>
            <input type="text" id="brand" name="brand" class="form-control" value="<?= htmlspecialchars($device['brand']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="connectivity" class="form-label">Connectivité</label>
            <input type="text" id="connectivity" name="connectivity" class="form-control" value="<?= htmlspecialchars($device['connectivity']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="battery_status" class="form-label">Niveau de batterie</label>
            <input type="number" id="battery_status" name="battery_status" class="form-control" value="<?= htmlspecialchars($device['battery_status']) ?>" required min="0" max="100">
        </div>

        <div class="mb-3">
            <label for="target_temperature" class="form-label">Température cible</label>
            <input type="number" id="target_temperature" name="target_temperature" class="form-control" value="<?= htmlspecialchars($device['target_temperature']) ?>" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="mode" class="form-label">Mode</label>
            <input type="text" id="mode" name="mode" class="form-control" value="<?= htmlspecialchars($device['mode']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select name="status" id="status" class="form-select">
                <option value="actif" <?= ($device['status'] == 'actif') ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= ($device['status'] == 'inactif') ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Emplacement</label>
            <input type="text" id="location" name="location" class="form-control" value="<?= htmlspecialchars($device['location']) ?>" required>
        </div>

        <button type="submit" name="edit_device" class="btn btn-primary">Mettre à jour l'objet</button>
    </form>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
