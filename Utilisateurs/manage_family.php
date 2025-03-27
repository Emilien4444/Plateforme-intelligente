<?php
session_start();
include '../BDD-Gestion/functions.php';

$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Arrêter l'exécution du code si l'utilisateur n'a pas accès
}

$familyId = $_SESSION['user_id']; // ID du chef de famille
$message = "";

// Récupérer les membres de la famille
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE family_id = ? AND id != ?");
$stmt->bind_param("ii", $familyId, $familyId);
$stmt->execute();
$familyMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Envoyer une invitation par email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invite_email'])) {
    $inviteEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($inviteEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        sendInvitationEmail($inviteEmail, $familyId);
        $message = "Invitation envoyée à $inviteEmail !";
    }
}

// Supprimer un membre de la famille
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_user'])) {
    $removeUserId = $_POST['user_id'];

    if (removeUserFromFamily($familyId, $removeUserId)) {
        $message = "Membre supprimé avec succès !";
    } else {
        $message = "Erreur lors de la suppression.";
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
