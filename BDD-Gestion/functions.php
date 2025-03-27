<?php
include 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fonction pour vérifier si un utilisateur existe dans la base de données à partir de son adresse email
function userExists($email) {
    global $conn; // Utilisation de la connexion globale à la base de données
    // Requête SQL 
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    // On lie le param ? avec la var $email, en précisant que c'est une chaîne de caractères ("s")
    $stmt->bind_param("s", $email);
    $stmt->execute();
    // Stockage du résultat 
    $stmt->store_result();
    // Retourne true si au moins une ligne a été trouvée, sinon false
    return $stmt->num_rows > 0;
}


// Fonction pour enregistrer un nouvel utilisateur dans la base de données
function registerUser($username, $email, $password, $first_name, $last_name, $birthdate, $member_type) {
    global $conn; 
// Vérifie si l'email est déjà utilisé par un autre utilisateur
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
// Si l'email existe déjà, on retourne un message d'erreur
    if ($stmt->num_rows > 0) {
        return "Cet email est déjà utilisé.";
    }
// Hachage sécurisé du mot de passe avec BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
// Requête SQL pour insérer un nouvel utilisateur avec les colonnes spécifiées
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, birthdate, member_type, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $is_active = 0; // Par défaut, l'utilisateur est inactif
    $stmt->bind_param("sssssssi", $username, $email, $hashedPassword, $first_name, $last_name, $birthdate, $member_type, $is_active);
// Exécution de la requête 
    if ($stmt->execute()) {
        return true; // Succès de l'enregistrement
    } else {
        return "Erreur lors de l'inscription."; // En cas d'échec
    }
}


// Fonction pour connecter un utilisateur
function loginUser($email, $password) {
    global $conn; 
// Requête SQL pour récupérer les infos de l'utilisateur
    $stmt = $conn->prepare("SELECT id, username, password, member_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
// Récupère le résultat
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // Récupère les données sous forme de tableau associatif
// Vérifie si l'utilisateur existe et si le mot de passe fourni correspond au mot de passe haché en base
    if ($user && password_verify($password, $user['password'])) {
        return $user; // Authentification réussie
    }
    return false; //(email ou mot de passe incorrect)
}

// Fonction pour récupérer les informations d'un utilisateur à partir de son identifiant (ID)
function getUserById($userId) {
    global $conn; 
// Requête SQL pour récupérer les données de l'utilisateur correspondant à l'ID
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, gender, birthdate, member_type, profile_picture FROM users WHERE id = ?");
// Lie le paramètre $userId à la requête préparée en tant qu'entier
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    // Retourne les données de l'utilisateur sous forme de tableau associatif
    return $result->fetch_assoc();
}



// Fonction pour mettre à jour la photo de profil d'un utilisateur
function updateProfilePicture($userId, $fileName) {
    global $conn;
 // Debug pcq j'avais des pb : Affiche l'ID utilisateur et le nom du fichier
    echo "UserID: $userId, FileName: $fileName";
// Requête SQL pour mettre à jour la photo de profil
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $fileName, $userId); // "s" pour string, "i" pour integer
    $result = $stmt->execute();
// Affiche un message
    if ($result) {
        echo "Photo mise à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour.";
    }
    return $result;
}


// Fonction pour mettre à jour les informations du profil d'un utilisateur
function updateUserProfile($userId, $username, $email, $first_name, $last_name, $gender, $birthdate) {
    global $conn; // Connexion globale
// Requête SQL pour mettre à jour les champs de l'utilisateur
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, gender = ?, birthdate = ? WHERE id = ?");
// Liaison des paramètres (6 strings + 1 entier)
    $stmt->bind_param("ssssssi", $username, $email, $first_name, $last_name, $gender, $birthdate, $userId);
// Exécute la requête et retourne le résultat (true ou false)
    return $stmt->execute();
}



// Fonction pour changer le mot de passe d’un utilisateur
function changeUserPassword($userId, $oldPassword, $newPassword) {
    global $conn; // Connexion globale 
// Récupère le mot de passe actuel depuis la base
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

// Vérifie si l’ancien mot de passe saisi correspond à celui en base
    if (password_verify($oldPassword, $user['password'])) {
        // Hache le nouveau mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Met à jour le mot de passe dans la base
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        // Validation
        return $stmt->execute();
    } else {
        // L’ancien mot de passe ne correspond pas
        return false;
    }
}


