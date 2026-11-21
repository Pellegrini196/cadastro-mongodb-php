<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../src/matriculas.php';
exigirPost();

$id = objectId($_POST['id'] ?? null);
$m  = $id ? db()->matriculas->findOne(['_id' => $id]) : null;

if (!$m) {
    flash('warning', 'Matrícula não encontrada.');
    redirecionar('index.php');
}

// Sem JavaScript, pede a confirmação em uma página própria antes de apagar
$origem = ($_POST['voltar'] ?? '') === 'curso'
    ? 'cursos/ver.php?id=' . $m['curso_id']
    : 'estudantes/ver.php?id=' . $m['estudante_id'];
exigirConfirmacao('Excluir esta matrícula?', $origem);

db()->matriculas->deleteOne(['_id' => $id]);

flash('success', 'Matrícula excluída.');
voltarMatricula($m['estudante_id'], $m['curso_id']);
