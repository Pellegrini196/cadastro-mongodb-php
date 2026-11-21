</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pede confirmação antes de enviar formulários de exclusão.
// Sem JavaScript, o servidor mostra uma página de confirmação (veja exigirConfirmacao em src/helpers.php).
document.querySelectorAll('form[data-confirmar]').forEach(f =>
    f.addEventListener('submit', e => {
        if (!confirm(f.dataset.confirmar)) { e.preventDefault(); return; }
        const ok = document.createElement('input');
        ok.type = 'hidden'; ok.name = 'confirmado'; ok.value = '1';
        f.appendChild(ok);
    })
);
</script>
<script>
// Enquanto salva, o botão fica bloqueado contra clique duplo
document.querySelectorAll('form').forEach(f =>
    f.addEventListener('submit', e => {
        if (e.defaultPrevented) return;
        const b = f.querySelector('button:not([type=button])');
        if (!b) return;
        const texto = b.textContent.trim();
        setTimeout(() => { b.disabled = true; if (texto) b.textContent = 'Salvando...'; }, 0);
        window.addEventListener('pageshow', () => { b.disabled = false; if (texto) b.textContent = texto; }, { once: true });
    })
);
</script>
<?php $primeiroErro = camposInvalidos()[0] ?? null; ?>
<?php if ($primeiroErro): ?>
<script>document.getElementById(<?= json_encode($primeiroErro) ?>)?.focus();</script>
<?php endif; ?>
<?php if (!empty($scripts)) echo $scripts; ?>
</body>
</html>
