<?php
require_once __DIR__ . '/db.php';
$pageTitle = 'Editar tarea';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$errors = [];

// Carga inicial
$stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
$stmt->execute([':id' => $id]);
$task = $stmt->fetch();
if (!$task) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task['title']       = trim($_POST['title']       ?? '');
    $task['description'] = trim($_POST['description'] ?? '');
    $task['priority']    = $_POST['priority']         ?? 'media';
    $task['status']      = $_POST['status']           ?? 'pendiente';
    $task['due_date']    = $_POST['due_date']         ?? '';

    if ($task['title'] === '') {
        $errors[] = 'El título es obligatorio.';
    }
    if (!in_array($task['priority'], ['baja','media','alta'], true)) {
        $errors[] = 'Prioridad inválida.';
    }
    if (!in_array($task['status'], ['pendiente','en_progreso','completada'], true)) {
        $errors[] = 'Estado inválido.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE tasks
            SET title=:title, description=:description, priority=:priority,
                status=:status, due_date=:due_date
            WHERE id=:id
        ");
        $stmt->execute([
            ':title'       => $task['title'],
            ':description' => $task['description'] !== '' ? $task['description'] : null,
            ':priority'    => $task['priority'],
            ':status'      => $task['status'],
            ':due_date'    => $task['due_date'] !== '' ? $task['due_date'] : null,
            ':id'          => $id,
        ]);
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 mb-4">✏️ Editar tarea</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="card">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-control" required maxlength="200"
                           value="<?= htmlspecialchars($task['title']) ?>" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Prioridad</label>
                        <select name="priority" class="form-select">
                            <?php foreach (['baja','media','alta'] as $p): ?>
                                <option value="<?= $p ?>" <?= $task['priority']===$p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="pendiente"   <?= $task['status']==='pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_progreso" <?= $task['status']==='en_progreso' ? 'selected' : '' ?>>En progreso</option>
                            <option value="completada"  <?= $task['status']==='completada'  ? 'selected' : '' ?>>Completada</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha límite</label>
                        <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">
                    </div>
                </div>
                <small class="text-muted d-block mt-3">
                    Creada: <?= htmlspecialchars($task['created_at']) ?> · Última modificación: <?= htmlspecialchars($task['updated_at']) ?>
                </small>
            </div>
            <div class="card-footer d-flex justify-content-between bg-white">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
