<?php
// Démarrage de la session 
session_start();

// Vérification si l'utilisateur est connecté en vérifiant la présence de 'user_id' dans la session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté -> on le redirige vers la page de connexion
    exit(); 
}
?>

<?php include '../Principale/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Déclaration du charset et de la meta description pour l'adaptation mobile -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement ePlatform+</title>
    <!-- Inclusion de la bibliothèque Bootstrap pour la mise en page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclusion de Stripe.js pour l'intégration de la solution de paiement Stripe -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center">💳 Abonnement ePlatform+</h2>
            <p class="text-center">Souscrivez à un abonnement mensuel pour accélérer votre progression et obtenir des points plus rapidement.</p>
            
            <!-- Formulaire de paiement -->
            <form id="payment-form">
                <div class="mb-3">
                    <label for="card-element" class="form-label">Carte de crédit</label>
                    <!-- Elément Stripe où la carte de crédit sera insérée -->
                    <div id="card-element">
                        <!-- Un élément Stripe sera inséré ici -->
                    </div>
                    <!-- Affichage des erreurs de paiement, si applicable -->
                    <div id="card-errors" role="alert"></div>
                </div>

                <button type="submit" id="submit" class="btn btn-primary w-100">Payer 5€ par mois</button>
            </form>
        </div>
    </div>

    <script>
    // Initialisation de Stripe avec la clé publique
    const stripe = Stripe('pk_test_51R08HoQUvf2ISyc1uCAKZVDyFjiPHLGN4HMWxtiL8Jzjvk00CTB2NSZrAliWYxNsgcc7ZdPoTT0Wq6hrohzACLyR00sowIEywb'); // Clé publique Stripe
    const elements = stripe.elements(); // Initialisation de Stripe Elements

    // Création de l'élément de carte de crédit Stripe
    const card = elements.create('card');
    card.mount('#card-element'); // Montre cet élément dans la div 'card-element'

    // Gestion des erreurs liées à la carte -> carte est invalide
    card.addEventListener('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message; // Affiche le message d'erreur
        } else {
            displayError.textContent = ''; // Efface les erreurs si tout est ok
        }
    });

    // Gestion de la soumission du formulaire de paiement
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Empêche le comportement par défaut de soumettre le formulaire

        // Création du PaymentMethod avec Stripe, en utilisant l'élément de carte
        stripe.createPaymentMethod({
            type: 'card',
            card: card,
        }).then(function(result) {
            if (result.error) {
                // Si une erreur se produit lors de la création du PaymentMethod -> affiche l'erreur
                const displayError = document.getElementById('card-errors');
                displayError.textContent = result.error.message;
            } else {
                // Si le PaymentMethod est créé sans erreur
                const paymentMethodId = result.paymentMethod.id;

                // Création d'un champ caché pour inclure l'ID du PaymentMethod dans le formulaire
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_method_id');
                hiddenInput.setAttribute('value', paymentMethodId);
                form.appendChild(hiddenInput);
                
                // Ajout de l'ID utilisateur à transmettre au serveur pour savoir quel utilisateur effectue le paiement
                const userIdInput = document.createElement('input');
                userIdInput.setAttribute('type', 'hidden');
                userIdInput.setAttribute('name', 'user_id');
                userIdInput.setAttribute('value', '<?php echo $_SESSION["user_id"]; ?>'); // Utilisation de la session pour récupérer l'ID utilisateur
                form.appendChild(userIdInput);

                // Soumettre le formulaire après avoir ajouté les inputs cachés
                return fetch('process_payment.php', {
                    method: 'POST',
                    body: new FormData(form), // Envoi du formulaire avec FormData
                });
            }
        }).then(function(response) {
            return response.json(); // Conversion de la réponse du serveur en JSON
        }).then(function(data) {
            // Si le paiement est réussi -> affiche un message et rediriger
            if (data.success) {
                alert(data.message);  // Affichage du message de succès
                window.location.href = 'confirmation_page.php'; 
            } else {
                alert(data.message);  // Affichage du message d'erreur
            }
        }).catch(function(error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors du paiement');
        });
    });
    </script>

</body>
</html>
