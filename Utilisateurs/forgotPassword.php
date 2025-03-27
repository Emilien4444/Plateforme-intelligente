<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Charge PHPMailer

include '../BDD-Gestion/config.php';

$message = "";
$alertType = "";

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Adresse email invalide.";
        $alertType = "danger";
    } else {
        // Vérifier si l'email existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $token = bin2hex(random_bytes(50)); // Génère un token sécurisé
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();

            // Configurer PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'emilienbouffart@gmail.com'; // Remplace par ton email
                $mail->Password = 'yaremtiqdoyiviiv'; // Remplace par ton mot de passe d'application Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Paramètres de l'email
                $mail->setFrom('tonemail@gmail.com', 'Plateforme Intelligente');
                $mail->addAddress($email);
                $mail->Subject = '🔑 Réinitialisation de votre mot de passe';
                $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/";
                $mail->Body = "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe : " . $baseUrl . "resetPassword.php?token=$token\n\nSi vous n'avez pas fait cette demande, ignorez cet email.";

                $mail->send();
                $message = "✅ Un email de récupération a été envoyé.";
                $alertType = "success";
            } catch (Exception $e) {
                $message = "❌ Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
                $alertType = "danger";
            }
        } else {
            $message = "❌ Email non trouvé.";
            $alertType = "danger";
        }
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="height: auto; margin-top: 50px;">
    <div class="card shadow-lg p-4" style="max-width: 450px; width: 100%;">
        <h2 class="text-center text-primary">🔒 Mot de passe oublié</h2>

        <!-- Affichage des messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $alertType ?> text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">📧 Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Entrez votre adresse email" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Envoyer</button>
        </form>

        <div class="mt-3 text-center">
            <a href="login.php" class="text-decoration-none">🔙 Retour à la connexion</a>
   
