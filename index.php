<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $pdo = getDB();
    
    // Vérifier admin
    if ($login === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE login = ? AND password = ?");
        $stmt->execute([$login, $password]);
        if ($stmt->fetch()) {
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_name'] = 'Administrateur';
            redirect('admin.php');
        }
    }
    
    // Vérifier établissement
    $stmt = $pdo->prepare("SELECT * FROM etablissements WHERE login = ? AND password = ?");
    $stmt->execute([strtoupper($login), $password]);
    $etabs = $stmt->fetchAll();
    
    if (count($etabs) > 0) {
        $_SESSION['user_type'] = 'etablissement';
        $_SESSION['etablissements'] = $etabs;
        $_SESSION['user_name'] = $login;
        redirect('etablissement.php');
    }
    
    $error = "Identifiants incorrects";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion AESH - Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Gestion AESH</h1>
            <h2>Circonscription de Vienne</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="login">Identifiant</label>
                    <input type="text" id="login" name="login" required placeholder="Ex: PONSARD ou admin">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="Mot de passe">
                </div>
                
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
