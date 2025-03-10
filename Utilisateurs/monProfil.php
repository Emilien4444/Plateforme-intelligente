<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Mise à jour des informations utilisateur
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];

        if (updateUserProfile($userId, $username, $email)) {
            $message = "Profil mis à jour avec succès.";
        } else {
            $message = "Erreur lors de la mise à jour.";
        }
    } elseif (isset($_POST['change_password'])) {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];

        if (changeUserPassword($userId, $oldPassword, $newPassword)) {
            $message = "Mot de passe mis à jour avec succès.";
        } else {
            $message = "Erreur lors de la mise à jour du mot de passe.";
        }
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="profile-container">
    <h2>Mon Profil</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="profile-section">
        <h3>Informations personnelles</h3>
        <form method="POST">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <button type="submit" name="update_profile" class="btn-primary">Mettre à jour</button>
        </form>
    </div>

    <div class="profile-section">
        <h3>Changer de mot de passe</h3>
        <form method="POST">
            <label for="old_password">Ancien mot de passe :</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" id="new_password" name="new_password" required>

            <button type="submit" name="change_password" class="btn-primary">Changer le mot de passe</button>
        </form>
    </div>

    <div class="logout-section">
        <a href="logout.php" class="btn-danger">Se déconnecter</a>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
