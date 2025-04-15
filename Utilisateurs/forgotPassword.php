<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Charger l'autoloader de Composer pour PHPMailer

include '../BDD-Gestion/config.php'; // Inclut la configuration de la BDD

$message = ""; // Variable pour stocker les messages à afficher
$alertType = ""; // Type d'alerte (succès ou erreur)

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage de l'email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Vérification si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide."; // Message d'erreur si email invalide
        $alertType = "danger"; // Type d'alerte pour email invalide
    } else {
        // Vérification si l'email existe dans la BDD
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); // Lier l'email à la requête
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) { // Si l'email existe dans la BDD
            // Générer un token sécurisé pour la réinitialisation du mot de passe
            $token = bin2hex(random_bytes(50));
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();

            // Configuration de PHPMailer pour envoyer l'email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Serveur SMTP pour Gmail
                $mail->SMTPAuth = true;
                $mail->Username = 'emilienbouffart@gmail.com'; // Remplacer par votre email
                $mail->Password = 'yaremtiqdoyiviiv'; // Remplacer par votre mot de passe d'application Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Paramètres de l'email
                $mail->setFrom('emilienbouffart@gmail.com', 'Plateforme Intelligente'); // L'email de l'expéditeur
                $mail->addAddress($email); // L'email du destinataire
                $mail->Subject = 'Réinitialisation de votre mot de passe'; // Sujet de l'email
                $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/"; // L'URL de base pour la réinitialisation
                $mail->Body = "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe : " . $baseUrl . "resetPassword.php?token=$token\n\nSi vous n'avez pas fait cette demande, ignorez cet email."; // Corps de l'email

                // Envoi de l'email
                $mail->send();
                $message = "Un email de récupération a été envoyé."; // Message de succès
                $alertType = "success"; // Type d'alerte pour l'email envoyé
            } catch (Exception $e) {
                // En cas d'erreur lors de l'envoi de l'email -> afficher l'erreur
                $message = "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
                $alertType = "danger"; // Type d'alerte pour l'erreur
            }
        } else {
            $message = "Email non trouvé."; // Si l'email n'existe pas dans la BDD
            $alertType = "danger"; // Type d'alerte pour l'email non trouvé
        }
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $alertType; ?> text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Formulaire de récupération de mot de passe -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center mb-4">Réinitialisation du mot de passe</h2>

            <form method="POST" class="bg-light p-4 rounded shadow">
                <div class="mb-3">
                    <label for="email" class="form-label">📧 Entrez votre email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Votre email" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Envoyer le lien de réinitialisation</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
