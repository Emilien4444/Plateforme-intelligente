<?php
// Inclut les fichiers nécessaires pour la connexion à la BDD et l'en-tête.
include '../BDD-Gestion/functions.php';
include '../Principale/header.php'; 

// Récupère l'ID de l'utilisateur dont le profil doit être affiché à partir de l'URL.
$userId = $_GET['id'];  // L'ID de l'utilisateur dont on veut afficher le profil

// Requête SQL pour récupérer les détails du profil de l'utilisateur en fonction de son ID.
$sql = "SELECT * FROM users WHERE id = ?";  
$stmt = $conn->prepare($sql);  
$stmt->bind_param("i", $userId);  // Lie l'ID de l'utilisateur à la requête pour éviter les injections SQL.
$stmt->execute();  
$user = $stmt->get_result()->fetch_assoc();  // Récupère les données de l'utilisateur sous forme de tableau associatif.

if (!$user) {
    die("Utilisateur introuvable.");  // Si l'utilisateur n'existe pas dans la BDD, affiche un message d'erreur.
}

// Gestion de l'affichage de la photo de profil
$profile_picture = (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture']))
    ? "uploads/" . $user['profile_picture']  // Si une photo de profil est présente et existe sur le serveur, elle est affichée.
    : "Profile_default.jpeg";  // Sinon, une image par défaut est affichée.

// Calcul de l'âge de l'utilisateur à partir de sa date de naissance.
$birthdate = $user['birthdate'];  // Récupère la date de naissance de l'utilisateur.
$age = date_diff(date_create($birthdate), date_create('today'))->y;  // Calcule l'âge de l'utilisateur en années.

?>

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary">👤 Profil de <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h2>
        <!-- Affiche le prénom et le nom de l'utilisateur dans le titre du profil -->
        
        <!-- Photo de profil -->
        <div class="text-center mb-3">
            <img src="<?= $profile_picture ?>" class="rounded-circle mb-3" width="150" height="150" alt="Photo de profil">
        </div>

        <!-- Informations publiques de l'utilisateur -->
        <div class="mb-3">
            <strong>Pseudonyme : </strong><?= htmlspecialchars($user['username']) ?>  <!-- Affiche le pseudonyme de l'utilisateur -->
        </div>
        <div class="mb-3">
            <strong>Date de naissance : </strong><?= htmlspecialchars($user['birthdate']) ?> (<?= $age ?> ans)  <!-- Affiche la date de naissance et l'âge de l'utilisateur -->
        </div>
        <div class="mb-3">
            <strong>Genre : </strong><?= htmlspecialchars($user['gender']) ?>  <!-- Affiche le genre de l'utilisateur -->
        </div>
        <div class="mb-3">
            <strong>Type de membre : </strong><?= htmlspecialchars($user['member_type']) ?>  <!-- Affiche le type de membre de l'utilisateur -->
        </div>
    </div>
</div>

</body>
</html>
