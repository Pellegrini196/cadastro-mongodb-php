<?php
require __DIR__ . '/../../config/database.php';
exigirPost();

$id = objectId($_POST['id'] ?? null);

$dados = [
    'codigo'        => strtoupper(campo('codigo')),
    'nome'          => campo('nome'),
    'descricao'     => campo('descricao'),
    'coordenador'   => campo('coordenador'),
    'carga_horaria' => (int) campo('carga_horaria'),
    'vagas'         => (int) campo('vagas'),
    'modalidade'    => campo('modalidade'),
    'turno'         => campo('turno'),
];

// Cada erro fica ligado ao id do campo, para o formulário marcá-lo e o aviso apontar para ele
$erros = [];
if ($dados['codigo'] === '')                         $erros['codigo'] = 'Informe o código do curso.';
if (mb_strlen($dados['nome']) < 3)                   $erros['nome'] = 'Informe o nome do curso.';
if ($dados['descricao'] === '')                      $erros['descricao'] = 'Informe a descrição.';
if ($dados['coordenador'] === '')                    $erros['coordenador'] = 'Informe o coordenador.';
if ($dados['carga_horaria'] < 1)                     $erros['carga_horaria'] = 'Carga horária deve ser maior que zero.';
if ($dados['vagas'] < 1)                             $erros['vagas'] = 'Número de vagas deve ser maior que zero.';
if (!in_array($dados['modalidade'], MODALIDADES, true)) $erros['modalidade'] = 'Modalidade inválida.';
if (!in_array($dados['turno'], TURNOS, true))        $erros['turno'] = 'Turno inválido.';

// Não deixa reduzir as vagas abaixo do número de matrículas ativas
if ($id && !$erros) {
    $ativas = db()->matriculas->countDocuments(['curso_id' => $id, 'status' => 'Ativa']);
    if ($dados['vagas'] < $ativas) {
        $erros['vagas'] = "O curso já tem $ativas matrícula(s) ativa(s); as vagas não podem ser menores que isso.";
    }
}

$voltar = 'cursos/form.php' . ($id ? '?id=' . $id : '');

if ($erros) {
    erroDeFormulario($erros);
    guardarFormulario($dados);
    redirecionar($voltar);
}

$dados['atualizado_em'] = agora();

try {
    if ($id) {
        $resultado = db()->cursos->updateOne(['_id' => $id], ['$set' => $dados]);
        if ($resultado->getMatchedCount() === 0) {
            flash('warning', 'Curso não encontrado.');
            redirecionar('cursos/index.php');
        }
        flash('success', 'Curso atualizado.');
    } else {
        $dados['criado_em'] = agora();
        $id = db()->cursos->insertOne($dados)->getInsertedId();
        flash('success', 'Curso cadastrado.');
    }
} catch (\Throwable $e) {
    $unicos = ['codigo' => 'código'];
    $msg = mensagemDuplicado($e, $unicos);
    if ($msg === null) throw $e;
    $campo = campoDuplicado($e, $unicos);
    erroDeFormulario($campo ? [$campo => $msg] : []);
    if (!$campo) flash('danger', $msg);
    guardarFormulario($dados);
    redirecionar($voltar);
}

redirecionar('cursos/ver.php?id=' . $id);
