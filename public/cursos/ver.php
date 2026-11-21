<?php
require __DIR__ . '/../../config/database.php';

$id = objectId($_GET['id'] ?? null);
$c  = $id ? db()->cursos->findOne(['_id' => $id]) : null;
if (!$c) {
    flash('warning', 'Curso não encontrado.');
    redirecionar('cursos/index.php');
}

// Estudantes matriculados neste curso (matriculas + $lookup em estudantes)
$matriculas = db()->matriculas->aggregate([
    ['$match' => ['curso_id' => $id]],
    ['$lookup' => ['from' => 'estudantes', 'localField' => 'estudante_id', 'foreignField' => '_id', 'as' => 'estudante']],
    ['$unwind' => '$estudante'],
    ['$sort' => ['estudante.nome' => 1]],
])->toArray();

$ativas = count(array_filter($matriculas, fn($m) => $m['status'] === 'Ativa'));
$temVaga = $ativas < (int) $c['vagas'];

$jaMatriculados = array_map(fn($m) => $m['estudante_id'], $matriculas);
$disponiveis = db()->estudantes->find(
    ['_id' => ['$nin' => $jaMatriculados]],
    ['sort' => ['nome' => 1], 'projection' => ['nome' => 1, 'ra' => 1]]
)->toArray();

$titulo = $c['nome'];
$voltarPara = 'curso';

require __DIR__ . '/../../templates/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= url('cursos/index.php') ?>" class="text-decoration-none">Cursos</a>
        <h1 class="h3 mb-0"><?= h($c['nome']) ?> <small class="text-body-secondary fs-6"><?= h($c['codigo']) ?></small></h1>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('cursos/form.php?id=' . $id) ?>" class="btn btn-outline-primary">Editar</a>
        <form method="post" action="<?= url('cursos/excluir.php') ?>" data-confirmar="Excluir este curso e todas as matrículas nele?">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn btn-outline-danger">Excluir</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Dados do curso</h2>
            <div class="card-body">
                <p class="mb-3"><?= nl2br(h($c['descricao'])) ?></p>
                <dl class="row mb-0">
                    <dt class="col-5">Coordenador(a)</dt><dd class="col-7"><?= h($c['coordenador']) ?></dd>
                    <dt class="col-5">Modalidade</dt><dd class="col-7"><?= h($c['modalidade']) ?></dd>
                    <dt class="col-5">Turno</dt><dd class="col-7"><?= h($c['turno']) ?></dd>
                    <dt class="col-5">Carga horária</dt><dd class="col-7"><?= (int) $c['carga_horaria'] ?> horas</dd>
                    <dt class="col-5">Vagas</dt>
                    <dd class="col-7"><?= $ativas ?> de <?= (int) $c['vagas'] ?> ocupadas
                        <span class="regua regua-ficha" style="--ocupacao: <?= percentualOcupacao($ativas, (int) $c['vagas']) ?>%" aria-hidden="true"></span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Estudantes matriculados (<?= count($matriculas) ?>)</h2>

            <?php if ($disponiveis && $temVaga): ?>
            <form method="post" action="<?= url('matriculas/salvar.php') ?>" class="card-body border-bottom">
                <input type="hidden" name="curso_id" value="<?= $id ?>">
                <input type="hidden" name="voltar" value="curso">
                <div class="input-group">
                    <select name="estudante_id" class="form-select" required aria-label="Estudante">
                        <option value="">Selecione um estudante para matricular...</option>
                        <?php foreach ($disponiveis as $e): ?>
                            <option value="<?= $e['_id'] ?>"><?= h($e['ra'] . ' · ' . $e['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-principal">Matricular</button>
                </div>
            </form>
            <?php elseif (!$temVaga): ?>
                <div class="card-body border-bottom fw-semibold">Curso lotado.</div>
            <?php endif; ?>

            <?php if (!$matriculas): ?>
                <div class="card-body text-body-secondary">Nenhum estudante matriculado.</div>
            <?php else: ?>
            <div class="table-responsive" tabindex="0" role="region" aria-label="Estudantes do curso">
                <table class="table mb-0">
                    <thead><tr><th>Estudante</th><th>Desde</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($matriculas as $m): ?>
                        <tr>
                            <td>
                                <a href="<?= url('estudantes/ver.php?id=' . $m['estudante']['_id']) ?>"><?= h($m['estudante']['nome']) ?></a>
                                <div class="text-body-secondary">RA <?= h($m['estudante']['ra']) ?></div>
                            </td>
                            <td class="text-nowrap"><?= formatarData($m['data_matricula'], 'd/m/Y H:i') ?></td>
                            <td><?php include __DIR__ . '/../../templates/status-matricula.php'; ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('matriculas/excluir.php') ?>" data-confirmar="Excluir a matrícula de <?= h($m['estudante']['nome']) ?>?">
                                    <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                    <input type="hidden" name="voltar" value="curso">
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir matrícula" aria-label="Excluir matrícula de <?= h($m['estudante']['nome']) ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