// Fonction pour ajouter une action (log) dans la table des journaux (logs)
function addLog($userId, $action) {
    global $conn; // Connexion globale 

// Requête pour insérer un log avec l'identifiant de l'utilisateur et l'action effectuée
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    
// Liaison des paramètres : entre l'utilisateur user_id (entier), et laction (chaîne de caractères)
    $stmt->bind_param("is", $userId, $action);

// Exécute la requête et retourne true ou false 
    return $stmt->execute();
}



// Fonction pour ajouter un appareil (device) associé à un utilisateur
function addDevice($userId, $name, $type, $location) {
    global $conn; // Connexion globale

// Requête SQL pour insérer un nouvel appareil dans la table 'devices'
    $stmt = $conn->prepare("INSERT INTO devices (user_id, name, type, location) VALUES (?, ?, ?, ?)");

// Lie les paramètres à la requête : l'utilisateur user_id (entier), au infos de l'objet name/type/location (chaînes de caractères)
    $stmt->bind_param("isss", $userId, $name, $type, $location);

// Exécute la requête et retourne true  ou false 
    return $stmt->execute();
}

function getDevices($userId, $search = "", $status = "") {
    global $conn;
    $query = "SELECT * FROM devices WHERE user_id = ?";
    
    if (!empty($search)) {
        $query .= " AND name LIKE ?";
        $search = "%$search%";
    }
    if (!empty($status)) {
        $query .= " AND status = ?";
    }

    $stmt = $conn->prepare($query);

    if (!empty($search) && !empty($status)) {
        $stmt->bind_param("iss", $userId, $search, $status);
    } elseif (!empty($search)) {
        $stmt->bind_param("is", $userId, $search);
    } elseif (!empty($status)) {
        $stmt->bind_param("is", $userId, $status);
    } else {
        $stmt->bind_param("i", $userId);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function getDeviceById($deviceId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
    $stmt->bind_param("i", $deviceId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function updateDeviceStatus($deviceId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $deviceId);
    return $stmt->execute();
}
function deleteDevice($deviceId) {
    global $conn;

    $sql = "DELETE FROM devices WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deviceId);
    
    return $stmt->execute();  // Retourne true en cas de succès, false en cas d'échec
}
function addDeviceData($deviceId, $userId, $dataType, $value) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO device_data (device_id, user_id, data_type, value) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $deviceId, $userId, $dataType, $value);
    return $stmt->execute();
}
function getUserLevel($userId) {
    global $conn;  // Assurez-vous que la connexion à la base de données est déjà établie
    
    // Requête pour récupérer le niveau de l'utilisateur
    $sql = "SELECT level FROM users WHERE id = ?";
    
    // Préparer la requête
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);  // Le paramètre est de type entier (i) pour l'ID de l'utilisateur
    $stmt->execute();
    
    // Récupérer le résultat
    $result = $stmt->get_result();
    
    // Vérifier si un utilisateur est trouvé
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['level'];  // Retourner le niveau de l'utilisateur
    } else {
        return null;  // Retourne null si l'utilisateur n'existe pas
    }
}
function createFamily($userId) {
    global $conn;
    $familyId = $userId; // L'ID du chef de famille devient l'ID de la famille
    $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $familyId, $userId);
    return $stmt->execute();
}

