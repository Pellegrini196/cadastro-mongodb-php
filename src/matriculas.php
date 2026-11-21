<?php
/** Funções usadas pelos scripts de matrícula. */

use MongoDB\BSON\ObjectId;

/** Volta para a página do estudante ou do curso, conforme o formulário de origem. */
function voltarMatricula(?ObjectId $estudanteId, ?ObjectId $cursoId): never
{
    if (($_POST['voltar'] ?? '') === 'curso' && $cursoId) {
        redirecionar('cursos/ver.php?id=' . $cursoId);
    }
    if ($estudanteId) {
        redirecionar('estudantes/ver.php?id=' . $estudanteId);
    }
    redirecionar('index.php');
}

/** Verifica se ainda há vaga no curso (conta só matrículas ativas). */
function cursoTemVaga(array $curso): bool
{
    $ativas = db()->matriculas->countDocuments(['curso_id' => $curso['_id'], 'status' => 'Ativa']);
    return $ativas < (int) $curso['vagas'];
}
