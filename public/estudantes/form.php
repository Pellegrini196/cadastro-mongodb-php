<?php
require __DIR__ . '/../../config/database.php';

$id = objectId($_GET['id'] ?? null);
$estudante = [];

if (isset($_GET['id'])) {
    $estudante = $id ? db()->estudantes->findOne(['_id' => $id]) : null;
    if (!$estudante) {
        flash('warning', 'Estudante não encontrado.');
        redirecionar('estudantes/index.php');
    }
}

// Se voltou de um erro de validação, reaproveita o que foi digitado
$old = pegarFormulario();
$v = fn(string $campo) => $old[$campo]
    ?? ($campo === 'data_nascimento'
        ? formatarData($estudante['data_nascimento'] ?? null, 'Y-m-d')
        : ($estudante[$campo] ?? ''));
$end = fn(string $campo) => $old['endereco'][$campo] ?? ($estudante['endereco'][$campo] ?? '');

$editando = (bool) $id;
$titulo = $editando ? 'Editar estudante' : 'Novo estudante';

require __DIR__ . '/../../templates/header.php';
?>
<h1 class="h3 mb-3"><?= $titulo ?></h1>

<form method="post" action="<?= url('estudantes/salvar.php') ?>" class="card card-body">
    <?php if ($editando): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <fieldset class="mb-4">
        <legend>Dados pessoais</legend>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="ra">RA (registro acadêmico)</label>
                <input class="form-control" id="ra" <?= inv('ra') ?> name="ra" required maxlength="20" value="<?= h($v('ra')) ?>">
            </div>
            <div class="col-md-9">
                <label class="form-label" for="nome">Nome completo</label>
                <input class="form-control" id="nome" <?= inv('nome') ?> name="nome" required maxlength="120" value="<?= h($v('nome')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cpf">CPF</label>
                <input class="form-control" id="cpf" <?= inv('cpf') ?> name="cpf" inputmode="numeric" required placeholder="000.000.000-00" value="<?= h($v('cpf') ? formatarCpf($v('cpf')) : '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="data_nascimento">Data de nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" <?= inv('data_nascimento') ?> name="data_nascimento" required value="<?= h($v('data_nascimento')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="telefone">Telefone</label>
                <input class="form-control" id="telefone" <?= inv('telefone') ?> name="telefone" type="tel" required placeholder="(11) 99999-9999" value="<?= h($v('telefone')) ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" class="form-control" id="email" <?= inv('email') ?> name="email" required value="<?= h($v('email')) ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="mb-4">
        <legend>Endereço</legend>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="cep">CEP</label>
                <input class="form-control" id="cep" <?= inv('cep') ?> name="endereco[cep]" inputmode="numeric" required placeholder="00000-000" value="<?= h($end('cep')) ?>">
                <div class="form-text" id="cep-status" role="status"></div>
            </div>
            <div class="col-md-7">
                <label class="form-label" for="logradouro">Logradouro</label>
                <input class="form-control" id="logradouro" <?= inv('logradouro') ?> name="endereco[logradouro]" required value="<?= h($end('logradouro')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="numero">Número</label>
                <input class="form-control" id="numero" <?= inv('numero') ?> name="endereco[numero]" required value="<?= h($end('numero')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="complemento">Complemento</label>
                <input class="form-control" id="complemento" <?= inv('complemento') ?> name="endereco[complemento]" value="<?= h($end('complemento')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="bairro">Bairro</label>
                <input class="form-control" id="bairro" <?= inv('bairro') ?> name="endereco[bairro]" required value="<?= h($end('bairro')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="cidade">Cidade</label>
                <input class="form-control" id="cidade" <?= inv('cidade') ?> name="endereco[cidade]" required value="<?= h($end('cidade')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="uf">UF</label>
                <select class="form-select" id="uf" <?= inv('uf') ?> name="endereco[uf]" required>
                    <option value="">--</option>
                    <?php foreach (UFS as $uf): ?>
                        <option <?= $end('uf') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <div class="d-flex gap-2">
        <button class="btn btn-success"><i class="bi bi-check-lg"></i> Salvar</button>
        <a href="<?= url($editando ? 'estudantes/ver.php?id=' . $id : 'estudantes/index.php') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<?php
// Busca o endereço pelo CEP na API pública ViaCEP
$scripts = <<<'JS'
<script>
document.getElementById('cep').addEventListener('blur', async (ev) => {
    const cep = ev.target.value.replace(/\D/g, '');
    const status = document.getElementById('cep-status');
    if (cep.length !== 8) return;
    status.textContent = 'Buscando...';
    try {
        const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const d = await r.json();
        if (d.erro) { status.textContent = 'CEP não encontrado.'; return; }
        document.getElementById('logradouro').value = d.logradouro || '';
        document.getElementById('bairro').value = d.bairro || '';
        document.getElementById('cidade').value = d.localidade || '';
        document.getElementById('uf').value = d.uf || '';
        status.textContent = '';
        document.getElementById('numero').focus();
    } catch { status.textContent = 'Não foi possível consultar o CEP.'; }
});
</script>
JS;
require __DIR__ . '/../../templates/footer.php';
