<?php
require __DIR__ . '/../../config/database.php';
exigirPost();

$id = objectId($_POST['id'] ?? null);

$endereco = [
    'cep'         => trim($_POST['endereco']['cep'] ?? ''),
    'logradouro'  => trim($_POST['endereco']['logradouro'] ?? ''),
    'numero'      => trim($_POST['endereco']['numero'] ?? ''),
    'complemento' => trim($_POST['endereco']['complemento'] ?? ''),
    'bairro'      => trim($_POST['endereco']['bairro'] ?? ''),
    'cidade'      => trim($_POST['endereco']['cidade'] ?? ''),
    'uf'          => strtoupper(trim($_POST['endereco']['uf'] ?? '')),
];

$dados = [
    'ra'              => campo('ra'),
    'nome'            => campo('nome'),
    'cpf'             => somenteDigitos(campo('cpf')),
    'email'           => strtolower(campo('email')),
    'telefone'        => campo('telefone'),
    'data_nascimento' => campo('data_nascimento'),
    'endereco'        => $endereco,
];

// ---------- Validação ----------
// Cada erro fica ligado ao id do campo, para o formulário marcá-lo e o aviso apontar para ele
$erros = [];
if ($dados['ra'] === '')                           $erros['ra'] = 'Informe o RA.';
if (mb_strlen($dados['nome']) < 3)                 $erros['nome'] = 'Informe o nome completo.';
if (!cpfValido($dados['cpf']))                     $erros['cpf'] = 'CPF inválido. Digite os 11 números do CPF.';
if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido. Use o formato nome@exemplo.com.';
if (strlen(somenteDigitos($dados['telefone'])) < 10)     $erros['telefone'] = 'Telefone inválido. Informe o DDD e o número.';
$nascimento = dataParaMongo($dados['data_nascimento']);
if (!$nascimento || $dados['data_nascimento'] > date('Y-m-d')) $erros['data_nascimento'] = 'Data de nascimento inválida. Informe uma data que já passou.';

if (strlen(somenteDigitos($endereco['cep'])) !== 8) $erros['cep'] = 'CEP inválido. Digite os 8 números do CEP.';
foreach (['logradouro' => 'o logradouro', 'numero' => 'o número', 'bairro' => 'o bairro', 'cidade' => 'a cidade'] as $c => $rotulo) {
    if ($endereco[$c] === '') $erros[$c] = "Informe $rotulo do endereço.";
}
if (!in_array($endereco['uf'], UFS, true))         $erros['uf'] = 'Selecione a UF.';

$voltar = 'estudantes/form.php' . ($id ? '?id=' . $id : '');

if ($erros) {
    erroDeFormulario($erros);
    guardarFormulario($dados);
    redirecionar($voltar);
}

// ---------- Monta o documento ----------
// O endereço fica EMBUTIDO no estudante (relação 1:1, sempre lido junto).
// Já os cursos ficam em outra coleção e o vínculo é feito pela coleção "matriculas".
$documento = $dados;
$documento['data_nascimento'] = $nascimento;
$documento['endereco']['cep'] = somenteDigitos($endereco['cep']);
$documento['atualizado_em']   = agora();

try {
    if ($id) {
        $resultado = db()->estudantes->updateOne(['_id' => $id], ['$set' => $documento]);
        if ($resultado->getMatchedCount() === 0) {
            flash('warning', 'Estudante não encontrado.');
            redirecionar('estudantes/index.php');
        }
        flash('success', 'Estudante atualizado.');
    } else {
        $documento['criado_em'] = agora();
        $id = db()->estudantes->insertOne($documento)->getInsertedId();
        flash('success', 'Estudante cadastrado. Agora você pode matriculá-lo em cursos.');
    }
} catch (\Throwable $e) {
    $unicos = ['cpf' => 'CPF', 'email' => 'e-mail', 'ra' => 'RA'];
    $msg = mensagemDuplicado($e, $unicos);
    if ($msg === null) throw $e;
    $campo = campoDuplicado($e, $unicos);
    erroDeFormulario($campo ? [$campo => $msg] : []);
    if (!$campo) flash('danger', $msg);
    guardarFormulario($dados);
    redirecionar($voltar);
}

redirecionar('estudantes/ver.php?id=' . $id);
