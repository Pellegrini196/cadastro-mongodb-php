<?php
/**
 * Cria uma matrícula = um documento na coleção "matriculas" que REFERENCIA
 * um estudante e um curso pelos seus _id (relação N:N).
 */
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../src/matriculas.php';
exigirPost();

$estudanteId = objectId($_POST['estudante_id'] ?? null);
$cursoId     = objectId($_POST['curso_id'] ?? null);

$estudante = $estudanteId ? db()->estudantes->findOne(['_id' => $estudanteId]) : null;
$curso     = $cursoId ? db()->cursos->findOne(['_id' => $cursoId]) : null;

if (!$estudante || !$curso) {
    flash('danger', 'Selecione um estudante e um curso válidos.');
    voltarMatricula($estudanteId, $cursoId);
}

if (!cursoTemVaga($curso)) {
    flash('warning', "O curso {$curso['nome']} não tem mais vagas.");
    voltarMatricula($estudanteId, $cursoId);
}

try {
    db()->matriculas->insertOne([
        'estudante_id'   => $estudanteId,
        'curso_id'       => $cursoId,
        'data_matricula' => agora(),
        'status'         => 'Ativa',
    ]);
    flash('success', "{$estudante['nome']} matriculado(a) em {$curso['nome']}.");
} catch (\Throwable $e) {
    if ((int) $e->getCode() !== 11000) throw $e;
    flash('warning', 'Este estudante já está matriculado neste curso.');
}

voltarMatricula($estudanteId, $cursoId);
