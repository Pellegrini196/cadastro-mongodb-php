<?php
/**
 * Conexão com o MongoDB.
 *
 * Coleções usadas:
 *  - estudantes : dados do estudante + endereço (documento embutido, relação 1:1)
 *  - cursos     : dados do curso
 *  - matriculas : relação N:N entre estudantes e cursos (referências por ObjectId)
 */

use MongoDB\Client;
use MongoDB\Database;

require_once __DIR__ . '/../vendor/autoload.php';

/** Lê o arquivo .env (formato CHAVE=valor) sem dependências externas. */
function carregarEnv(string $arquivo): void
{
    if (!is_file($arquivo)) {
        return;
    }
    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) {
            continue;
        }
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim(trim($valor), "\"'");
        if (getenv($chave) === false) {
            putenv("$chave=$valor");
        }
    }
}

carregarEnv(__DIR__ . '/../.env');

// Erros inesperados (ex.: MongoDB fora do ar) viram uma página amigável
if (PHP_SAPI !== 'cli') {
    set_exception_handler(function (\Throwable $e) {
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
            . '<div class="container py-5"><div class="alert alert-danger"><h1 class="h5">Ocorreu um erro</h1>'
            . '<p class="mb-1">Verifique se o MongoDB está rodando e se o <code>.env</code> está correto.</p>'
            . '<small>' . htmlspecialchars($e->getMessage()) . '</small></div>'
            . '<a href="javascript:history.back()">Voltar</a></div>';
    });
}

function db(): Database
{
    static $db = null;
    if ($db === null) {
        $uri  = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
        $nome = getenv('MONGODB_DB') ?: 'faculdade';
        $cliente = new Client($uri, [], [
            // Devolve documentos como arrays PHP, mais simples de usar nas views
            'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
        ]);
        $db = $cliente->selectDatabase($nome);
        criarIndices($db);
    }
    return $db;
}

/**
 * Índices únicos garantem a integridade:
 *  - não repetir CPF / e-mail / RA de estudante nem código de curso
 *  - um estudante não pode se matricular duas vezes no mesmo curso
 * createIndex é idempotente: se o índice já existir, nada muda.
 */
function criarIndices(Database $db): void
{
    $db->estudantes->createIndex(['cpf' => 1], ['unique' => true]);
    $db->estudantes->createIndex(['email' => 1], ['unique' => true]);
    $db->estudantes->createIndex(['ra' => 1], ['unique' => true]);
    $db->cursos->createIndex(['codigo' => 1], ['unique' => true]);
    $db->matriculas->createIndex(['estudante_id' => 1, 'curso_id' => 1], ['unique' => true]);
    $db->matriculas->createIndex(['curso_id' => 1]);
}
