<?php
require __DIR__ . '/../../config/database.php';

$id = objectId($_GET['id'] ?? null);
$e  = $id ? db()->estudantes->findOne(['_id' => $id]) : null;
if (!$e) {
    flash('warning', 'Estudante não encontrado.');
    redirecionar('estudantes/index.php');
}

// Cursos em que o estudante está matriculado (matriculas + $lookup em cursos)
$matriculas = db()->matriculas->aggregate([
    ['$match' => ['estudante_id' => $id]],
    ['$lookup' => ['from' => 'cursos', 'localField' => 'curso_id', 'foreignField' => '_id', 'as' => 'curso']],
    ['$unwind' => '$curso'],
    ['$sort' => ['curso.nome' => 1]],
])->toArray();

// Cursos disponíveis para nova matrícula (os que ele ainda não faz)
$jaMatriculado = array_map(fn($m) => $m['curso_id'], $matriculas);
$disponiveis = db()->cursos->find(
    ['_id' => ['$nin' => $jaMatriculado]],
    ['sort' => ['nome' => 1], 'projection' => ['nome' => 1, 'codigo' => 1]]
)->toArray();

$end = $e['endereco'] ?? [];
$cep = $end['cep'] ?? '';
$titulo = $e['nome'];

require __DIR__ . '/../../templates/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= url('estudantes/index.php') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Estudantes</a>
        <h1 class="h3 mb-0"><?= h($e['nome']) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('estudantes/form.php?id=' . $id) ?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Editar</a>
        <form method="post" action="<?= url('estudantes/excluir.php') ?>" data-confirmar="Excluir este estudante e todas as suas matrículas?">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Excluir</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card mb-3">
            <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Dados pessoais</h2>
            <dl class="row card-body mb-0">
                <dt class="col-5">RA</dt><dd class="col-7"><?= h($e['ra']) ?></dd>
                <dt class="col-5">CPF</dt><dd class="col-7"><?= h(formatarCpf($e['cpf'])) ?></dd>
                <dt class="col-5">Nascimento</dt><dd class="col-7"><?= formatarData($e['data_nascimento'] ?? null) ?></dd>
                <dt class="col-5">E-mail</dt><dd class="col-7 text-break"><?= h($e['email']) ?></dd>
                <dt class="col-5">Telefone</dt><dd class="col-7"><?= h($e['telefone']) ?></dd>
            </dl>
        </div>
        <div class="card">
            <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Endereço</h2>
            <div class="card-body">
                <?= h($end['logradouro'] ?? '') ?>, <?= h($end['numero'] ?? '') ?>
                <?= !empty($end['complemento']) ? ' - ' . h($end['complemento']) : '' ?><br>
                <?= h($end['bairro'] ?? '') ?>, <?= h($end['cidade'] ?? '') ?>/<?= h($end['uf'] ?? '') ?><br>
                CEP <?= h(strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep) ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <h2 class="card-header bg-transparent fw-semibold fs-6 mb-0">Cursos matriculados (<?= count($matriculas) ?>)</h2>

            <?php if ($disponiveis): ?>
            <form method="post" action="<?= url('matriculas/salvar.php') ?>" class="card-body border-bottom">
                <input type="hidden" name="estudante_id" value="<?= $id ?>">
                <input type="hidden" name="voltar" value="estudante">
                <div class="input-group">
                    <select name="curso_id" class="form-select" required aria-label="Curso">
                        <option value="">Selecione um curso para matricular...</option>
                        <?php foreach ($disponiveis as $c): ?>
                            <option value="<?= $c['_id'] ?>"><?= h($c['codigo'] . ' · ' . $c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-success"><i class="bi bi-plus-lg"></i> Matricular</button>
                </div>
            </form>
            <?php endif; ?>

            <?php if (!$matriculas): ?>
                <div class="card-body text-body-secondary">Ainda não está matriculado em nenhum curso.</div>
            <?php else: ?>
            <div class="table-responsive" tabindex="0" role="region" aria-label="Cursos do estudante">
                <table class="table mb-0">
                    <thead><tr><th>Curso</th><th>Desde</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($matriculas as $m): ?>
                        <tr>
                            <td>
                                <a href="<?= url('cursos/ver.php?id=' . $m['curso']['_id']) ?>"><?= h($m['curso']['nome']) ?></a>
                                <div class="small text-body-secondary"><?= h($m['curso']['codigo']) ?></div>
                            </td>
                            <td><?= formatarData($m['data_matricula'], 'd/m/Y H:i') ?></td>
                            <td><?php include __DIR__ . '/../../templates/status-matricula.php'; ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('matriculas/excluir.php') ?>" data-confirmar="Excluir esta matrícula?">
                                    <input type="hidden" name="id" value="<?= $m['_id'] ?>">
                                    <input type="hidden" name="voltar" value="estudante">
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir matrícula" aria-label="Excluir matrícula"><i class="bi bi-x-lg"></i></button>
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
