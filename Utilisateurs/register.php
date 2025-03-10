<?php
include '../BDD-Gestion/functions.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = registerUser($_POST['username'], $_POST['email'], $_POST['password']);
    
    if ($result === true) {
        header("Location: login.php");
        exit();
    } else {
        $error = $result;
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Inscription</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
        <a href="login.php">Déjà un compte ? Connectez-vous</a>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
