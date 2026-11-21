<?php
require __DIR__ . '/../config/database.php';

$titulo = 'Início';

try {
    $totalEstudantes = db()->estudantes->countDocuments();
    $totalCursos     = db()->cursos->countDocuments();
    $totalMatriculas = db()->matriculas->countDocuments(['status' => 'Ativa']);

    // Últimas matrículas: junta (com $lookup) os dados do estudante e do curso
    $ultimas = db()->matriculas->aggregate([
        ['$sort' => ['data_matricula' => -1]],
        ['$limit' => 5],
        ['$lookup' => ['from' => 'estudantes', 'localField' => 'estudante_id', 'foreignField' => '_id', 'as' => 'estudante']],
        ['$lookup' => ['from' => 'cursos', 'localField' => 'curso_id', 'foreignField' => '_id', 'as' => 'curso']],
        ['$unwind' => '$estudante'],
        ['$unwind' => '$curso'],
    ])->toArray();
    $erroConexao = null;
} catch (\Throwable $e) {
    $erroConexao = $e->getMessage();
}

require __DIR__ . '/../templates/header.php';
?>
<h1 class="h3 mb-4">Painel</h1>

<?php if ($erroConexao): ?>
    <div class="alert alert-danger">
        <strong>Não foi possível conectar ao MongoDB.</strong><br>
        Confira o arquivo <code>.env</code> (MONGODB_URI) e se o servidor está rodando.<br>
        <small class="text-body-secondary"><?= h($erroConexao) ?></small>
    </div>
<?php else: ?>
<div class="list-group mb-4">
    <a href="<?= url('estudantes/index.php') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        Estudantes <span class="badge text-bg-success rounded-pill"><?= $totalEstudantes ?></span>
    </a>
    <a href="<?= url('cursos/index.php') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        Cursos <span class="badge text-bg-success rounded-pill"><?= $totalCursos ?></span>
    </a>
    <div class="list-group-item d-flex justify-content-between align-items-center">
        Matrículas ativas <span class="badge text-bg-success rounded-pill"><?= $totalMatriculas ?></span>
    </div>
</div>

<div class="card">
    <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Últimas matrículas</h2>
    <?php if (!$ultimas): ?>
        <div class="card-body text-body-secondary">Nenhuma matrícula.</div>
    <?php else: ?>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Últimas matrículas">
        <table class="table mb-0">
            <thead><tr><th>Estudante</th><th>Curso</th><th>Data</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($ultimas as $m): ?>
                <tr>
                    <td><a href="<?= url('estudantes/ver.php?id=' . $m['estudante']['_id']) ?>"><?= h($m['estudante']['nome']) ?></a></td>
                    <td><a href="<?= url('cursos/ver.php?id=' . $m['curso']['_id']) ?>"><?= h($m['curso']['nome']) ?></a></td>
                    <td><?= formatarData($m['data_matricula'], 'd/m/Y H:i') ?></td>
                    <td><?= h($m['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
