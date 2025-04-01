<?php
use PHPMailer\PHPMailer\PHPMailer; // Utilisation de PHPMailer pour envoyer des emails
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Charge l'autoloader de Composer pour PHPMailer

include '../BDD-Gestion/config.php';

$message = ""; // Variable pour stocker le message à afficher à l'utilisateur
$alertType = ""; // Variable pour définir le type d'alerte 

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage de l'email fourni par l'utilisateur
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
            $token = bin2hex(random_bytes(50)); // Génère un token sécurisé pour la réinitialisation du mdp
            $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->bind_param("ss", $token, $email); // Maj la BDD avec le token
            $stmt->execute();

            // Configuration de PHPMailer pour envoyer l'email de réinitialisation
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Serveur SMTP pour Gmail
                $mail->SMTPAuth = true;
                $mail->Username = 'emilienbouffart@gmail.com'; // L'adresse email de l'expéditeur
                $mail->Password = 'yaremtiqdoyiviiv'; // Mot de passe de l'application Gmail ( peronnel pas toucher ! )
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Paramètres de l'email
                $mail->setFrom('tonemail@gmail.com', 'Plateforme Intelligente'); // L'email de l'expéditeur
                $mail->addAddress($email); // L'email du destinataire
                $mail->Subject = ' Réinitialisation de votre mot de passe'; // Sujet de l'email
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
