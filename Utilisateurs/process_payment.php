<?php
require '../vendor/autoload.php';  // Inclut le fichier autoload.php généré par Composer pour charger Stripe SDK

// Définir la clé secrète de Stripe (clé test ici pour l'environnement de développement)
\Stripe\Stripe::setApiKey('sk_test_51R08HoQUvf2ISyc1USkbTf3vPLXjeeEJbdpIWmL5W53zieW5BAyibICaKE6shRU7RSdnhTUDIHTKImDj2UNsQ0sE00kj048HHM'); 
// La clé secrète permet à votre serveur de communiquer avec l'API Stripe pour effectuer des paiements. Elle est privée et ne doit jamais être exposée au public.

$paymentMethodId = $_POST['payment_method_id'];  // Récupère l'ID du PaymentMethod soumis depuis le formulaire
$userId = $_POST['user_id'];  // L'ID de l'utilisateur à promouvoir au niveau "expert"

// Créer un PaymentIntent pour effectuer le paiement
try {
    // Créer un PaymentIntent avec Stripe
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => 500,  // Montant en centimes (5€)
        'currency' => 'eur',
        'payment_method' => $paymentMethodId,
        'confirm' => true, // Confirme immédiatement le paiement dès la création du PaymentIntent.
        'automatic_payment_methods' => ['enabled' => true],  // Activer les méthodes automatiques
        'return_url' => 'http://localhost/Plateforme_Intelligente/Principale/confirmation_page.php', // Redirige vers une page de validation
    ]);

    // Si le paiement a réussi -> effectuer la promotion
    if ($paymentIntent->status == 'succeeded') {
        if (setUserToExpert($userId)) {
            echo json_encode(["success" => true, "message" => "L'utilisateur a été promu au niveau expert."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la mise à jour du niveau de l'utilisateur."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Le paiement a échoué."]);
    }
    // Si une erreur générale se produit, elle est capturée et un message d'erreur générique est renvoyé.
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(["success" => false, "message" => 'Erreur Stripe : ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => 'Erreur générale : ' . $e->getMessage()]);
}

// Ici le paiement fonctionne -> je recois l'argent sur com compte Stripe mais un message d'erreur s'affiche car Stripe exige qu'on utilisie HTTPS en production cependant
// Wamp ne permet pas une tel sécurité ainsi il suffirait de passer sur serveur local sécurisé comme ngrok pour exposer notre serveur local via HTTPS.