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

// Fonction pour récupérer les appareils d'un utilisateur avec recherche et filtre sur le statut
function getDevices($userId, $search = "", $status = "") {
    global $conn; // Connexion globale

    // Requête SQL de base
    $query = "SELECT * FROM devices WHERE user_id = ?";

    // Si un terme de recherche est fourni, ajoute un filtre sur le nom
    if (!empty($search)) {
        $query .= " AND name LIKE ?";
        $search = "%$search%"; // Prépare la recherche avec des caractères génériques
    }

    // Si un statut est fourni, ajoute un filtre sur le statut
    if (!empty($status)) {
        $query .= " AND status = ?";
    }

    // Requête SQL
    $stmt = $conn->prepare($query);

    // Lier les paramètres en fonction de la présence des variables search et status
    if (!empty($search) && !empty($status)) {
        // Si les deux filtres sont présents, lier 3 paramètres : user_id, search, status
        $stmt->bind_param("iss", $userId, $search, $status);
    } elseif (!empty($search)) {
        // Si seulement search est présent, lier 2 paramètres : user_id, search
        $stmt->bind_param("is", $userId, $search);
    } elseif (!empty($status)) {
        // Si seulement status est présent, lier 2 paramètres : user_id, status
        $stmt->bind_param("is", $userId, $status);
    } else {
        // Si aucun filtre n'est présent, lier uniquement user_id
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();

    // Récupérer et retourner tous les résultats sous forme de tableau associatif
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Fonction pour récupérer un appareil spécifique à partir de son ID
function getDeviceById($deviceId) {
    global $conn; // Connexion globale 

    // Requête SQL pour récupérer les informations d'un appareil avec l'ID donné
    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
    
    // Lie l'ID de l'appareil (entier) au paramètre de la requête SQL
    $stmt->bind_param("i", $deviceId);
    $stmt->execute();

    // Récupère le résultat de la requête et retourne les données sous forme de tableau associatif
    return $stmt->get_result()->fetch_assoc();
}


// Fonction pour mettre à jour le statut d'un appareil en fonction de son ID
function updateDeviceStatus($deviceId, $status) {
    global $conn; // Connexion globale

    // Prépare la requête SQL pour mettre à jour le statut de l'appareil avec l'ID donné
    $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
    
    // Lie les paramètres status de l'objet (chaîne de caractères) et deviceId (entier)
    $stmt->bind_param("si", $status, $deviceId);
    return $stmt->execute();
}


// Fonction pour supprimer un appareil de la base de données en fonction de son ID
function deleteDevice($deviceId) {
    global $conn; // Utilise la connexion globale à la base de données

    // Requête SQL pour supprimer un appareil avec l'ID donné
    $sql = "DELETE FROM devices WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Lier l'ID de l'appareil à la requête préparée 
    $stmt->bind_param("i", $deviceId);
    return $stmt->execute();
}


// Fonction pour ajouter des données associées à un appareil
function addDeviceData($deviceId, $userId, $dataType, $value) {
    global $conn; // Connexion globale

    // Requête SQL pour insérer de nouvelles données dans la table 'device_data'
    $stmt = $conn->prepare("INSERT INTO device_data (device_id, user_id, data_type, value) VALUES (?, ?, ?, ?)");
    
    // Lier les paramètres à la requête 
    // "i" pour entier (device_id et user_id), "s" pour chaîne de caractères (data_type et value)
    $stmt->bind_param("iiss", $deviceId, $userId, $dataType, $value);
    return $stmt->execute();
}


// Fonction pour récupérer le niveau d'un utilisateur à partir de son ID
function getUserLevel($userId) {
    global $conn;  
    
    // Requête SQL pour récupérer le niveau de l'utilisateur
    $sql = "SELECT level FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Lier l'ID de l'utilisateur à la requête préparée (paramètre de type entier "i")
    $stmt->bind_param("i", $userId);  
    $stmt->execute();
    
    // Récupérer le résultat 
    $result = $stmt->get_result();
    
    // Vérifier si l'utilisateur a été trouvé dans la base de données
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();  // Récupérer le premier résultat sous forme de tableau associatif
        return $user['level'];  // Retourner le niveau de l'utilisateur
    } else {
        return null;  // Retourner null si l'utilisateur n'existe pas
    }
}



// Fonction pour créer une famille, l'utilisateur devient le chef de famille
function createFamily($userId) {
    global $conn;      
    // L'ID du chef de famille devient l'ID de la famille
    $familyId = $userId; 

    // Requête SQL pour mettre à jour l'ID de la famille pour cet utilisateur
    $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
    
    // Lier les paramètres : family_id et user_id tous deux  entier
    $stmt->bind_param("ii", $familyId, $userId);
    return $stmt->execute();
}


// Fonction pour générer une invitation pour un utilisateur et associer un complexe à une famille si nécessaire
function generateInvitation($complexeId, $email) {
    global $conn;  

    // Vérifier si l'utilisateur invité existe en recherchant par son email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si l'utilisateur existe, procéder à l'invitation
    if ($user = $result->fetch_assoc()) {
        $userId = $user['id'];  // Récupère l'ID de l'utilisateur
        $token = bin2hex(random_bytes(50));  // Génère un token d'invitation sécurisé

        // Vérifier si l'utilisateur a déjà une famille (en vérifiant s'il a un `family_id`)
        $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $complexeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $complexeData = $result->fetch_assoc();

        // Si le complexe n'a pas encore de `family_id`, on lui attribue son propre ID comme famille
        if (!$complexeData['family_id']) {
            $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $complexeId, $complexeId);
            $stmt->execute();
        }

        // Stocker le token d'invitation pour l'utilisateur invité
        $stmt = $conn->prepare("UPDATE users SET invitation_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $userId);
        $stmt->execute();

        // Retourner le token d'invitation généré
        return $token;
    }

    // Retourner false si l'utilisateur n'a pas été trouvé
    return false;
}


// Fonction pour envoyer un email d'invitation à un utilisateur pour rejoindre une famille
function sendInvitationEmail($email, $complexeId) {
    global $conn;  

    // Générer un token d'invitation
    $token = generateInvitation($complexeId, $email);
    
    // Si le token n'est pas généré (c'est-à-dire si l'utilisateur n'existe pas), retourner false
    if (!$token) return false;

    // Créer une instance de PHPMailer pour envoyer l'email
    $mail = new PHPMailer(true);
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Hôte SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'emilienbouffart@gmail.com';  // Mon adresse email
        $mail->Password = 'yaremtiqdoyiviiv';  // Mon mot de passe fais via mail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Sécurisation de la connexion
        $mail->Port = 587;  // Port SMTP pour Gmail

        // Paramètres de l'email
        $mail->setFrom('emilienbouffart@gmail.com', 'Plateforme Intelligente');
        $mail->addAddress($email); 
        $mail->Subject = 'Invitation à rejoindre une famille';  // Sujet de l'email
        
        // Lien d'invitation
        $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/";  // URL de base pour l'invitation
        $invitationLink = $baseUrl . "accept_invite.php?token=$token";  // Lien d'invitation avec le token généré
        
        // Corps de l'email avec le lien d'invitation
        $mail->Body = "Vous avez été invité à rejoindre une famille. Cliquez ici pour accepter : $invitationLink";

        // Envoyer l'email
        $mail->send();
        return true;  // Retourne true si l'email est envoyé avec succès
    } catch (Exception $e) {
        // Si une exception est levée, retourner false
        return false;
    }
}


