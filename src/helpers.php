<?php
/** Funções utilitárias compartilhadas pelas páginas. */

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}

/** Escapa texto para HTML (evita XSS). */
function h(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Monta uma URL a partir da pasta public/, funcionando com `php -S` ou dentro do htdocs (XAMPP). */
function url(string $caminho = ''): string
{
    static $base = null;
    if ($base === null) {
        $publico = realpath(__DIR__ . '/../public');
        $raiz    = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
        $base    = ($raiz !== '' && str_starts_with($publico, $raiz))
            ? str_replace('\\', '/', substr($publico, strlen($raiz)))
            : '';
        $base = rtrim($base, '/');
    }
    return $base . '/' . ltrim($caminho, '/');
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

/** Mensagem que aparece uma única vez na próxima página. Com $campo, a mensagem vira um link para o campo. */
function flash(string $tipo, string $mensagem, ?string $campo = null): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem, 'campo' => $campo];
}

/**
 * Registra os erros de um formulário: cada mensagem aponta para o campo com problema
 * (['cpf' => 'CPF inválido...']) e o formulário marca esses campos com aria-invalid.
 */
function erroDeFormulario(array $erros): void
{
    foreach ($erros as $campo => $mensagem) {
        flash('danger', $mensagem, (string) $campo);
    }
    $_SESSION['campos_invalidos'] = array_map('strval', array_keys($erros));
}

/** Campos com erro na última tentativa de envio (lidos uma única vez por página). */
function camposInvalidos(): array
{
    static $campos = null;
    if ($campos === null) {
        $campos = $_SESSION['campos_invalidos'] ?? [];
        unset($_SESSION['campos_invalidos']);
    }
    return $campos;
}

/** Atributo para colocar no campo do formulário: marca o campo como inválido para leitores de tela. */
function inv(string $campo): string
{
    return in_array($campo, camposInvalidos(), true) ? 'aria-invalid="true"' : '';
}

/**
 * Exclusão sem JavaScript: se o envio não veio confirmado, mostra uma página pedindo
 * a confirmação (com JavaScript, o navegador já perguntou e envia confirmado=1).
 */
function exigirConfirmacao(string $mensagem, string $cancelar): void
{
    if (($_POST['confirmado'] ?? '') === '1') {
        return;
    }
    $titulo = 'Confirmar exclusão';
    require __DIR__ . '/../templates/header.php';
    ?>
    <h1 class="h3 mb-3">Confirmar exclusão</h1>
    <form method="post" class="card card-body">
        <p class="mb-3"><?= h($mensagem) ?> Esta ação não pode ser desfeita.</p>
        <?php foreach ($_POST as $nome => $valor): ?>
            <?php if (is_string($valor)): ?>
                <input type="hidden" name="<?= h($nome) ?>" value="<?= h($valor) ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        <input type="hidden" name="confirmado" value="1">
        <div class="d-flex gap-2">
            <button class="btn btn-danger">Excluir</button>
            <a href="<?= url($cancelar) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
    <?php
    require __DIR__ . '/../templates/footer.php';
    exit;
}

function pegarFlash(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

/** Converte string em ObjectId; retorna null se for inválida. */
function objectId(?string $id): ?ObjectId
{
    if ($id === null || !preg_match('/^[a-f\d]{24}$/i', $id)) {
        return null;
    }
    return new ObjectId($id);
}

/** Garante que a requisição é POST (ações que alteram dados). */
function exigirPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido');
    }
}

/** "2004-05-17" -> UTCDateTime (tipo Date do MongoDB). */
function dataParaMongo(?string $data): ?UTCDateTime
{
    $dt = DateTime::createFromFormat('!Y-m-d', (string) $data, new DateTimeZone('UTC'));
    return $dt ? new UTCDateTime($dt) : null;
}

/** UTCDateTime -> string formatada. */
function formatarData(mixed $data, string $formato = 'd/m/Y'): string
{
    if ($data instanceof UTCDateTime) {
        $dt = $data->toDateTime();
        // Só converte para o fuso local quando há hora; datas puras (nascimento) ficam em UTC
        if (str_contains($formato, 'H')) {
            $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        }
        return $dt->format($formato);
    }
    return '';
}

/** Percentual de vagas ocupadas (0 a 100), para a régua do curso. Com pelo menos uma matrícula, nunca menos que 2%. */
function percentualOcupacao(int $ocupadas, int $vagas): int
{
    if ($ocupadas <= 0 || $vagas <= 0) {
        return 0;
    }
    return max(2, min(100, (int) round($ocupadas / $vagas * 100)));
}

function agora(): UTCDateTime
{
    return new UTCDateTime();
}

function somenteDigitos(?string $valor): string
{
    return preg_replace('/\D/', '', (string) $valor);
}

function cpfValido(string $cpf): bool
{
    $cpf = somenteDigitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

function formatarCpf(string $cpf): string
{
    $d = somenteDigitos($cpf);
    return strlen($d) === 11
        ? substr($d, 0, 3) . '.' . substr($d, 3, 3) . '.' . substr($d, 6, 3) . '-' . substr($d, 9)
        : $cpf;
}

/** Guarda o que o usuário digitou para reexibir o formulário após um erro. */
function guardarFormulario(array $dados): void
{
    $_SESSION['old'] = $dados;
}

function pegarFormulario(): array
{
    $dados = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    return $dados;
}

/** Lê um campo de texto do POST já sem espaços nas pontas. */
function campo(string $nome): string
{
    return trim((string) ($_POST[$nome] ?? ''));
}

/** Traduz erro de chave duplicada (código 11000) em mensagem amigável. */
function mensagemDuplicado(\Throwable $e, array $nomes): ?string
{
    if ((int) $e->getCode() !== 11000) {
        return null;
    }
    foreach ($nomes as $campo => $rotulo) {
        // A mensagem do MongoDB traz o nome do índice, ex.: "index: cpf_1 dup key"
        if (str_contains($e->getMessage(), "index: {$campo}_")) {
            return "Já existe um cadastro com este $rotulo.";
        }
    }
    return 'Registro duplicado.';
}

/** Descobre qual campo causou o erro de chave duplicada (para apontar o erro no formulário). */
function campoDuplicado(\Throwable $e, array $nomes): ?string
{
    foreach (array_keys($nomes) as $campo) {
        if (str_contains($e->getMessage(), "index: {$campo}_")) {
            return $campo;
        }
    }
    return null;
}

const UFS = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
const MODALIDADES = ['Presencial', 'EAD', 'Híbrido'];
const TURNOS = ['Matutino', 'Vespertino', 'Noturno', 'Integral'];
const STATUS_MATRICULA = ['Ativa', 'Trancada', 'Concluída', 'Cancelada'];
