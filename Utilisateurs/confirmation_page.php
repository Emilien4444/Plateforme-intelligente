<?php
require '../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51R08HoQUvf2ISyc1USkbTf3vPLXjeeEJbdpIWmL5W53zieW5BAyibICaKE6shRU7RSdnhTUDIHTKImDj2UNsQ0sE00kj048HHM');

// Récupérer l'ID du PaymentIntent depuis l'URL
$paymentIntentId = $_GET['payment_intent'];

// Récupérer le PaymentIntent
try {
    $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

    if ($paymentIntent->status == 'succeeded') {
        // Le paiement a réussi, vous pouvez mettre à jour l'utilisateur au niveau "expert"
        echo "Le paiement a réussi et l'utilisateur est promu au niveau expert.";
    } else {
        echo "Le paiement n'a pas été effectué avec succès.";
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "Erreur lors de la récupération du PaymentIntent : " . $e->getMessage();
}
?>
