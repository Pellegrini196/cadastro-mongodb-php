<?php
require __DIR__ . '/../../config/database.php';

$id = objectId($_GET['id'] ?? null);
$curso = [];

if (isset($_GET['id'])) {
    $curso = $id ? db()->cursos->findOne(['_id' => $id]) : null;
    if (!$curso) {
        flash('warning', 'Curso não encontrado.');
        redirecionar('cursos/index.php');
    }
}

$old = pegarFormulario();
$v = fn(string $campo) => $old[$campo] ?? ($curso[$campo] ?? '');

$editando = (bool) $id;
$titulo = $editando ? 'Editar curso' : 'Novo curso';

require __DIR__ . '/../../templates/header.php';
?>
<h1 class="h3 mb-3"><?= $titulo ?></h1>

<form method="post" action="<?= url('cursos/salvar.php') ?>" class="card card-body">
    <?php if ($editando): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="codigo">Código</label>
            <input class="form-control text-uppercase" id="codigo" <?= inv('codigo') ?> name="codigo" required maxlength="20" placeholder="ADS-01" value="<?= h($v('codigo')) ?>">
        </div>
        <div class="col-md-9">
            <label class="form-label" for="nome">Nome do curso</label>
            <input class="form-control" id="nome" <?= inv('nome') ?> name="nome" required maxlength="120" value="<?= h($v('nome')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="descricao">Descrição</label>
            <textarea class="form-control" id="descricao" <?= inv('descricao') ?> name="descricao" rows="3" required><?= h($v('descricao')) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="coordenador">Coordenador(a)</label>
            <input class="form-control" id="coordenador" <?= inv('coordenador') ?> name="coordenador" required value="<?= h($v('coordenador')) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="carga_horaria">Carga horária (h)</label>
            <input type="number" min="1" class="form-control" id="carga_horaria" <?= inv('carga_horaria') ?> name="carga_horaria" required value="<?= h($v('carga_horaria')) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="vagas">Vagas</label>
            <input type="number" min="1" class="form-control" id="vagas" <?= inv('vagas') ?> name="vagas" required value="<?= h($v('vagas')) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="modalidade">Modalidade</label>
            <select class="form-select" id="modalidade" <?= inv('modalidade') ?> name="modalidade" required>
                <?php foreach (MODALIDADES as $op): ?>
                    <option <?= $v('modalidade') === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="turno">Turno</label>
            <select class="form-select" id="turno" <?= inv('turno') ?> name="turno" required>
                <?php foreach (TURNOS as $op): ?>
                    <option <?= $v('turno') === $op ? 'selected' : '' ?>><?= $op ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-principal">Salvar curso</button>
        <a href="<?= url($editando ? 'cursos/ver.php?id=' . $id : 'cursos/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