// Fonction pour retirer un utilisateur d'une famille (en mettant le family_id à NULL)
function removeUserFromFamily($complexeId, $userId) {
    global $conn;  
    
    // Vérifier si l'utilisateur existe et obtenir son family_id
    $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();  // Récupère les données sous forme de tableau associatif

    // Vérifie si l'utilisateur existe
    if (!$family) {
        echo "L'utilisateur n'existe pas !";
        return false;  // Retourne false si l'utilisateur n'existe pas
    }

    // Vérifie si l'utilisateur appartient bien à la famille du complexe spécifié
    if ($family['family_id'] == $complexeId) {
        echo "L'utilisateur appartient bien à cette famille.";
    } else {
        echo "L'utilisateur n'appartient pas à cette famille.";
        return false;  // Retourne false si l'utilisateur n'appartient pas à la famille du complexe
    }

    // Exécuter la mise à jour pr retirer l'utilisateur de la famille
    $stmt = $conn->prepare("UPDATE users SET family_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $userId);

    // Vérifier si la requête a été exécutée avec succès
    if ($stmt->execute()) {
        echo "Suppression réussie !";
        return true;  // Retourne true si la suppression a réussi
    } else {
        echo "Erreur SQL  : " . $stmt->error;
        return false;  // Retourne false si une erreur SQL se produit
    }
}


// Fonction pour récupérer les statistiques d'utilisation des appareils d'un utilisateur
function getDeviceUsageStats($userId) {
    global $conn;  

    // Requête SQL pour récupérer les statistiques d'utilisation des appareils
    $query = "
        SELECT 
            d.name, d.type, d.status, 
            COALESCE(SUM(dd.value), 0) AS consommation,  // Somme des valeurs de consommation pour chaque appareil
            COALESCE(COUNT(dd.id), 0) AS temps_utilisation  // Nombre de fois que l'appareil a été utilisé
        FROM devices d
        LEFT JOIN device_data dd ON d.id = dd.device_id  // Jointure avec la table device_data pour récupérer les données d'utilisation
        WHERE d.user_id = ?  // Filtrer par l'ID de l'utilisateur
        GROUP BY d.id  // Groupement par ID de l'appareil pour obtenir les statistiques de chaque appareil
    ";

    // Préparer la requête SQL
    $stmt = $conn->prepare($query);
    
    // Lier l'ID de l'utilisateur à la requête préparée
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Retourner les résultats sous forme de tableau associatif
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Fonction pour mettre à jour le niveau d'un utilisateur en fonction de ses points
function updateUserLevel($userId) {
    global $conn;  // Utilise la connexion globale à la base de données

    // Récupérer le nombre de points de l'utilisateur
    $sql = "SELECT points FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);  // Lier l'ID de l'utilisateur à la requête
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();  // Récupérer le résultat sous forme de tableau associatif
    $points = $result['points'];  // Extraire les points

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
    $stmtLevel->bind_param("si", $level, $userId);  // Lier le niveau et l'ID utilisateur
    $stmtLevel->execute();  // Exécuter la mise à jour
}


// Fonction pour ajouter des points à un utilisateur et mettre à jour son niveau en fonction des points
function updateUserPointsAndLevel($userId, $pointsAdded) {
    global $conn;  

    // Ajouter des points à l'utilisateur
    $sql = "UPDATE users SET points = points + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $pointsAdded, $userId);  // Lier les paramètres : points et userId 
    $stmt->execute();

    // Récupérer les points mis à jour pour l'utilisateur
    $sqlPoints = "SELECT points FROM users WHERE id = ?";
    $stmtPoints = $conn->prepare($sqlPoints);
    $stmtPoints->bind_param("i", $userId);  // Lier l'ID de l'utilisateur
    $stmtPoints->execute();
    $result = $stmtPoints->get_result()->fetch_assoc();  // Extraire les points
    $points = $result['points'];  // Nombre total de points après ajout

    // Déterminer le niveau de l'utilisateur en fonction des points
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
    $stmtLevel->bind_param("si", $level, $userId);  // Lier le niveau (chaîne) et l'ID utilisateur
    $stmtLevel->execute();
}

// Fonction pour vérifier l'intégrité de la base de données, notamment les clés étrangères
function checkDatabaseIntegrity() {
    global $conn;  // Utilise la connexion globale à la base de données

    // Vérification des clés étrangères : si des appareils sont associés à des utilisateurs inexistants
    $stmt = $conn->prepare("SELECT COUNT(*) FROM devices WHERE user_id NOT IN (SELECT id FROM users)");
    $stmt->execute();
    $result = $stmt->get_result();
    $invalidUsers = $result->fetch_assoc();  // Récupérer le nombre d'appareils associés à des utilisateurs inexistants

    if ($invalidUsers['COUNT(*)'] > 0) {
        echo "Il y a des appareils associés à des utilisateurs inexistants.";  // Afficher un message si l'intégrité est violée
    } else {
        echo "L'intégrité des données est vérifiée.";  // Afficher un message si l'intégrité des données est bonne
    }
}

?>
