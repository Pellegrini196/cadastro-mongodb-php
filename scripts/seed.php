<?php
/**
 * Popula o banco com dados de exemplo.
 * Uso: php scripts/seed.php        (adiciona se ainda não existir)
 *      php scripts/seed.php --limpar (apaga tudo antes)
 */
require __DIR__ . '/../config/database.php';

if (in_array('--limpar', $argv, true)) {
    db()->matriculas->deleteMany([]);
    db()->estudantes->deleteMany([]);
    db()->cursos->deleteMany([]);
    echo "Coleções limpas.\n";
}

$cursos = [
    ['codigo' => 'ADS-101', 'nome' => 'Desenvolvimento Web com PHP', 'descricao' => 'HTML, CSS, PHP e acesso a banco de dados.', 'coordenador' => 'Carla Mendes', 'carga_horaria' => 80, 'vagas' => 30, 'modalidade' => 'Presencial', 'turno' => 'Noturno'],
    ['codigo' => 'BD-201', 'nome' => 'Banco de Dados NoSQL', 'descricao' => 'Modelagem de documentos com MongoDB.', 'coordenador' => 'Ana Ribeiro', 'carga_horaria' => 60, 'vagas' => 25, 'modalidade' => 'Híbrido', 'turno' => 'Noturno'],
    ['codigo' => 'SEG-301', 'nome' => 'Segurança da Informação', 'descricao' => 'Fundamentos de segurança, criptografia e pentest.', 'coordenador' => 'Marcos Lima', 'carga_horaria' => 72, 'vagas' => 20, 'modalidade' => 'EAD', 'turno' => 'Integral'],
];

$estudantes = [
    ['ra' => '2024001', 'nome' => 'Ana Souza', 'cpf' => '52998224725', 'email' => 'ana.souza@exemplo.com', 'telefone' => '(11) 98888-1111', 'data_nascimento' => '2003-04-12',
     'endereco' => ['cep' => '01310100', 'logradouro' => 'Avenida Paulista', 'numero' => '1000', 'complemento' => 'Apto 12', 'bairro' => 'Bela Vista', 'cidade' => 'São Paulo', 'uf' => 'SP']],
    ['ra' => '2024002', 'nome' => 'Bruno Lima', 'cpf' => '11144477735', 'email' => 'bruno.lima@exemplo.com', 'telefone' => '(19) 97777-2222', 'data_nascimento' => '2002-09-30',
     'endereco' => ['cep' => '13010111', 'logradouro' => 'Rua Barão de Jaguara', 'numero' => '55', 'complemento' => '', 'bairro' => 'Centro', 'cidade' => 'Campinas', 'uf' => 'SP']],
];

$idsCursos = [];
foreach ($cursos as $c) {
    $c['criado_em'] = $c['atualizado_em'] = agora();
    // upsert: insere só se o código ainda não existir
    db()->cursos->updateOne(['codigo' => $c['codigo']], ['$setOnInsert' => $c], ['upsert' => true]);
    $idsCursos[] = db()->cursos->findOne(['codigo' => $c['codigo']])['_id'];
}

$idsEstudantes = [];
foreach ($estudantes as $e) {
    $e['data_nascimento'] = dataParaMongo($e['data_nascimento']);
    $e['criado_em'] = $e['atualizado_em'] = agora();
    db()->estudantes->updateOne(['ra' => $e['ra']], ['$setOnInsert' => $e], ['upsert' => true]);
    $idsEstudantes[] = db()->estudantes->findOne(['ra' => $e['ra']])['_id'];
}

// Ana nos 3 cursos, Bruno em 2 -> demonstra a relação N:N
$vinculos = [[0, 0], [0, 1], [0, 2], [1, 0], [1, 1]];
foreach ($vinculos as [$e, $c]) {
    db()->matriculas->updateOne(
        ['estudante_id' => $idsEstudantes[$e], 'curso_id' => $idsCursos[$c]],
        ['$setOnInsert' => ['data_matricula' => agora(), 'status' => 'Ativa']],
        ['upsert' => true]
    );
}

printf("Pronto: %d cursos, %d estudantes, %d matrículas.\n",
    db()->cursos->countDocuments(), db()->estudantes->countDocuments(), db()->matriculas->countDocuments());
