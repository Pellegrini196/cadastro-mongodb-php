<?php
require __DIR__ . '/../../config/database.php';

$titulo = 'Cursos';
$busca  = trim($_GET['q'] ?? '');

$filtro = [];
if ($busca !== '') {
    $regex  = new MongoDB\BSON\Regex(preg_quote($busca), 'i');
    $filtro = ['$or' => [['nome' => $regex], ['codigo' => $regex], ['coordenador' => $regex]]];
}

// $lookup com pipeline: conta só as matrículas ATIVAS de cada curso
$cursos = db()->cursos->aggregate([
    ['$match' => (object) $filtro],
    ['$sort' => ['nome' => 1]],
    ['$lookup' => [
        'from' => 'matriculas',
        'let' => ['cursoId' => '$_id'],
        'pipeline' => [
            ['$match' => ['$expr' => ['$and' => [
                ['$eq' => ['$curso_id', '$$cursoId']],
                ['$eq' => ['$status', 'Ativa']],
            ]]]],
        ],
        'as' => 'ativas',
    ]],
    ['$addFields' => ['matriculados' => ['$size' => '$ativas']]],
    ['$project' => ['ativas' => 0]],
])->toArray();

require __DIR__ . '/../../templates/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Cursos</h1>
    <a href="<?= url('cursos/form.php') ?>" class="btn btn-success"><i class="bi bi-plus-lg"></i> Cadastrar curso</a>
</div>

<form class="mb-3" method="get">
    <div class="input-group">
        <input type="search" name="q" aria-label="Buscar" value="<?= h($busca) ?>" class="form-control" placeholder="Nome, código ou coordenador">
        <button class="btn btn-outline-secondary" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="card">
<?php if (!$cursos): ?>
    <div class="card-body text-body-secondary"><?= $busca === '' ? 'Nenhum curso cadastrado.' : 'Nenhum resultado para “' . h($busca) . '”.' ?></div>
<?php else: ?>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Lista de cursos">
        <table class="table table-hover mb-0">
            <thead>
            <tr><th>Código</th><th>Nome</th><th>Modalidade</th><th>Turno</th><th class="text-center">Carga</th><th class="text-center">Vagas</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach ($cursos as $c):
                $lotado = $c['matriculados'] >= $c['vagas']; ?>
                <tr>
                    <td><?= h($c['codigo']) ?></td>
                    <td><a href="<?= url('cursos/ver.php?id=' . $c['_id']) ?>"><?= h($c['nome']) ?></a></td>
                    <td><?= h($c['modalidade']) ?></td>
                    <td><?= h($c['turno']) ?></td>
                    <td class="text-center"><?= (int) $c['carga_horaria'] ?>h</td>
                    <td class="text-center">
                        <span class="badge <?= $lotado ? 'text-bg-danger' : 'text-bg-success' ?>"><?= $c['matriculados'] ?>/<?= (int) $c['vagas'] ?><?= $lotado ? ' lotado' : '' ?></span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('cursos/ver.php?id=' . $c['_id']) ?>" title="Ver"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= url('cursos/form.php?id=' . $c['_id']) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="<?= url('cursos/excluir.php') ?>"
                              data-confirmar="Excluir o curso <?= h($c['nome']) ?> e todas as matrículas nele?">
                            <input type="hidden" name="id" value="<?= $c['_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Excluir" aria-label="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
