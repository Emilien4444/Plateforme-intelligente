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

<div class="container py-5">
    <!-- Section de réinitialisation du mot de passe -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center text-primary mb-4">Réinitialisation du mot de passe</h2>

                <!-- Affichage du message de succès ou d'erreur -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info text-center"><?= $message ?></div>  
                <?php endif; ?>

                <!-- Formulaire de réinitialisation du mot de passe -->
                <form method="POST">
                    <!-- Le token de réinitialisation est inclus dans un champ caché -->
                    <input type="hidden" name="token" value="<?= $_GET['token'] ?>">  

                    <!-- Champ pour saisir le nouveau mdp -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Votre nouveau mot de passe" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Réinitialiser</button>
                </form>

                <!-- Lien vers la page de connexion si l'utilisateur a réussi la réinitialisation -->
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-outline-dark w-100">Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
