<?php
require_once 'config.php';

if (!isAdmin()) {
    redirect('index.php');
}

$pdo = getDB();

// Traitement suppression élève !!!!!!!!!!! ******** --------***********-----------////////
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM eleves WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    redirect('admin.php?msg=deleted');
}

// Traitement mise à jour élève
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_eleve'])) {
    $id = (int)$_POST['eleve_id'];
    $stmt = $pdo->prepare("UPDATE eleves SET 
        nom_prenom = ?, etablissement_id = ?, classe = ?, 
        notif_i = ?, notif_m = ?, plan_b = ?,
        aesh = ?, heures_effectives = ?, remarques = ?
        WHERE id = ?");
    $stmt->execute([
        $_POST['nom_prenom'],
        $_POST['etablissement_id'],
        $_POST['classe'],
        $_POST['notif_i'],
        $_POST['notif_m'],
        isset($_POST['plan_b']) ? 1 : 0,
        $_POST['aesh'],
        $_POST['heures'] !== '' ? $_POST['heures'] : null,
        $_POST['remarques'],
        $id
    ]);
    redirect('admin.php?msg=saved');
}

// Traitement ajout élève
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_eleve'])) {
    $stmt = $pdo->prepare("INSERT INTO eleves (nom_prenom, etablissement_id, classe, notif_i, notif_m, plan_b) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nom_prenom'],
        $_POST['etablissement_id'],
        $_POST['classe'],
        $_POST['notif_i'],
        $_POST['notif_m'],
        isset($_POST['plan_b']) ? 1 : 0
    ]);
    redirect('admin.php?msg=added');
}

// Traitement mise à jour établissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_etab'])) {
    $stmt = $pdo->prepare("UPDATE etablissements SET password = ? WHERE id = ?");
    $stmt->execute([$_POST['password'], $_POST['etab_id']]);
    redirect('admin.php?tab=etab&msg=saved');
}

// Récupérer les données
$etablissements = $pdo->query("SELECT * FROM etablissements ORDER BY nom")->fetchAll();
$etab_map = [];
foreach ($etablissements as $e) {
    $etab_map[$e['id']] = $e['nom'];
}

// Filtre
$filter_etab = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT e.*, et.nom as etablissement_nom 
        FROM eleves e 
        JOIN etablissements et ON e.etablissement_id = et.id 
        WHERE 1=1";
$params = [];

