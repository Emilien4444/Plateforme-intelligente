<?php
session_start(); // Démarre la session PHP
include '../BDD-Gestion/functions.php'; // Inclut les fonctions nécessaires

$message = ""; // Variable pour afficher les messages

// Vérification si le token est passé dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token']; // Récupère le token de l'URL

    // Appel de la fonction pour vérifier si le token existe et est valide
    $userId = getUserIdByToken($token);

    if ($userId) {
        // Si l'utilisateur est trouvé avec ce token, on met à jour is_verified à 1
        if (verifyUserEmail($userId)) {
            // Message de confirmation
            $message = '<div class="alert alert-success text-center">Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.</div>';
        } else {
            // Message d'erreur
            $message = '<div class="alert alert-danger text-center">Erreur lors de la vérification de l\'email. Veuillez réessayer.</div>';
        }
    } else {
        // Si aucun utilisateur n'a ce token
        $message = '<div class="alert alert-danger text-center">Token invalide ou expiré.</div>';
    }
} else {
    // Si le token n'est pas fourni dans l'URL
    $message = '<div class="alert alert-danger text-center">Aucun token de vérification trouvé.</div>';
}

?>

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <!-- Affichage du message -->
    <?php echo $message; ?>

    <div class="text-center mt-4">
        <a href="login.php" class="btn btn-primary">Se connecter</a>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