function generateInvitation($complexeId, $email) {
    global $conn;

    // Vérifier si l'utilisateur invité existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $userId = $user['id'];
        $token = bin2hex(random_bytes(50));

        // Vérifier si le complexe a déjà une famille
        $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $complexeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $complexeData = $result->fetch_assoc();

        if (!$complexeData['family_id']) {
            // Si le complexe n'a pas encore de `family_id`, on lui donne son propre ID
            $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $complexeId, $complexeId);
            $stmt->execute();
        }

        // Stocker le token d'invitation
        $stmt = $conn->prepare("UPDATE users SET invitation_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $userId);
        $stmt->execute();

        return $token;
    }
    return false;
}
function sendInvitationEmail($email, $complexeId) {
    global $conn;

    $token = generateInvitation($complexeId, $email);
    if (!$token) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emilienbouffart@gmail.com';
        $mail->Password = 'yaremtiqdoyiviiv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('emilienbouffart@gmail.com', 'Plateforme Intelligente');
        $mail->addAddress($email);
        $mail->Subject = 'Invitation à rejoindre une famille';
        
        $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/";
        $invitationLink = $baseUrl . "accept_invite.php?token=$token";
        $mail->Body = "Vous avez été invité à rejoindre une famille. Cliquez ici pour accepter : $invitationLink";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
function removeUserFromFamily($complexeId, $userId) {
    global $conn;
    
    // Vérifier si l'utilisateur appartient bien à la famille
    $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();

    if (!$family) {
        echo "L'utilisateur n'existe pas !";
        return false;
    }

    if ($family['family_id'] == $complexeId) {
        echo "L'utilisateur appartient bien à cette famille.";
    }

    // Exécuter la mise à jour
    $stmt = $conn->prepare("UPDATE users SET family_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $userId);
        
    if ($stmt->execute()) {
        echo "Suppression réussie !";
        return true;
    } else {
        echo "Erreur SQL  : " . $stmt->error;
        return false;
    }
}
function getDeviceUsageStats($userId) {
    global $conn;

    $query = "
        SELECT 
            d.name, d.type, d.status, 
            COALESCE(SUM(dd.value), 0) AS consommation, 
            COALESCE(COUNT(dd.id), 0) AS temps_utilisation
        FROM devices d
        LEFT JOIN device_data dd ON d.id = dd.device_id
        WHERE d.user_id = ?
        GROUP BY d.id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function updateUserLevel($userId) {
    global $conn;

    // Récupérer le nombre de points de l'utilisateur
    $sql = "SELECT points FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $points = $result['points'];

    // Déterminer le niveau en fonction des points
    $level = 'beginner';  // Par défaut, débutant
    if ($points >= 0 && $points < 3) {
        $level = 'beginner';
    } elseif ($points >= 3 && $points < 5) {
        $level = 'intermediate';
    } elseif ($points >= 5 && $points < 7) {
        $level = 'advanced';
    } elseif ($points >= 7) {
        $level = 'expert';
    }

    // Mettre à jour le niveau de l'utilisateur
    $sqlLevel = "UPDATE users SET level = ? WHERE id = ?";
    $stmtLevel = $conn->prepare($sqlLevel);
    $stmtLevel->bind_param("si", $level, $userId);
    $stmtLevel->execute();
}
function updateUserPointsAndLevel($userId, $pointsAdded) {
    global $conn;

    // Ajouter des points à l'utilisateur
    $sql = "UPDATE users SET points = points + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $pointsAdded, $userId);
    $stmt->execute();

    // Mettre à jour le niveau en fonction des points
    $sqlPoints = "SELECT points FROM users WHERE id = ?";
    $stmtPoints = $conn->prepare($sqlPoints);
    $stmtPoints->bind_param("i", $userId);
    $stmtPoints->execute();
    $result = $stmtPoints->get_result()->fetch_assoc();
    $points = $result['points'];

    // Déterminer le niveau en fonction des points
    $level = 'beginner';
    if ($points >= 0 && $points < 3) {
        $level = 'beginner';
    } elseif ($points >= 3 && $points < 5) {
        $level = 'intermediate';
    } elseif ($points >= 5 && $points < 7) {
        $level = 'advanced';
    } elseif ($points >= 7) {
        $level = 'expert';
    }

    // Mettre à jour le niveau de l'utilisateur
    $sqlLevel = "UPDATE users SET level = ? WHERE id = ?";
    $stmtLevel = $conn->prepare($sqlLevel);
    $stmtLevel->bind_param("si", $level, $userId);
    $stmtLevel->execute();
}
function checkDatabaseIntegrity() {
    global $conn;

    // Vérification des clés étrangères
    $stmt = $conn->prepare("SELECT COUNT(*) FROM devices WHERE user_id NOT IN (SELECT id FROM users)");
    $stmt->execute();
    $result = $stmt->get_result();
    $invalidUsers = $result->fetch_assoc();

    if ($invalidUsers['COUNT(*)'] > 0) {
        echo "Il y a des appareils associés à des utilisateurs inexistants.";
    } else {
        echo "L'intégrité des données est vérifiée.";
    }
}

?>