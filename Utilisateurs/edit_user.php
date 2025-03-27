<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

if ($userLevel != 'expert') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $userIdToEdit = $_GET['id'];

    // Récupérer les informations de l'utilisateur à modifier
    $sql = "SELECT id, username, email, level, points FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userIdToEdit);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Vérification si l'utilisateur existe
    if (!$user) {
        die("Utilisateur non trouvé.");
    }

    // Modifier les informations de l'utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $level = $_POST['level'];
        $points = $_POST['points'];

        // Préparer et exécuter la requête de mise à jour
        $sqlUpdate = "UPDATE users SET username = ?, email = ?, level = ?, points = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssssi", $username, $email, $level, $points, $userIdToEdit);
        $stmtUpdate->execute();

        header("Location: manage_user.php");
        exit();
    }
} else {
    die("ID utilisateur non spécifié.");
}

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Modifier l'utilisateur</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="level">Niveau</label>
            <select name="level" class="form-control" required>
                <option value="basic" <?= $user['level'] == 'basic' ? 'selected' : '' ?>>De base</option>
                <option value="advanced" <?= $user['level'] == 'advanced' ? 'selected' : '' ?>>Avancé</option>
                <option value="expert" <?= $user['level'] == 'expert' ? 'selected' : '' ?>>Expert</option>
            </select>
        </div>
        <div class="form-group">
            <label for="points">Points</label>
            <input type="number" name="points" class="form-control" value="<?= htmlspecialchars($user['points']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>
