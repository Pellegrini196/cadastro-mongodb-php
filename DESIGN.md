# Matriculum: identidade visual

Escolhida pelo Murilo em 21/11/2026, entre três opções (A institucional e calma, B técnica e precisa, C acolhedora e acadêmica). Regras de origem: Notion, Preferências › Interface (Cores e fontes, Vícios de IA e assinatura, Formulários, Tabelas e listas).

## Nome

Matriculum, escolhido pelo Murilo em 21/11/2026 entre quatro opções (Matriculum, Registrum, Studium e Academicum). Junta matrícula e curriculum, liga ao que o sistema faz (matricular estudantes em cursos) e soa institucional, no tom da identidade. O repositório e a pasta mantêm o nome antigo, `cadastro-mongodb-php`.

## Personalidade

Institucional e calma. Um sistema de secretaria: poucas cores, muito neutro, a informação em primeiro lugar. Tema claro suave, nunca branco puro.

## Esquema de cores

Análogo (cores vizinhas no círculo): petróleo (matiz 225) como cor principal e verde-azulado (matiz 175) como destaque. Motivo: esquema calmo e institucional, sem tensão entre as cores.

Proporção 60-30-10: neutros cerca de 60%, petróleo 30%, destaque 10% (só o botão principal). Neutros puxados para o petróleo (matiz 205), não cinza puro.

## Tokens

Definidos em `public/assets/app.css`. Nomeados pela função, nunca pela cor. Nas telas, nenhuma cor é escrita à mão: o Bootstrap lê estes tokens.

| Token | OKLCH | Hex | Função |
|---|---|---|---|
| `fundo` | oklch(0.94 0.010 205) | #E4EDEE | Fundo da página |
| `superficie` | oklch(0.97 0.007 205) | #F0F7F7 | Cartões, listas e tabelas |
| `campo` | oklch(0.985 0.004 205) | #F7FBFB | Fundo dos campos |
| `barra` | oklch(0.91 0.014 205) | #D7E4E6 | Barra de navegação |
| `borda` | oklch(0.85 0.012 205) | #C5D0D1 | Estrutura (cartões, linhas) |
| `borda-campo` | oklch(0.58 0.020 215) | #6E7E82 | Borda dos controles (3,9:1) |
| `linha-hover` | oklch(0.925 0.012 205) | #DEE9EA | Linha sob o mouse |
| `regua-fundo` | oklch(0.88 0.012 205) | #CFDADB | Trilho da régua de vagas |
| `texto` | oklch(0.24 0.025 215) | #102227 | Texto principal |
| `texto-suave` | oklch(0.42 0.025 215) | #3E5156 | Texto secundário e cabeçalhos |
| `selecao` | oklch(0.86 0.050 225) | #AFD9EA | Seleção de texto |
| `primaria` | oklch(0.44 0.085 225) | #005C76 | Links, menu, foco, régua |
| `primaria-hover` | oklch(0.37 0.080 225) | #00485F | Link e botão pressionados |
| `destaque` | oklch(0.46 0.095 175) | #006955 | Só o botão principal |
| `destaque-hover` | oklch(0.39 0.085 175) | #005342 | Botão principal em hover |
| `sobre-destaque` | oklch(0.99 0.003 175) | #FAFCFC | Texto sobre botão cheio |
| `erro` | oklch(0.48 0.150 25) | #A12F2F | Erro de formulário e ação destrutiva |
| `erro-hover` | oklch(0.40 0.130 25) | #7F2021 | Erro em hover e texto de aviso de erro |
| `erro-fundo` | oklch(0.94 0.030 25) | #FFE4E1 | Fundo do aviso de erro |

### Sem semáforo

Não há verde, amarelo e vermelho de status. Status (Ativa, Trancada, Concluída, Cancelada) é texto neutro. Sucesso é só a mensagem, sem cor. Aviso de atenção é neutro. Cor forte só no erro de formulário, na ação destrutiva e na falha de conexão com o banco, e sai da paleta do projeto.

### Contraste verificado (WCAG)

| Par | Razão | Mínimo |
|---|---|---|
| texto sobre fundo | 13,8:1 | 4,5:1 |
| texto-suave sobre superficie | 7,7:1 | 4,5:1 |
| primaria sobre superficie | 6,9:1 | 4,5:1 |
| primaria sobre barra | 5,8:1 | 4,5:1 |
| sobre-destaque sobre destaque | 6,5:1 | 4,5:1 |
| erro sobre erro-fundo | 5,9:1 | 4,5:1 |
| borda-campo sobre superficie | 3,9:1 | 3:1 |

O `impeccable detect` rodado contra o servidor deu 0 achados nas 7 telas em 21/11/2026.

## Fontes

