<?php
/**
 * Seletor de status de uma matrícula. O envio é pelo botão, nunca ao trocar a opção.
 * @var array  $m      matrícula atual
 * Usa $voltarPara se definido; senão, volta para a página do estudante.
 */
$destino = $voltarPara ?? 'estudante';
?>
<form method="post" action="<?= url('matriculas/status.php') ?>">
    <input type="hidden" name="id" value="<?= $m['_id'] ?>">
    <input type="hidden" name="voltar" value="<?= h($destino) ?>">
    <div class="input-group flex-nowrap">
        <select name="status" class="form-select" aria-label="Status da matrícula">
            <?php foreach (STATUS_MATRICULA as $s): ?>
                <option <?= $m['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline-secondary" title="Salvar status" aria-label="Salvar status"><i class="bi bi-check-lg" aria-hidden="true"></i></button>
    </div>
</form>
