<?php
require_once 'config.php';

if (!isAdmin()) {
    redirect('index.php');
}

$pdo = getDB();

// Récupérer toutes les données
$stmt = $pdo->query("
    SELECT 
        e.nom_prenom as 'Élève',
        et.nom as 'Établissement',
        e.classe as 'Classe',
        e.notif_i as 'Notif. Individuelle',
        e.notif_m as 'Notif. Mutualisée',
        CASE WHEN e.plan_b = 1 THEN 'Oui' ELSE '' END as 'Plan B',
        e.aesh as 'AESH',
        e.heures_effectives as 'Heures effectives',
        e.remarques as 'Remarques'
    FROM eleves e 
    JOIN etablissements et ON e.etablissement_id = et.id 
    ORDER BY et.nom, e.nom_prenom
");
$data = $stmt->fetchAll();

// Export CSV (compatible Excel)
$filename = 'export_aesh_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// BOM pour Excel
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// En-têtes
if (count($data) > 0) {
    fputcsv($output, array_keys($data[0]), ';');
}

// Données
foreach ($data as $row) {
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
