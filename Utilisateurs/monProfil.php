<?php
session_start(); // Démarre une session 
include '../BDD-Gestion/config.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté

// Récupérer les informations de l'utilisateur à partir de la base de données
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $userId); // Lie l'ID de l'utilisateur à la requête
$stmt->execute(); 
$user = $stmt->get_result()->fetch_assoc(); // Récupère les données de l'utilisateur sous forme de tableau associatif

// Si l'utilisateur n'est pas trouvé dans la BDD -> arrête l'exécution
if (!$user) {
    die("Utilisateur introuvable.");
}


// Calcul de l'âge de l'utilisateur en fonction de sa date de naissance
$birthdate = $user['birthdate'];
$age = date_diff(date_create($birthdate), date_create('today'))->y;

// Gestion de la photo de profil
$profile_picture = (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture']))
    ? "uploads/" . $user['profile_picture']
    : "default-profile.png";


// Message de mise à jour du profil
$message = "";

// Traitement de la mise à jour du profil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    // Récupère les données du formulaire de maj du profil
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = trim($_POST['gender']);
    $birthdate = trim($_POST['birthdate']);

    // Requête pour maj les infos de l'utilisateur
    $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, gender = ?, birthdate = ? WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prépare la requête SQL
    $stmt->bind_param("ssssssi", $username, $email, $first_name, $last_name, $gender, $birthdate, $userId); // Lier les paramètres à la requête

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">Profil mis à jour avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Erreur lors de la mise à jour.</div>';
    }
}


// Gestion de l'upload de la photo de profil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    // Vérification des types de fichiers autorisés (JPG, JPEG, PNG)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']); // Vérifie le type MIME du fichier

    if (!in_array($fileType, $allowedTypes)) {
        $message = '<div class="alert alert-danger text-center">Seuls les formats JPG, JPEG et PNG sont acceptés.</div>';
    } else {
        // Génération d'un nom unique pour la photo de profil
        $fileName = "profile_" . $userId . "_" . time() . "." . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $uploadDir = __DIR__ . "/../Principale/uploads/"; // Dossier où les fichiers seront stockés
        $uploadPath = $uploadDir . $fileName;

        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Déplacer le fichier téléchargé vers le dossier
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
            // Maj la photo de profil dans la BDD
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $fileName, $userId);

            if ($stmt->execute()) {
                $profile_picture = 'uploads/' . $fileName;
                $message = '<div class="alert alert-success text-center">✅ Photo de profil mise à jour avec succès.</div>';
            } else {
                $message = '<div class="alert alert-danger text-center">❌ Erreur lors de la mise à jour de la photo de profil dans la base de données.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger text-center">❌ Erreur lors du téléchargement du fichier.</div>';
        }
    }
}

// Recherche des utilisateurs
$search = $_GET['search'] ?? '';  // Récupérer le terme de recherche
$searchQuery = "%" . $search . "%";

// Requête pour rechercher des utilisateurs par prénom, nom ou pseudonyme
$sql = "SELECT * FROM users WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Changer le mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier l'ancien mot de passe
    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            // Maj le mot de passe
            $hashedNewPassword = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedNewPassword, $userId);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success text-center">Mot de passe mis à jour avec succès.</div>';
            } else {
                $message = '<div class="alert alert-danger text-center">Erreur lors de la mise à jour du mot de passe.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger text-center">Les mots de passe ne correspondent pas.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger text-center">L\'ancien mot de passe est incorrect.</div>';
    }
}

// Récupérer les points et le niveau de l'utilisateur
$sqlUser = "SELECT points, level FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$userData = $stmtUser->get_result()->fetch_assoc();
$points = $userData['points'];
$level = $userData['level'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../Principale/header.php'; ?>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center text-primary">👤 Mon Profil</h2>
            <?php if (!empty($message)) echo $message; ?>

            <!-- Photo de profil -->
            <div class="text-center">
                <img src="<?= $profile_picture ?>" class="rounded-circle mb-3" width="150" height="150" alt="Photo de profil">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="profile_picture" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-secondary w-100">Changer de photo</button>
                </form>
            </div>
            
            <br><br>
            
            <!-- Informations de l'utilisateur -->
            <div class="card shadow p-3 mb-4">
                <div class="mb-3">
                    <p><strong>Niveau actuel :</strong> <?= ucfirst($level); ?></p>
                    <p><strong>Points accumulés :</strong> <?= $points; ?></p>
                </div>
                
                <div class="container mt-4">
                    <h3>Fonctionnalités accessibles</h3>

                    <?php if ($level == 'beginner'): ?>
                        <p>Accédez aux fonctionnalités de base.</p>
                    <?php endif; ?>

                    <?php if ($level == 'intermediate'): ?>
                        <p>Vous pouvez maintenant gérer vos objets.</p>
                    <?php endif; ?>

                    <?php if ($level == 'advanced'): ?>
                        <p>Vous pouvez maintenant gérer vos objets.</p>
                    <?php endif; ?>

                    <?php if ($level == 'expert'): ?>
                        <p>Vous avez accès à toutes les fonctionnalités, y compris la gestion des utilisateurs et des objets.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations publiques -->
            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Pseudonyme</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($birthdate) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Genre</label>
                    <select name="gender" class="form-select">
                        <option value="Homme" <?= ($user['gender'] == "Homme") ? 'selected' : ''; ?>>Homme</option>
                        <option value="Femme" <?= ($user['gender'] == "Femme") ? 'selected' : ''; ?>>Femme</option>
                        <option value="Autre" <?= ($user['gender'] == "Autre") ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type de membre</label>
                    <input type="text" name="member_type" class="form-control" value="<?= htmlspecialchars($user['member_type']) ?>" readonly>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary w-100">Mettre à jour</button>
            </form>

            <!-- Recherche des utilisateurs -->
            <div class="card shadow p-3 mb-4">
                <h3 class="card-title">Rechercher un utilisateur</h3>
                <form method="GET" class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Nom ou pseudonyme de l'utilisateur..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100">Rechercher</button>
                    </div>
                </form>
            </div>

            <!-- Afficher les utilisateurs trouvés -->
            <div class="card shadow p-3 mb-4">
                <h3 class="card-title">Utilisateurs trouvés</h3>
                <ul class="list-group">
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($user['username']) ?></strong> - <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?>
                        </div>
                    <a href="profile.php?id=<?= $user['id'] ?>" class="btn btn-info btn-sm">Voir Profil</a>
                    </li>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <li class="list-group-item text-center">Aucun utilisateur trouvé.</li>
                    <?php endif; ?>
                </ul>
            </div>
            

            <!-- Formulaire de changement de mot de passe -->
            <div class="card shadow p-3 mb-4">
                <h3 class="card-title">Changer mon mot de passe</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Ancien mot de passe</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-warning w-100">Changer le mot de passe</button>
                </form>
            </div>

            <div class="mt-4 text-center">
                <a href="logout.php" class="btn btn-danger w-100">🚪 Se déconnecter</a>
            </div>
        </div>
    </div>
    
    <?php include '../Principale/footer.php'; ?>

</body>
</html>
