<?php
require_once 'config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('index.php');
}

$pdo = getDB();
$etablissements = $_SESSION['etablissements'];
$etab_ids = array_column($etablissements, 'id');

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $eleve_id = (int)$_POST['eleve_id'];
    $aesh = trim($_POST['aesh'] ?? '');
    $heures = $_POST['heures'] !== '' ? (float)$_POST['heures'] : null;
    $remarques = trim($_POST['remarques'] ?? '');
    
    // Vérifier que l'élève appartient bien à cet établissement
    $stmt = $pdo->prepare("SELECT etablissement_id FROM eleves WHERE id = ?");
    $stmt->execute([$eleve_id]);
    $eleve = $stmt->fetch();
    
    if ($eleve && in_array($eleve['etablissement_id'], $etab_ids)) {
        $stmt = $pdo->prepare("UPDATE eleves SET aesh = ?, heures_effectives = ?, remarques = ? WHERE id = ?");
        $stmt->execute([$aesh, $heures, $remarques, $eleve_id]);
        $success = true;
    }
}

// Récupérer les élèves de ces établissements
$placeholders = implode(',', array_fill(0, count($etab_ids), '?'));
$stmt = $pdo->prepare("
    SELECT e.*, et.nom as etablissement_nom 
    FROM eleves e 
    JOIN etablissements et ON e.etablissement_id = et.id 
    WHERE e.etablissement_id IN ($placeholders)
    ORDER BY et.nom, e.nom_prenom
");
$stmt->execute($etab_ids);
$eleves = $stmt->fetchAll();

// Grouper par établissement
$eleves_par_etab = [];
foreach ($eleves as $eleve) {
    $eleves_par_etab[$eleve['etablissement_nom']][] = $eleve;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion AESH - <?= htmlspecialchars($_SESSION['user_name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestion AESH - <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
            <a href="logout.php" class="btn btn-logout">Déconnexion</a>
        </header>
        
        <?php if (isset($success)): ?>
            <div class="success">Modifications enregistrées avec succès</div>
        <?php endif; ?>
        
        <p class="info">
            <strong>Champs modifiables :</strong> AESH, Heures effectives, Remarques<br>
            Les autres champs sont en lecture seule.
        </p>
        
        <?php foreach ($eleves_par_etab as $etab_nom => $eleves_etab): ?>
            <h2 class="etab-title"><?= htmlspecialchars($etab_nom) ?> (<?= count($eleves_etab) ?> élèves)</h2>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Notif. I</th>
                            <th>Notif. M</th>
                            <th>Plan B</th>
                            <th>AESH</th>
                            <th>Heures eff.</th>
                            <th>Remarques</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eleves_etab as $eleve): ?>
                            <tr>
                                <form method="post">
                                    <input type="hidden" name="eleve_id" value="<?= $eleve['id'] ?>">
                                    <td class="readonly"><?= htmlspecialchars($eleve['nom_prenom']) ?></td>
                                    <td class="readonly"><?= htmlspecialchars($eleve['classe']) ?></td>
                                    <td class="readonly"><?= htmlspecialchars($eleve['notif_i']) ?></td>
                                    <td class="readonly"><?= htmlspecialchars($eleve['notif_m']) ?></td>
                                    <td class="readonly center"><?= $eleve['plan_b'] ? '✓' : '' ?></td>
                                    <td>
                                        <input type="text" name="aesh" value="<?= htmlspecialchars($eleve['aesh'] ?? '') ?>" 
                                               class="editable" placeholder="Nom(s) AESH" maxlength="200">
                                    </td>
                                    <td>
                                        <input type="number" name="heures" value="<?= $eleve['heures_effectives'] ?? '' ?>" 
                                               class="editable small" step="0.5" min="0" max="40">
                                    </td>
                                    <td>
                                        <input type="text" name="remarques" value="<?= htmlspecialchars($eleve['remarques'] ?? '') ?>" 
                                               class="editable" placeholder="Remarques">
                                    </td>
                                    <td>
                                        <button type="submit" name="save" class="btn btn-save">✓</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
