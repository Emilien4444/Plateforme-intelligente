<?php
include '../BDD-Gestion/functions.php'; 

$message = "";  // Variable pour stocker les messages d'erreur ou de succès

// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère le token et le mdp depuis le formulaire
    $token = $_POST['token'];  // Le token est passé dans le formulaire en tant que champ caché
    $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hachage du nouveau mdp avec bcrypt

    // Prépare la requête SQL pour maj le mdp de l'utilisateur
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $newPassword, $token);  // Lie les paramètres à la requête SQL

    // Exécute la requête SQL
    if ($stmt->execute()) {
        // Si la maj est réussie -> affiche un message de succès avec un lien pour se connecter
        $message = "Mot de passe réinitialisé avec succès. <a href='login.php'>Connectez-vous</a>";
    } else {
        // Si le token est invalide ou expiré -> un message d'erreur est affiché
        $message = "Token invalide ou expiré.";
    }
}
?>

<?php include '../Principale/header.php'; ?>  

<div class="form-container">  
    <h2>Réinitialisation du mot de passe</h2>

    <!-- Affichage du message de succès ou d'erreur -->
    <?php if (!empty($message)): ?>
        <p><?= $message ?></p>  
    <?php endif; ?>

    <!-- Formulaire de réinitialisation du mot de passe -->
    <form method="POST">
        <!-- Le token de réinitialisation est inclus dans un champ caché -->
        <input type="hidden" name="token" value="<?= $_GET['token'] ?>">  
        
        <!-- Champ pour saisir le nouveau mdp -->
        <input type="password" name="password" placeholder="Nouveau mot de passe" required>  
        
        <button type="submit">Réinitialiser</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?> 
