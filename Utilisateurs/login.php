<?php
session_start();
include '../BDD-Gestion/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = loginUser($_POST['email'], $_POST['password']);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id']; // Stocke uniquement l'ID
        $_SESSION['user_role'] = $user['role']; // Stocke le rôle récupéré

        addLog($_SESSION['user_id'], "Connexion réussie");
        
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Email ou mot de passe incorrect.";
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Connexion</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
        <a href="register.php">Pas encore inscrit ? Créez un compte</a>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
