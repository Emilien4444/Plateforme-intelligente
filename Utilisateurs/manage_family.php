<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'complexe') {
    header("Location: ../Principale/index.php");
    exit();
}

$familyId = $_SESSION['user_id'];
$familyMembers = $conn->query("SELECT * FROM users WHERE family_id = $familyId AND id != $familyId")->fetch_all(MYSQLI_ASSOC);

// Envoyer une invitation par email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invite_email'])) {
    $inviteEmail = $_POST['email'];
    sendInvitationEmail($inviteEmail, $userId);
    header("Location: manage_family.php?message=Invitation envoyée !");
    exit();
}

// Supprimer un membre de la famille
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_user'])) {
    $removeUserId = $_POST['user_id']; // Correction : Nom de la variable
    if (removeUserFromFamily($_SESSION['user_id'], $removeUserId)) { // Correction : Passer le bon ID
        header("Location: manage_family.php?message=Membre supprimé !");
    } else {
        header("Location: manage_family.php?message=Erreur lors de la suppression !");
    }
    exit();
}


?>

<?php include '../Principale/header.php'; ?>
<div class="container">
    <h2>Gestion des membres de la famille</h2>

    <h3>Inviter un membre :</h3>
    <form method="POST">
        <input type="email" name="email" placeholder="Email de l'utilisateur" required>
        <button type="submit" name="invite_email">Envoyer une invitation</button>
    </form>

    <h3>Membres actuels :</h3>
    <ul>
        <?php foreach ($familyMembers as $member): ?>
            <li>
                <?= htmlspecialchars($member['username']) ?> 
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                    <button type="submit" name="remove_user" style="background-color:red;">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php include '../Principale/footer.php'; ?>
