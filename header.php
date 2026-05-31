<?php require_once __DIR__ . '/db.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>TaskCloud — UAO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            ☁️ TaskCloud
        </a>
        <div class="d-flex align-items-center">
            <span class="badge bg-secondary me-2" title="Hostname de la instancia EC2 sirviendo esta petición">
                EC2: <?= htmlspecialchars(getServerHostname()) ?>
            </span>
            <span class="badge bg-info text-dark" title="Availability Zone">
                AZ: <?= htmlspecialchars(getAvailabilityZone()) ?>
            </span>
        </div>
    </div>
</nav>
<main class="container">