- **Títulos: Sora**, geométrica, pesos 500 e 600. Dá caráter ao nome do sistema e aos títulos sem pesar.
- **Corpo: Source Sans 3**, humanista, pesos 400 a 600, com algarismos tabulares (`font-variant-numeric: tabular-nums` nas tabelas). Legível em tabelas densas e com acentos completos.
- Par por contraste (geométrica com humanista), duas famílias, ambas gratuitas no Google Fonts.
- Carregadas por `<link>` no `templates/header.php`, com `display=swap`. Reserva: Segoe UI e fonte do sistema.

### Escala

Razão 1,25 sobre o corpo de 17 px: corpo 17 px, subtítulo 21 px, título de página 27 px (tokens `--fs-0`, `--fs-1` e `--fs-2`). A raiz (`html`) fica em 100%, respeitando a fonte escolhida no navegador; o corpo de 17 px vem de `--fs-0`. Entrelinha 1,5 no corpo e 1,2 nos títulos. Títulos com quebra equilibrada (`text-wrap: balance`).

## Espaçamento

Escala de 4, 8, 12, 16, 20, 24, 32, 40, 48 e 64 px. Com a raiz em 16 px, os espaços do Bootstrap já saem nela (0,5rem é 8 px, 1rem é 16 px, 3rem é 48 px). Ajustes: botões com 8 px em cima e embaixo e 16 px dos lados, campos com 8 px em cima e embaixo, e o logo e a seta do seletor também na escala.

Tabelas: 16 px de respiro dos lados em cada célula, o mesmo do título e do corpo do cartão, para o texto não colar na borda e o alinhamento da caixa se manter. Linhas de 40 a 48 px; a que tem botões de 40 px usa 4 px em cima e embaixo (48 px). Verificado por varredura de todos os elementos das 7 telas: só sobram o centro automático do container, as margens negativas da grade e a sobreposição de 1 px dos botões agrupados.

Origem do ajuste: a primeira versão usava raiz de 17 px, o que dava 8,5 px nas células e espaços de 6,4, 12,8 e 51 px, fora da escala e com a primeira coluna a 8,5 px da borda enquanto o título do cartão ficava a 17 px.

## Assinatura

**A ocupação do curso é uma régua fina**, em `primaria`, no lugar da etiqueta colorida. Na lista de cursos aparece abaixo de "2/25"; na ficha do curso, abaixo de "2 de 25 ocupadas". Régua cheia e a palavra "lotado" em peso 600 quando não há vaga. Mesmo gesto nos dois lugares, sem cor extra.

## Logotipo e favicon

O logotipo é o gerado no Gemini em 21/11/2026, a pedido do Murilo: um capelo de formatura com borla sobre um livro aberto, em petróleo (`primaria`), com a faixa e a borla em verde-azulado (`destaque`). Foi **vetorizado**, ou seja, redesenhado em curvas a partir do desenho original, com as cores dos tokens e cantos vivos, em `public/assets/logo.svg` (2,4 KB, fundo transparente). O JPG do Gemini (2048 px, fundo claro e cor manchada pela compressão) não entra no projeto.

Onde aparece: no cabeçalho, ao lado do nome Matriculum, com 32 px de altura e 8 px de espaço, com `alt` vazio porque o nome ao lado já descreve o link.

Favicon: o mesmo logotipo sobre um quadrado de cantos arredondados em `superficie`, com fio em `borda`, ocupando 75% do quadrado (`public/assets/favicon.svg`). Arquivos em `public/assets/`: `favicon.svg` (principal), `favicon.ico` (16, 32 e 48 px, para navegadores antigos e o pedido automático de `/favicon.ico`) e `apple-touch-icon.png` (180 px, quadrado inteiro, para o atalho no iPhone). Ligados no `templates/header.php`, com `theme-color` na cor da barra (`barra`).

Limite conhecido: em 16 px os detalhes do livro viram mancha e o favicon fica uma silhueta de capelo sobre livro; em 32 px o desenho aparece inteiro. Aceito de propósito para manter o desenho igual ao do Gemini.

## Decisões conscientes

- Bootstrap 5.3.3 fica como base de componentes, mas toda cor e fonte vem dos tokens. Motivo: entrega simples, sem etapa de build.
- Sem ícone decorativo; ficam só os botões que são ícone, com nome acessível citando o item. Motivo: regra de Interface.
- `autocomplete="off"` nos campos do estudante. Motivo: o formulário cadastra outra pessoa, e o navegador sugeriria os dados de quem está digitando.
- Sombra só na borda das tabelas que rolam para o lado, como indicação de que há mais conteúdo. Cartões têm borda fina e nenhuma sombra.
- Sem animação além do botão que afunda a 0,97 ao clicar e da troca de cor, ambas em 150 ms e desligadas com `prefers-reduced-motion`.
