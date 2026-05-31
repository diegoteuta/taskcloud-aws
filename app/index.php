<?php
require_once __DIR__ . '/db.php';
$pageTitle = 'Mis tareas';

$db = getDB();

// Filtros opcionales
$filterStatus   = $_GET['status']   ?? '';
$filterPriority = $_GET['priority'] ?? '';

$sql    = "SELECT * FROM tasks WHERE 1=1";
$params = [];
if ($filterStatus !== '' && in_array($filterStatus, ['pendiente','en_progreso','completada'], true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $filterStatus;
}
if ($filterPriority !== '' && in_array($filterPriority, ['baja','media','alta'], true)) {
    $sql .= " AND priority = :priority";
    $params[':priority'] = $filterPriority;
}
$sql .= " ORDER BY
            CASE status WHEN 'en_progreso' THEN 1 WHEN 'pendiente' THEN 2 ELSE 3 END,
            CASE priority WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
            due_date IS NULL, due_date ASC, created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Estadísticas
$stats = $db->query("
    SELECT
      SUM(status='pendiente')   AS pendientes,
      SUM(status='en_progreso') AS en_progreso,
      SUM(status='completada')  AS completadas,
      COUNT(*)                  AS total
    FROM tasks
")->fetch();

include __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">📋 Mis tareas</h1>
    <a href="create.php" class="btn btn-primary">＋ Nueva tarea</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card text-center"><div class="card-body py-3">
            <div class="h2 mb-0"><?= (int)($stats['total'] ?? 0) ?></div>
            <small class="text-muted">Total</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-warning"><div class="card-body py-3">
            <div class="h2 mb-0 text-warning"><?= (int)($stats['pendientes'] ?? 0) ?></div>
            <small class="text-muted">Pendientes</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-primary"><div class="card-body py-3">
            <div class="h2 mb-0 text-primary"><?= (int)($stats['en_progreso'] ?? 0) ?></div>
            <small class="text-muted">En progreso</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-success"><div class="card-body py-3">
            <div class="h2 mb-0 text-success"><?= (int)($stats['completadas'] ?? 0) ?></div>
            <small class="text-muted">Completadas</small>
        </div></div>
    </div>
</div>

<form class="row g-2 mb-4" method="get">
    <div class="col-md-3">
        <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">— Todos los estados —</option>
            <option value="pendiente"    <?= $filterStatus==='pendiente'    ? 'selected' : '' ?>>Pendiente</option>
            <option value="en_progreso"  <?= $filterStatus==='en_progreso'  ? 'selected' : '' ?>>En progreso</option>
            <option value="completada"   <?= $filterStatus==='completada'   ? 'selected' : '' ?>>Completada</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="priority" onchange="this.form.submit()">
            <option value="">— Todas las prioridades —</option>
            <option value="alta"  <?= $filterPriority==='alta'  ? 'selected' : '' ?>>Alta</option>
            <option value="media" <?= $filterPriority==='media' ? 'selected' : '' ?>>Media</option>
            <option value="baja"  <?= $filterPriority==='baja'  ? 'selected' : '' ?>>Baja</option>
        </select>
    </div>
    <?php if ($filterStatus || $filterPriority): ?>
    <div class="col-md-2">
        <a href="index.php" class="btn btn-outline-secondary w-100">Limpiar</a>
    </div>
    <?php endif; ?>
</form>

<?php if (empty($tasks)): ?>
    <div class="empty-state">
        <div class="icon">📭</div>
        <h4>No hay tareas</h4>
        <p>Agrega tu primera tarea para empezar.</p>
        <a href="create.php" class="btn btn-primary">＋ Crear tarea</a>
    </div>
<?php else: ?>
    <div class="row g-3">
    <?php foreach ($tasks as $task): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card task-card priority-<?= htmlspecialchars($task['priority']) ?> status-<?= htmlspecialchars($task['status']) ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                        <span class="badge priority-badge bg-<?=
                            $task['priority']==='alta'  ? 'danger' :
                           ($task['priority']==='media' ? 'warning text-dark' : 'success')
                        ?>"><?= htmlspecialchars($task['priority']) ?></span>
                    </div>
                    <?php if (!empty($task['description'])): ?>
                        <p class="small mb-2"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                    <?php endif; ?>
                    <div class="task-meta mb-2">
                        <span class="badge bg-light text-dark border">
                            <?= ['pendiente'=>'⏳ Pendiente','en_progreso'=>'⚙️ En progreso','completada'=>'✅ Completada'][$task['status']] ?>
                        </span>
                        <?php if (!empty($task['due_date'])): ?>
                            <span class="ms-1">📅 <?= htmlspecialchars($task['due_date']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?= (int)$task['id'] ?>"   class="btn btn-sm btn-outline-primary flex-fill">✏️ Editar</a>
                        <form method="post" action="delete.php" onsubmit="return confirm('¿Eliminar esta tarea?');" class="flex-fill m-0">
                            <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">🗑️ Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
