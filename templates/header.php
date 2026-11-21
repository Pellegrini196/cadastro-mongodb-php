<?php
/** @var string $titulo */
$titulo = $titulo ?? 'Cadastro Acadêmico';
$atual  = $_SERVER['SCRIPT_NAME'] ?? '';
$ativo  = fn(string $parte) => str_contains($atual, $parte) ? 'active' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titulo) ?> · Cadastro Acadêmico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('assets/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-md app-nav border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold text-body" href="<?= url('index.php') ?>">
            <i class="bi bi-database-fill"></i> Cadastro Acadêmico
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?= $ativo('/estudantes/') ?>" href="<?= url('estudantes/index.php') ?>"><i class="bi bi-people"></i> Estudantes</a></li>
                <li class="nav-item"><a class="nav-link <?= $ativo('/cursos/') ?>" href="<?= url('cursos/index.php') ?>"><i class="bi bi-journal-bookmark"></i> Cursos</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container pb-5">
<?php foreach (pegarFlash() as $f): ?>
    <div class="alert alert-<?= h($f['tipo']) ?> alert-dismissible fade show" role="<?= in_array($f['tipo'], ['danger', 'warning'], true) ? 'alert' : 'status' ?>">
        <?php if (!empty($f['campo'])): ?>
            <a class="alert-link" href="#<?= h($f['campo']) ?>"><?= h($f['mensagem']) ?></a>
        <?php else: ?>
            <?= h($f['mensagem']) ?>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endforeach; ?>
