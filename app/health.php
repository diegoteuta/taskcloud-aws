<?php
/**
 * health.php — Endpoint de health check para el Application Load Balancer.
 * Devuelve HTTP 200 si la app puede conectar a la base de datos, 503 si no.
 * Configurar en el Target Group del ALB como Health Check Path: /health.php
 */
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    $row = $db->query("SELECT 1 AS ok")->fetch();
    if ($row && (int)$row['ok'] === 1) {
        http_response_code(200);
        echo json_encode([
            'status'   => 'ok',
            'hostname' => getServerHostname(),
            'az'       => getAvailabilityZone(),
            'time'     => date('c'),
        ]);
        exit;
    }
    throw new RuntimeException('Query did not return expected result');
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'error'  => $e->getMessage(),
        'time'   => date('c'),
    ]);
}
