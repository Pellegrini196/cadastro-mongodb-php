<?php
require __DIR__ . '/../../config/database.php';

$titulo = 'Estudantes';
$busca  = trim($_GET['q'] ?? '');

$filtro = [];
if ($busca !== '') {
    // Busca parcial, sem diferenciar maiúsculas/minúsculas
    $regex = new MongoDB\BSON\Regex(preg_quote($busca), 'i');
    $filtro = ['$or' => [
        ['nome' => $regex], ['email' => $regex], ['ra' => $regex],
        ['cpf' => $regex], ['endereco.cidade' => $regex],
    ]];
}

// $lookup na coleção matriculas para contar em quantos cursos cada estudante está
$estudantes = db()->estudantes->aggregate([
    ['$match' => (object) $filtro],
    ['$sort' => ['nome' => 1]],
    ['$lookup' => [
        'from' => 'matriculas',
        'localField' => '_id',
        'foreignField' => 'estudante_id',
        'as' => 'matriculas',
    ]],
    ['$addFields' => ['total_cursos' => ['$size' => '$matriculas']]],
    ['$project' => ['matriculas' => 0]],
])->toArray();

require __DIR__ . '/../../templates/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Estudantes</h1>
    <a href="<?= url('estudantes/form.php') ?>" class="btn btn-success"><i class="bi bi-plus-lg"></i> Cadastrar estudante</a>
</div>

<form class="mb-3" method="get">
    <div class="input-group">
        <input type="search" name="q" aria-label="Buscar" value="<?= h($busca) ?>" class="form-control" placeholder="Nome, RA, CPF, e-mail ou cidade">
        <button class="btn btn-outline-secondary" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="card">
<?php if (!$estudantes): ?>
    <div class="card-body text-body-secondary"><?= $busca === '' ? 'Nenhum estudante cadastrado.' : 'Nenhum resultado para “' . h($busca) . '”.' ?></div>
<?php else: ?>
    <div class="table-responsive" tabindex="0" role="region" aria-label="Lista de estudantes">
        <table class="table table-hover mb-0">
            <thead>
            <tr><th>RA</th><th>Nome</th><th>E-mail</th><th>Cidade/UF</th><th class="text-center">Cursos</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach ($estudantes as $e): ?>
                <tr>
                    <td><?= h($e['ra']) ?></td>
                    <td><a href="<?= url('estudantes/ver.php?id=' . $e['_id']) ?>"><?= h($e['nome']) ?></a></td>
                    <td><?= h($e['email']) ?></td>
                    <td><?= h(($e['endereco']['cidade'] ?? '') . '/' . ($e['endereco']['uf'] ?? '')) ?></td>
                    <td class="text-center"><span class="badge text-bg-success"><?= $e['total_cursos'] ?></span></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('estudantes/ver.php?id=' . $e['_id']) ?>" title="Ver"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= url('estudantes/form.php?id=' . $e['_id']) ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="<?= url('estudantes/excluir.php') ?>"
                              data-confirmar="Excluir <?= h($e['nome']) ?> e todas as suas matrículas?">
                            <input type="hidden" name="id" value="<?= $e['_id'] ?>">
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
