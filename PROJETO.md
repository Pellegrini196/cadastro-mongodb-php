# Faculdade - Cadastro MongoDB e PHP

Atualizado em 21/11/2026.

## 1. O que é

Trabalho da disciplina Desenvolvimento de Aplicações para WEB I (Atividade 3, 1,5 pt, professor Carlos Eduardo Simões Pelegrin, enunciado de 04/11/2026). Entrega no GitHub, equipe de até 2 pessoas.

Pede: cadastro de Estudante com mais de 5 campos e endereço com 5 campos ou mais; cadastro de Curso com no mínimo 5 campos; um estudante em vários cursos e um curso com vários estudantes; dados no MongoDB (nuvem ou local); relacionamento sem colocar tudo em documento embutido; Bootstrap ou Tailwind permitidos.

## 2. Como funciona

Três coleções no MongoDB:

- `estudantes`: dados pessoais e `endereco` embutido (relação 1:1).
- `cursos`: dados do curso.
- `matriculas`: `estudante_id`, `curso_id` (ObjectId), `data_matricula`, `status`. Faz a relação N:N.

As telas juntam as coleções com `aggregate` e `$lookup`. Índices únicos em CPF, e-mail, RA, código do curso e no par (estudante, curso). Vagas contam só matrículas ativas. Excluir estudante ou curso apaga as matrículas ligadas.

## 3. Tecnologias e versões

- PHP 8.3.37 (o projeto exige 8.1 ou mais) e extensão `mongodb` 1.21.14
- Biblioteca `mongodb/mongodb` (Composer; o `composer.json` aceita ^1.15 ou ^2.0, instalada a 1.21.6) e Composer 2.11.2
- MongoDB 7.0.28 local (mesma versão do `docker-compose.yml`); o Atlas também funciona
- Bootstrap 5.3.3 e Bootstrap Icons 1.11.3 por CDN, lendo os tokens de `public/assets/app.css`
- Fontes Sora (títulos) e Source Sans 3 (corpo) pelo Google Fonts; identidade completa em `DESIGN.md`
- ViaCEP (consulta de CEP no navegador)

## 4. Onde está

- Pasta no PC: `D:\ClaudeCode\cadastro-mongodb-php`.
- Ambiente neste PC (instalado em 21/11/2026 pelo winget): PHP 8.3 NTS em `%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe` (com `php.ini`, a DLL da extensão `mongodb` e o `composer.phar` com `composer.bat`) e o serviço do Windows `MongoDB` (porta 27017, sem usuário e senha, só acessível de `localhost`).
- Repositório GitHub (público, criado em 21/11/2026): https://github.com/Pellegrini196/cadastro-mongodb-php

## 5. Como rodar, testar e publicar

```
composer install
cp .env.example .env        # ajustar MONGODB_URI
php scripts/seed.php        # dados de exemplo (opcional)
php -S localhost:8000 -t public
```

Testes: não há testes automatizados no repositório. Em 21/11/2026 rodei um roteiro de ponta a ponta (44 verificações) que fala HTTP com o servidor `php -S` e confere o MongoDB: validação, cadastro, edição, CPF, e-mail, RA e código duplicados, matrícula repetida, curso lotado, troca de status, reativação sem vaga, exclusão com e sem confirmação e exclusão em cascata. Passou tudo. O roteiro não ficou no repositório. Também conferida a sintaxe de todos os arquivos (`php -l`). O detector de design (`impeccable detect` contra o servidor rodando, página por página, porque em `.php` ele só lê o texto) deu 0 achados nas 7 telas; a medição de área de clique (230 alvos) e de estouro de largura em 320, 1100 e 1200 px também está limpa.

Publicar: subir o código para um repositório GitHub e colar o link na atividade do Classroom. Commit e push só quando o Murilo pedir.

## 6. Situação atual

Funcionando no PC contra um MongoDB 7 local, com o roteiro de ponta a ponta passando. Painel, listas, detalhes, gravação, índices únicos e `$lookup` testados em dados reais. Interface revisada em 20/11/2026 com better-interface e, em 21/11/2026, com os três itens que ficaram abertos resolvidos (veja a seção 8). Layout conferido em 320, 1100 e 1200 px: nenhuma página estoura a largura; em 320 px as tabelas rolam por dentro, com sombra indicando o lado com mais conteúdo.

Identidade visual aplicada em 21/11/2026 (`DESIGN.md`): paleta análoga petróleo e verde-azulado, Sora e Source Sans 3, régua de vagas como assinatura, sem semáforo. Formulário de estudante com máscara de CPF, telefone e CEP, "(opcional)" no complemento, botão que bloqueia clique duplo e estados vazios com ação.

Ainda não testado: MongoDB Atlas (só o local) e `docker compose up -d`.

## 7. Próximos passos

