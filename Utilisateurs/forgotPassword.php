<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Charge PHPMailer

include '../BDD-Gestion/config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
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
            $mail->Username = 'emilienbouffart@gmail.com'; 
            $mail->Password = 'yaremtiqdoyiviiv'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Paramètres de l'email
            $mail->setFrom('tonemail@gmail.com', 'Plateforme Intelligente');
            $mail->addAddress($email);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/";
            $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $baseUrl . "resetPassword.php?token=$token";

            $mail->send();
            $message = "Un email de récupération a été envoyé.";
        } catch (Exception $e) {
            $message = "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
        }
    } else {
        $message = "Email non trouvé.";
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Mot de passe oublié</h2>
    <?php if (!empty($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Votre email" required>
        <button type="submit">Envoyer</button>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
