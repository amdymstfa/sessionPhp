<?php
// config.php - Configuration de base
session_start();
define('COOKIE_DURATION', 3 * 24); // 3 jours
$valid_users = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'user' => password_hash('user123', PASSWORD_DEFAULT)
];

//utilisateur est connecté
function isLoggedIn() {
    if (isset($_SESSION['user'])) {
        return true;
    }
    if (isset($_COOKIE['remember_user'])) {
        $_SESSION['user'] = $_COOKIE['remember_user'];
        return true;
    }
    return false;
}

//nettoyer  données
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<?php
// Page de connexion
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = cleanInput($_POST['username']);
        $password = $_POST['password'];

        if (isset($valid_users[$username]) && 
            password_verify($password, $valid_users[$username])) {
            
            $_SESSION['user'] = $username;
            
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                setcookie('remember_user', $username, time() + COOKIE_DURATION, '/');
            }
            
            $success = 'Connexion réussie!';
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    
    <?php if (!isLoggedIn()): ?>
    <form method="POST" action="">
        <div>
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label>
                <input type="checkbox" name="remember"> Se souvenir de moi
            </label>
        </div>
        <button type="submit">Se connecter</button>
    </form>
    <?php else: ?>
        <p>Vous êtes connecté en tant que <?php echo $_SESSION['user']; ?></p>
        <a href="logout.php">Se déconnecter</a>
    <?php endif; ?>
</body>
</html>

<?php
// Gestion des préférences utilisateur
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['language'])) {
    $language = cleanInput($_POST['language']);
    setcookie('user_language', $language, time() + COOKIE_DURATION, '/');
    $message = 'Préférences sauvegardées !';
}

$current_language = $_COOKIE['user_language'] ?? 'fr';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Préférences</title>
</head>
<body>
    <h1>Préférences utilisateur</h1>
    
    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div>
            <label for="language">Langue préférée:</label>
            <select id="language" name="language">
                <option value="fr" <?php echo $current_language === 'fr' ? 'selected' : ''; ?>>Français</option>
                <option value="en" <?php echo $current_language === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="es" <?php echo $current_language === 'es' ? 'selected' : ''; ?>>Español</option>
            </select>
        </div>
        <button type="submit">Sauvegarder</button>
    </form>
    
    <p>
        <?php
        $welcome_messages = [
            'fr' => 'Bienvenue sur notre site !',
            'en' => 'Welcome to our website!',
            'es' => '¡Bienvenido a nuestro sitio web!'
        ];
        echo $welcome_messages[$current_language] ?? $welcome_messages['fr'];
        ?>
    </p>
    
    <p><a href="logout.php">Se déconnecter</a></p>
</body>
</html>

<?php
// Déconnexion
require_once 'config.php';

// Destruction de la session
session_unset();
session_destroy();

// Suppression du cookie "Se souvenir de moi"
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirection vers la page de connexion
header('Location: login.php');
exit;
?>