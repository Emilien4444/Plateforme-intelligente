<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirige l'utilisateur 
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Récupère le niveau d'accès de l'utilisateur

// Si l'utilisateur n'est pas un expert, rediriger vers la page principale
if ($userLevel != 'expert') {
    header("Location: index.php"); // Redirige vers la page principale si l'utilisateur n'a pas le niveau d'accès "expert"
    exit();
}

// Vérifier si l'ID de l'utilisateur à modifier est passé en paramètre dans l'URL
if (isset($_GET['id'])) {
    $userIdToChange = $_GET['id']; // Récupère l'ID de l'utilisateur à modifier

    // Récupérer les informations actuelles de l'utilisateur à partir de la BDD
    $sql = "SELECT id, username, email, level FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("i", $userIdToChange); // Lie l'ID de l'utilisateur à la requête
    $stmt->execute(); 
    $user = $stmt->get_result()->fetch_assoc(); // Récupère les résultats sous forme de tableau associatif

    // Si l'utilisateur n'existe pas dans la BDD
    if (!$user) {
        die("Utilisateur non trouvé."); // Arrête le script et affiche un message d'erreur
    }

    // Vérifier si le formulaire a été soumis pour modifier le rôle de l'utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $level = $_POST['level']; // Récupère le niveau sélectionné dans le formulaire

        // Maj du rôle de l'utilisateur dans la BDD
        $sqlUpdate = "UPDATE users SET level = ? WHERE id = ?"; // Requête pour mettre à jour le niveau de l'utilisateur
        $stmtUpdate = $conn->prepare($sqlUpdate); 
        $stmtUpdate->bind_param("si", $level, $userIdToChange); // Lie les paramètres (niveau et ID utilisateur)
        $stmtUpdate->execute(); 

        header("Location: adminPanel.php"); // Redirige vers la page de gestion des utilisateurs après la mise à jour
        exit();
    }
} else {
    die("ID utilisateur non spécifié."); // Si l'ID de l'utilisateur n'est pas passé dans l'URL, arrête le script
}
?>


<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Modifier le rôle de l'utilisateur</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
        </div>
        <div class="form-group">
            <label for="level">Niveau</label>
            <select name="level" class="form-control" required>
                <option value="beginner" <?= $user['level'] == 'beginner' ? 'selected' : '' ?>>Simple</option>
                <option value="intermediate" <?= $user['level'] == 'intermediate' ? 'selected' : '' ?>>Intermédiaire</option>
                <option value="advanced" <?= $user['level'] == 'advanced' ? 'selected' : '' ?>>Avancé</option>
                <option value="expert" <?= $user['level'] == 'expert' ? 'selected' : '' ?>>Expert</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Changer le rôle</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>
