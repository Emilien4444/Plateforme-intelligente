<?php
include '../BDD-Gestion/functions.php';
include '../Principale/header.php'; 

$userId = $_GET['id'];  // L'ID de l'utilisateur dont on veut afficher le profil

// Requête pour récupérer les détails du profil
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Utilisateur introuvable.");
}

// Affichage des informations de profil
$profile_picture = (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture']))
    ? "uploads/" . $user['profile_picture']
    : "default-profile.png";

// Calcul de l'âge
$birthdate = $user['birthdate'];
$age = date_diff(date_create($birthdate), date_create('today'))->y;
?>

<div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center text-primary">👤 Profil de <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h2>

            <!-- Photo de profil -->
            <div class="text-center mb-3">
                <img src="<?= $profile_picture ?>" class="rounded-circle mb-3" width="150" height="150" alt="Photo de profil">
            </div>

            <!-- Informations publiques de l'utilisateur -->
            <div class="mb-3">
                <strong>Pseudonyme : </strong><?= htmlspecialchars($user['username']) ?>
            </div>
            <div class="mb-3">
                <strong>Date de naissance : </strong><?= htmlspecialchars($user['birthdate']) ?> (<?= $age ?> ans)
            </div>
            <div class="mb-3">
                <strong>Genre : </strong><?= htmlspecialchars($user['gender']) ?>
            </div>
            <div class="mb-3">
                <strong>Type de membre : </strong><?= htmlspecialchars($user['member_type']) ?>
            </div>
        </div>
    </div>
</body>
</html>