if ($filter_etab) {
    $sql .= " AND e.etablissement_id = ?";
    $params[] = $filter_etab;
}
if ($search) {
    $sql .= " AND e.nom_prenom LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY et.nom, e.nom_prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$eleves = $stmt->fetchAll();

$tab = $_GET['tab'] ?? 'eleves';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion AESH - Administration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Administration - Gestion AESH</h1>
            <div>
                <a href="export.php" class="btn btn-export">Exporter Excel</a>
                <a href="logout.php" class="btn btn-logout">Déconnexion</a>
            </div>
        </header>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="success">
                <?php
                switch($_GET['msg']) {
                    case 'saved': echo "Modifications enregistrées"; break;
                    case 'added': echo "Élève ajouté"; break;
                    case 'deleted': echo "Élève supprimé"; break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <a href="?tab=eleves" class="tab <?= $tab === 'eleves' ? 'active' : '' ?>">Élèves (<?= count($eleves) ?>)</a>
            <a href="?tab=etab" class="tab <?= $tab === 'etab' ? 'active' : '' ?>">Établissements</a>
            <a href="?tab=add" class="tab <?= $tab === 'add' ? 'active' : '' ?>">+ Ajouter élève</a>
        </div>
        
        <?php if ($tab === 'eleves'): ?>
            <!-- Filtres -->
            <form method="get" class="filters">
                <input type="hidden" name="tab" value="eleves">
                <select name="filter">
                    <option value="">-- Tous les établissements --</option>
                    <?php foreach ($etablissements as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= $filter_etab == $e['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="search" placeholder="Rechercher un élève..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn">Filtrer</button>
                <a href="?tab=eleves" class="btn">Reset</a>
            </form>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Établissement</th>
                            <th>Classe</th>
                            <th>Notif. I</th>
                            <th>Notif. M</th>
                            <th>Plan B</th>
                            <th>AESH</th>
                            <th>Heures</th>
                            <th>Remarques</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eleves as $eleve): ?>
                            <tr>
                                <form method="post">
                                    <input type="hidden" name="eleve_id" value="<?= $eleve['id'] ?>">
                                    <td><input type="text" name="nom_prenom" value="<?= htmlspecialchars($eleve['nom_prenom']) ?>" class="editable"></td>
                                    <td>
                                        <select name="etablissement_id" class="editable">
                                            <?php foreach ($etablissements as $e): ?>
                                                <option value="<?= $e['id'] ?>" <?= $eleve['etablissement_id'] == $e['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($e['nom']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="classe" value="<?= htmlspecialchars($eleve['classe']) ?>" class="editable small"></td>
                                    <td><input type="text" name="notif_i" value="<?= htmlspecialchars($eleve['notif_i']) ?>" class="editable small"></td>
                                    <td><input type="text" name="notif_m" value="<?= htmlspecialchars($eleve['notif_m']) ?>" class="editable small"></td>
                                    <td class="center"><input type="checkbox" name="plan_b" <?= $eleve['plan_b'] ? 'checked' : '' ?>></td>
                                    <td><input type="text" name="aesh" value="<?= htmlspecialchars($eleve['aesh'] ?? '') ?>" class="editable"></td>
                                    <td><input type="number" name="heures" value="<?= $eleve['heures_effectives'] ?? '' ?>" class="editable tiny" step="0.5"></td>
                                    <td><input type="text" name="remarques" value="<?= htmlspecialchars($eleve['remarques'] ?? '') ?>" class="editable"></td>
                                    <td class="actions">
                                        <button type="submit" name="save_eleve" class="btn btn-save" title="Enregistrer">✓</button>
                                        <a href="?delete=<?= $eleve['id'] ?>" class="btn btn-delete" 
                                           onclick="return confirm('Supprimer cet élève ?')" title="Supprimer">✕</a>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        
        <?php elseif ($tab === 'etab'): ?>
            <h2>Gestion des établissements</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Établissement</th>
                            <th>Login</th>
                            <th>Mot de passe</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etablissements as $e): ?>
                            <tr>
                                <form method="post">
                                    <input type="hidden" name="etab_id" value="<?= $e['id'] ?>">
                                    <td><?= htmlspecialchars($e['nom']) ?></td>
                                    <td><code><?= htmlspecialchars($e['login']) ?></code></td>
                                    <td><input type="text" name="password" value="<?= htmlspecialchars($e['password']) ?>" class="editable small"></td>
                                    <td><button type="submit" name="save_etab" class="btn btn-save">✓</button></td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        
        <?php elseif ($tab === 'add'): ?>
            <h2>Ajouter un élève</h2>
            <form method="post" class="form-add">
                <div class="form-row">
                    <label>Nom Prénom</label>
                    <input type="text" name="nom_prenom" required>
                </div>
                <div class="form-row">
                    <label>Établissement</label>
                    <select name="etablissement_id" required>
                        <?php foreach ($etablissements as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label>Classe</label>
                    <input type="text" name="classe">
                </div>
                <div class="form-row">
                    <label>Notification Individuelle</label>
                    <input type="text" name="notif_i" placeholder="Ex: 12H00-2026">
                </div>
                <div class="form-row">
                    <label>Notification Mutualisée</label>
                    <input type="text" name="notif_m" placeholder="Ex: M-2027">
                </div>
                <div class="form-row">
                    <label>Plan B</label>
                    <input type="checkbox" name="plan_b">
                </div>
                <button type="submit" name="add_eleve" class="btn btn-primary">Ajouter</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
