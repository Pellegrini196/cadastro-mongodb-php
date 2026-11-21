<?php
require __DIR__ . '/../../config/database.php';
exigirPost();

$id = objectId($_POST['id'] ?? null);

$curso = $id ? db()->cursos->findOne(['_id' => $id]) : null;
if ($curso) {
    // Sem JavaScript, pede a confirmação em uma página própria antes de apagar
    exigirConfirmacao("Excluir o curso {$curso['nome']} e todas as matrículas nele?", 'cursos/ver.php?id=' . $id);
}

$resultado = $id ? db()->cursos->deleteOne(['_id' => $id]) : null;

if ($resultado && $resultado->getDeletedCount() === 1) {
    // Exclusão em cascata: remove as matrículas deste curso
    $removidas = db()->matriculas->deleteMany(['curso_id' => $id])->getDeletedCount();
    flash('success', "Curso excluído ($removidas matrícula(s) excluída(s)).");
} else {
    flash('warning', 'Curso não encontrado.');
}

redirecionar('cursos/index.php');
