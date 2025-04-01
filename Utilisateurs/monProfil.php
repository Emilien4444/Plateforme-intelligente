<?php
include '../BDD-Gestion/functions.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $newPassword, $token);
    
    if ($stmt->execute()) {
        $message = "Mot de passe réinitialisé avec succès. <a href='login.php'>Connectez-vous</a>";
    } else {
        $message = "Token invalide ou expiré.";
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Réinitialisation du mot de passe</h2>
    <?php if (!empty($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?>">
        <input type="password" name="password" placeholder="Nouveau mot de passe" required>
        <button type="submit">Réinitialiser</button>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
