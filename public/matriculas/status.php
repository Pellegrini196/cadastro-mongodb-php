<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../src/matriculas.php';
exigirPost();

$id     = objectId($_POST['id'] ?? null);
$status = $_POST['status'] ?? '';
$m      = $id ? db()->matriculas->findOne(['_id' => $id]) : null;

if (!$m || !in_array($status, STATUS_MATRICULA, true)) {
    flash('danger', 'Matrícula ou status inválido.');
    redirecionar('index.php');
}

// Reativar uma matrícula ocupa vaga de novo
if ($status === 'Ativa' && $m['status'] !== 'Ativa') {
    $curso = db()->cursos->findOne(['_id' => $m['curso_id']]);
    if ($curso && !cursoTemVaga($curso)) {
        flash('warning', 'Não há vagas para reativar esta matrícula.');
        voltarMatricula($m['estudante_id'], $m['curso_id']);
    }
}

db()->matriculas->updateOne(['_id' => $id], ['$set' => ['status' => $status]]);
flash('success', "Status da matrícula alterado para \"$status\".");
voltarMatricula($m['estudante_id'], $m['curso_id']);
