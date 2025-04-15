<?php
session_start(); // Démarre la session PHP 
include '../BDD-Gestion/functions.php';

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$level = getUserLevel($userId); // Récupère le niveau d'accès de l'utilisateur

// Vérifier si l'utilisateur a le niveau "expert"
if ($level != 'expert') {
    header("Location: ../Principale/index.php"); // Redirige vers la page principale si l'utilisateur n'a pas les droits d'accès
    exit();  
}

$familyId = $_SESSION['user_id']; // L'ID du chef de famille est l'ID de l'utilisateur connecté
$message = ""; // Variable pour les messages 

// Récupérer les membres de la famille
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE family_id = ? AND id != ?"); // Prépare la requête pour récupérer les membres de la famille
$stmt->bind_param("ii", $familyId, $familyId); // Lier l'ID de la famille et de l'utilisateur pour éviter d'afficher le chef de famille
$stmt->execute(); // Exécute la requête
$familyMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les membres sous forme de tableau associatif


// Envoyer une invitation par email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invite_email'])) {
    $inviteEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Nettoyer l'email envoyé par l'utilisateur

    if (!filter_var($inviteEmail, FILTER_VALIDATE_EMAIL)) { // Vérifier si l'email est valide
        $message = "Adresse email invalide."; // Message d'erreur si l'email est invalide
    } else {
        sendInvitationEmail($inviteEmail, $familyId); // Appel de la fonction pour envoyer l'invitation
        $message = "Invitation envoyée à $inviteEmail !"; // Message de succès après l'envoi de l'invitation
    }
}

// Supprimer un membre de la famille
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_user'])) {
    $removeUserId = $_POST['user_id']; // Récupère l'ID de l'utilisateur à supprimer

    if (removeUserFromFamily($familyId, $removeUserId)) { // Appel de la fonction pour supprimer le membre
        $message = "Membre supprimé avec succès !"; // Message de succès
    } else {
        $message = "Erreur lors de la suppression."; // Message d'erreur si la suppression échoue
    }
}

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">👨‍👩‍👧‍👦 Gestion de votre famille</h2>

    <!-- Affichage des messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Formulaire d'invitation -->
    <div class="card p-4 mb-4">
        <h3 class="mb-3">Inviter un membre :</h3>
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email de l'utilisateur" required>
            </div>
            <button type="submit" name="invite_email" class="btn btn-primary w-100">Envoyer une invitation</button>
        </form>
    </div>

    <!-- Liste des membres -->
    <div class="card p-4">
        <h3 class="mb-3">Membres actuels :</h3>
        <?php if (count($familyMembers) > 0): ?>
            <ul class="list-group">
                <?php foreach ($familyMembers as $member): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong><?= htmlspecialchars($member['username']) ?></strong> (<?= htmlspecialchars($member['email']) ?>)</span>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <button type="submit" name="remove_user" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">Aucun membre pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
