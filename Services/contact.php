<?php
session_start();
include '../BDD-Gestion/functions.php';

// Variables de message
$messageSent = false;
$errorMessage = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $messageContent = htmlspecialchars($_POST['message']);

    // Appeler la fonction pour envoyer l'email
    $emailSent = sendContactEmail($name, $email, $messageContent);

    // Message de confirmation ou d'erreur
    if ($emailSent) {
        $messageSent = true;
    } else {
        $errorMessage = "Une erreur est survenue lors de l'envoi du message.";
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Contactez-nous</h2>

            <!-- Affichage du message de confirmation uniquement si le formulaire a été soumis -->
            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <?php if ($messageSent): ?>
                    <div class="alert alert-success text-center">Message envoyé avec succès !</div>
                <?php elseif ($errorMessage): ?>
                    <div class="alert alert-danger text-center"><?= $errorMessage ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" class="bg-light p-4 rounded shadow">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="Votre nom" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Votre email" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Votre message</label>
                    <textarea class="form-control" name="message" id="message" rows="4" placeholder="Tapez votre message ici..." required></textarea>
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100">Envoyer</button>
            </form>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