1. Enviar o link do repositório no Classroom (commit e push só quando o Murilo pedir). O trabalho é individual (definido em 21/11/2026), e o README já traz só o nome dele.
2. Opcional: testar com um cluster do Atlas, se o professor exigir banco na nuvem.
3. Itens das listas Formulários e Tabelas do Notion que ainda não foram feitos: paginação com total ("1 a 50 de N"), ordenação clicando no cabeçalho, cabeçalho fixo ao rolar, ações por linha mais discretas, validação ao sair do campo, aviso ao sair com alterações não salvas, estado de carregamento nas listas. Não impedem o uso; entram se o professor pedir ou o Murilo quiser.

## 8. Decisões e motivos

- Endereço embutido no estudante; cursos por referência em `matriculas`. Motivo: o enunciado proíbe tudo embutido, e o endereço pertence a um só estudante.
- PHP puro, sem framework. Motivo: o enunciado indica a biblioteca oficial do MongoDB para PHP e o trabalho é pequeno.
- Bootstrap por CDN. Motivo: o enunciado permite e evita etapa de build. Exige internet para carregar o estilo.
- Status da matrícula (Ativa, Trancada, Concluída, Cancelada). Motivo: a matrícula tem dados próprios, o que justifica a coleção separada.
- O status só muda ao clicar em "Salvar status". Motivo: enviar o formulário ao mudar a opção quebra a navegação por teclado (WCAG 3.2.2).
- Erros de formulário apontam para o campo: cada aviso do topo é um link para o campo, o campo ganha `aria-invalid="true"` e borda vermelha, e o foco vai para o primeiro. Motivo: leitor de tela e teclado precisam saber qual campo corrigir (`erroDeFormulario` e `inv` em `src/helpers.php`).
- Exclusão sem JavaScript abre uma página de confirmação (`exigirConfirmacao` em `src/helpers.php`); com JavaScript, o navegador pergunta e envia `confirmado=1`. Motivo: sem JavaScript a exclusão era imediata, sem pergunta.
- Logotipo do Gemini vetorizado (`public/assets/logo.svg`), no cabeçalho e no favicon (21/11/2026). Motivo: o Murilo quis a logo igual à do Gemini; o JPG não serve no site (fundo não transparente, cor manchada), então o desenho foi vetorizado com cores chapadas dos tokens. Detalhes em `DESIGN.md`.
- Nome do sistema: Matriculum (21/11/2026), no cabeçalho, no título das páginas, no README e no `DESIGN.md`. Motivo: escolha do Murilo entre quatro opções; junta matrícula e curriculum. O repositório e a pasta seguem como `cadastro-mongodb-php`.
- Identidade A, "Institucional e calma" (`DESIGN.md`): tokens por função, Sora e Source Sans 3, assinatura na régua de vagas. Motivo: regra de Cores e fontes do Notion (paleta e fontes escolhidas de propósito, nunca o padrão do Bootstrap), escolhida pelo Murilo entre três opções.
- Espaçamento na escala de 4 e 8 (raiz de 16 px, corpo de 17 px) e 16 px de respiro nas células das tabelas. Motivo: a primeira versão tinha 8,5 px nas células, texto colado na borda da caixa e valores como 6,4 e 12,8 px fora da escala do Notion.
- Sem semáforo: status em texto neutro, sucesso sem cor, só o erro colorido. Motivo: regra "Nada de semáforo" do Notion; antes havia etiquetas verdes e vermelhas em cada linha.
- Sem ícone decorativo: ficam só os botões que são só ícone (ver, editar, excluir, buscar, salvar status), todos com nome acessível que cita o item. Motivo: regra de Interface do Notion (Boas práticas, seção 3).
- Contraste mínimo de 4,5:1 e área de clique de 40 px em todos os botões e campos. Motivo: o detector do impeccable achou os botões de contorno do Bootstrap em 4,3:1, e o seletor de status tinha 32 px.
- Texto longo sem espaço (e-mail, código) quebra em vez de estourar a tela; colunas curtas (RA, datas, vagas) e cabeçalhos não quebram. Motivo: teste com dados extremos (nome de 120 caracteres, e-mail de 200 sem espaço, emoji, 1 milhão de vagas) estourava 560 px em 320 px.
- Tabelas largas viram região com foco por teclado (`tabindex="0"`, `role="region"` e `aria-label`) e mostram sombra na borda que ainda tem conteúdo. Motivo: em 320 px a rolagem lateral passava despercebida.

## 9. Cuidados

- Nunca versionar o `.env` (já está no `.gitignore`).
- No Atlas, liberar o IP em Network Access, senão a conexão falha.
- Os formulários de exclusão e de gravação aceitam só POST. Não há login: qualquer pessoa com acesso à URL altera os dados.
- CPFs de exemplo do `seed.php` são válidos só no cálculo, não de pessoas reais. Os de teste não podem repetir os do seed (529.982.247-25 e 111.444.777-35), senão o teste de cadastro cai em "CPF duplicado".
- Terminais abertos antes da instalação do PHP não enxergam `php` nem `composer` no PATH; abra um terminal novo.
- O MongoDB local não tem usuário e senha. Serve para desenvolvimento em `localhost`; nunca abrir a porta 27017 para a rede.

## 10. Segredos

A string de conexão do MongoDB (usuário e senha do Atlas, se usar) fica só no `.env` local, fora do Git. Nenhum valor neste documento.
