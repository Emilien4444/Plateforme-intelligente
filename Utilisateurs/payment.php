<?php
require '../vendor/autoload.php';
include '../BDD-Gestion/config.php';
session_start();

\Stripe\Stripe::setApiKey('sk_test_51R08HoQUvf2ISyc1USkbTf3vPLXjeeEJbdpIWmL5W53zieW5BAyibICaKE6shRU7RSdnhTUDIHTKImDj2UNsQ0sE00kj048HHM'); // Remplace avec ta clé API Stripe secrète

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'eur',
            'product_data' => ['name' => 'Abonnement Complexe'],
            'unit_amount' => 500, // Prix en centimes (5.00€)
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/Plateforme_Intelligente/Utilisateurs/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/Plateforme_Intelligente/Utilisateurs/dashboard.php',
]);

header("Location: " . $checkout_session->url);
