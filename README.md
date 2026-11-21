# Cadastro acadêmico com PHP e MongoDB

Atividade 3 de Desenvolvimento de Aplicações para WEB I. O sistema cadastra estudantes e cursos em PHP e grava no MongoDB, local ou Atlas. Um estudante pode estar em vários cursos e um curso pode ter vários estudantes. A interface usa Bootstrap 5.

Autor: Murilo Machado dos Santos Pellegrini (trabalho individual).

## Funcionalidades

Estudantes: cadastrar, listar com busca, ver, editar e excluir. Cada estudante tem RA, nome, CPF validado, e-mail, telefone e data de nascimento, mais um endereço com CEP, logradouro, número, complemento, bairro, cidade e UF. O CEP preenche o restante do endereço pela API ViaCEP.

Cursos: cadastrar, listar com busca, ver, editar e excluir. Cada curso tem código, nome, descrição, coordenador, carga horária, vagas, modalidade e turno.

Matrículas: criadas pela página do estudante ou pela do curso. O status pode ser Ativa, Trancada, Concluída ou Cancelada. Só as matrículas ativas ocupam vaga. O sistema recusa a mesma matrícula duas vezes, e excluir um estudante ou um curso apaga as matrículas dele. Toda exclusão pede confirmação, também com o JavaScript desligado.

Formulários: quando há erro, o aviso no topo leva ao campo com problema, que fica marcado em vermelho, e o que foi digitado é mantido.

## Modelagem

O enunciado proíbe colocar tudo em documento embutido. Por isso há três coleções:

- `estudantes`: dados pessoais e o `endereco` embutido, porque o endereço pertence a um só estudante e é lido junto com ele.
- `cursos`: dados do curso.
- `matriculas`: guarda `estudante_id` e `curso_id` (ObjectId), a data e o status. É ela que liga estudantes e cursos, e permite que cada lado tenha vários do outro sem repetir dados.

As telas juntam as coleções com `aggregate` e `$lookup`, que funciona como um JOIN. Exemplo de `public/cursos/ver.php`:

```php
db()->matriculas->aggregate([
    ['$match'  => ['curso_id' => $id]],
    ['$lookup' => ['from' => 'estudantes', 'localField' => 'estudante_id',
                   'foreignField' => '_id', 'as' => 'estudante']],
    ['$unwind' => '$estudante'],
]);
```

Os índices são criados na primeira conexão, em `config/database.php`. São únicos em `estudantes.cpf`, `estudantes.email`, `estudantes.ra` e `cursos.codigo`, e também no par `matriculas.estudante_id` e `matriculas.curso_id`.

## Como rodar

Requisitos: PHP 8.1 ou mais novo com a extensão `mongodb`, o Composer e um MongoDB local ou um cluster gratuito no MongoDB Atlas.

1. Instale a extensão do MongoDB no PHP.
   - Windows com XAMPP: baixe a DLL em https://pecl.php.net/package/mongodb, na versão do seu PHP (Thread Safe, x64). Copie `php_mongodb.dll` para `C:\xampp\php\ext` e adicione `extension=mongodb` no `php.ini`.
   - Linux: `sudo apt install php-mongodb`.
   - Para conferir, rode `php -m` e procure `mongodb` na lista.
2. Instale as dependências com `composer install`.
3. Copie `.env.example` para `.env` e ajuste `MONGODB_URI`. Para MongoDB local use `mongodb://localhost:27017`; com Docker, `docker compose up -d` sobe um servidor. Para o Atlas, cole a connection string do cluster e libere o seu IP em Network Access.
4. Se quiser dados de exemplo, rode `php scripts/seed.php`.
5. Inicie com `php -S localhost:8000 -t public` e abra http://localhost:8000. Dentro do `htdocs` do XAMPP, abra `http://localhost/<pasta>/public/`.

## Estrutura

```
config/database.php     conexão, leitura do .env e índices
src/helpers.php         validação de CPF, datas e mensagens
src/matriculas.php      regras de matrícula
templates/              cabeçalho, rodapé e componentes
public/                 páginas
  index.php             painel
  estudantes/           lista, formulário, gravação, detalhe e exclusão
  cursos/               lista, formulário, gravação, detalhe e exclusão
  matriculas/           criar, mudar status e excluir
scripts/seed.php        dados de exemplo
```

## Referências

- Documentação do MongoDB para PHP: https://www.mongodb.com/pt-br/docs/languages/php/
- Manual do PHP, extensão MongoDB: https://www.php.net/manual/en/book.mongodb.php
