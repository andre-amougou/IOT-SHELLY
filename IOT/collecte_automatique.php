<?php
// Script exécutable via CRON (pas besoin de session)
date_default_timezone_set('UTC');

// Connexion BDD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    file_put_contents("collecte_log.txt", "[".date('Y-m-d H:i:s')."] Erreur BDD: ".$e->getMessage()."\n", FILE_APPEND);
    exit;
}

// Récupérer tous les équipements actifs (tu peux filtrer plus tard par collecter_auto = 1)
$stmt = $pdo->query("SELECT id, nom_appareil, ip FROM equipement");
$equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($equipements as $eq) {
    $id = $eq['id'];
    $ip = $eq['ip'];
    $nom = $eq['nom_appareil'];

    $url = "http://$ip/rpc/Switch.GetStatus?id=0";
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $json = @file_get_contents($url, false, $context);

    if ($json === false) {
        file_put_contents("collecte_log.txt", "[".date('Y-m-d H:i:s')."] ❌ $nom ($ip) injoignable\n", FILE_APPEND);
        continue;
    }

    $data = json_decode($json, true);
    $energie = $data['aenergy']['total'] ?? null;

    if ($energie !== null) {
        $stmtInsert = $pdo->prepare("INSERT INTO aenergy_log (equipement_id, energie_totale) VALUES (?, ?)");
        $stmtInsert->execute([$id, $energie]);

        file_put_contents("collecte_log.txt", "[".date('Y-m-d H:i:s')."] ✅ $nom ($ip) : $energie Wh\n", FILE_APPEND);
    } else {
        file_put_contents("collecte_log.txt", "[".date('Y-m-d H:i:s')."] ⚠️ $nom ($ip) : données non valides\n", FILE_APPEND);
    }
}