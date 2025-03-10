<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

// Vérification stricte du rôle
$userRole = getUserRole($_SESSION['user_id']);
if ($userRole != 'complexe' && $userRole != 'admin') {
    die("Accès refusé.");
}

// Vérifie si un ID d'objet est présent
if (!isset($_GET['id'])) {
    die("Aucun objet sélectionné.");
}

$deviceId = intval($_GET['id']);
$device = getDeviceById($deviceId);

if (!$device) {
    die("Objet introuvable.");
}

?>

<?php include '../Principale/header.php'; ?>

<div class="form-container">
    <h2>Modifier l'état de l'objet</h2>
    <p><strong>Objet :</strong> <?= htmlspecialchars($device['name']) ?></p>

    <!-- Bouton ON/OFF -->
    <button id="toggle-btn" class="<?= ($device['status'] == 'actif') ? 'on' : 'off' ?>" 
            data-id="<?= $deviceId ?>" data-status="<?= $device['status'] ?>">
        <?= ($device['status'] == 'actif') ? 'ON' : 'OFF' ?>
    </button>

    <p id="status-message"></p>
</div>

<script>
    document.getElementById("toggle-btn").addEventListener("click", function () {
        let button = this;
        let deviceId = button.getAttribute("data-id");
        let currentStatus = button.getAttribute("data-status");
        let newStatus = (currentStatus === "actif") ? "inactif" : "actif";

        fetch("../Utilisateurs/toggle_device_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + deviceId + "&status=" + newStatus
        })
        .then(response => response.text())
        .then(data => {
            if (data === "success") {
                button.textContent = (newStatus === "actif") ? "ON" : "OFF";
                button.setAttribute("data-status", newStatus);
                button.classList.toggle("on");
                button.classList.toggle("off");
                document.getElementById("status-message").textContent = "Statut mis à jour avec succès.";
            } else {
                document.getElementById("status-message").textContent = "Erreur lors de la mise à jour.";
            }
        });
    });
</script>



<?php include '../Principale/footer.php'; ?>
