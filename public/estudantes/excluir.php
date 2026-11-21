<?php
require __DIR__ . '/../../config/database.php';
exigirPost();

$id = objectId($_POST['id'] ?? null);

$estudante = $id ? db()->estudantes->findOne(['_id' => $id]) : null;
if ($estudante) {
    // Sem JavaScript, pede a confirmação em uma página própria antes de apagar
    exigirConfirmacao("Excluir {$estudante['nome']} e todas as suas matrículas?", 'estudantes/ver.php?id=' . $id);
}

$resultado = $id ? db()->estudantes->deleteOne(['_id' => $id]) : null;

if ($resultado && $resultado->getDeletedCount() === 1) {
    // Exclusão em cascata: remove as matrículas que apontavam para este estudante
    $removidas = db()->matriculas->deleteMany(['estudante_id' => $id])->getDeletedCount();
    flash('success', "Estudante excluído ($removidas matrícula(s) excluída(s)).");
} else {
    flash('warning', 'Estudante não encontrado.');
}

redirecionar('estudantes/index.php');
