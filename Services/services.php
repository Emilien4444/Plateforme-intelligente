<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <h1 class="text-center mb-4">Nos Services</h1>
    <p class="text-center lead">Bienvenue sur notre plateforme intelligente ! Découvrez nos fonctionnalités innovantes.</p>

    <div class="row g-4">
        <!-- Authentification et Sécurité -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">🔐 Authentification et Sécurité</h2>
                    <p class="card-text">Connexion et inscription sécurisées avec gestion des mots de passe. Possibilité de réinitialiser son mot de passe par email.</p>
                    <p class="card-text">L'authentification à deux facteurs (2FA) peut être ajoutée pour plus de sécurité.</p>
                </div>
            </div>
        </div>

        <!-- Gestion des Objets Connectés -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">📡 Gestion des Objets Connectés</h2>
                    <ul class="list-unstyled">
                        <li>📌 Ajouter tous vos objets connectés</li>
                        <li>💡 Allumer/éteindre à distance</li>
                        <li>📊 Affichage de la consommation énergétique</li>
                        <li>📝 Historique des actions</li>
                        <li>⚡ Message d'avertissement pour température trop élevé</li> 
                        <li>⚡ Suivie de batterie </li> 
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Système d’Accès Premium -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">💳 Système d’Accès Premium</h2>
                    <p>Possibilité de souscrire à un compte <strong>ePlateform+</strong> pour accéder plus rapidement aux fonctionnalités et accélérer la progression dans le système de points. </p>
                </div>
            </div>
        </div>

        <!-- Gestion des Familles -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">🏠 Gestion des Familles</h2>
                    <p>Possibilité de créer une famille et d’inviter des utilisateurs :</p>
                    <ul class="list-unstyled">
                        <li>📩 Invitation d'un membre par email</li>
                        <li>🚪 Suppression d’un membre de la famille</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Gestion des Rôles et Permissions -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">👥 Gestion des Rôles et Permissions</h2>
                    <p>Différents types d’utilisateurs avec des permissions spécifiques :</p>
                    <ul class="list-unstyled">
                        <li>👤 <strong>Visiteur :</strong> Peut voir un échantillon d'objet capable de se connécter avec notre application </li>
                        <li>👤 <strong>Simple :</strong> Peut nous contacter et peut voir les objets connécté si il est dans la famille d'un utilisateur expert</li>
                        <li>⚙️ <strong>intermédiaire :</strong> Peut ajouter, et visualiser les détails des objets</li>
                        <li>⚙️ <strong>advanced :</strong> Peut voir le rapport détailler des objets (consommation, tempéarture actuel..) et les supprimer </li>
                        <li>🌟 <strong>Expert :</strong> Accède à toutes les fonctionnalités, y compris la gestion des utilisateurs, des objets et des familles </li> 
                    </ul>
                </div>
            </div>
        </div>

        <!-- Notifications et Améliorations Futures -->
        <div class="col-md-6">
            <div class="card bg-light shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">🔔 Notifications et Améliorations Futures</h2>
                    <ul class="list-unstyled">
                        <li>✅ Ajout de notifications pour suivre l’état des objets</li>
                        <li>✅ Intégration de vrai objets pour suivr leur onsommation en temps réel</li>
                        <li>📆 Planification des actions sur les objets connectés</li>
                        <li>🛠️ Mise en place de mises à jour automatiques pour améliorer l'expérience utilisateur</li> 
                        <li>💬 Intégration de chat en direct ou de support via chatbot</li> 
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>
