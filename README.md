# TechNote

Loja de notebooks em PHP 8, MariaDB e TypeScript. O painel administrativo mostra indicadores de vendas por meio de uma API JSON.

## Instalação no XAMPP

1. Copie **o conteúdo desta pasta** para `C:\xampp\htdocs\` (a aplicação usa URLs iniciadas por `/`).
2. Inicie Apache e MySQL no XAMPP.
3. No phpMyAdmin ou no cliente MariaDB, execute **todo** o arquivo `banco/technote.sql`. Ele cria o banco, tabelas, dados de demonstração, views, triggers, função e procedures.
4. A conexão usa `localhost`, usuário `root` e senha vazia, padrão do XAMPP. Para outra instalação, configure `DB_HOST`, `DB_USER`, `DB_PASSWORD` e `DB_NAME` no ambiente do Apache.
5. Abra `http://localhost/index.php`. Cadastre uma conta comum na página de cadastro.
6. Para habilitar **a sua própria conta** como administrador na instalação local, execute no banco: `UPDATE usuarios SET tipo='admin' WHERE email='SEU_EMAIL_AQUI';`. Saia e entre novamente. Não há senha administrativa padrão no projeto.
7. Entre em `http://localhost/admin/dashboard.php`. O link **Gerenciar cadastros** abre os CRUDs de notebooks, marcas e categorias.

## Compilação

O JavaScript gerado já está incluído em `js/dashboard.js`. Para recompilar o TypeScript, execute `npm ci` e `npm run build` na pasta do projeto.

## O que demonstrar na apresentação

- Criar, listar, editar e excluir marca, categoria e notebook na área administrativa.
- Criar uma compra de teste e verificar o desconto do estoque, a confirmação, os pedidos recentes e a atualização da dashboard.
- Mostrar a chamada `api/dashboard.php` na aba Network do navegador e o fluxo MariaDB → view → PHP → JSON → TypeScript → DOM.
- Alterar os filtros de data e categoria na dashboard para verificar `filter`, o ranking com `sort`, a renderização com `map` e os totais com `reduce`.
- Mostrar no arquivo SQL a CTE `vendas_consolidadas`, as views `vw_vendas_dashboard` e `vw_catalogo_completo`, os triggers `BEFORE INSERT/UPDATE`, a função `fn_subtotal_item` e as procedures `sp_resumo_dashboard` e `sp_vendas_paginadas`.

## Estrutura

- `banco/technote.sql`: instalação completa do banco.
- `admin/cadastros.php`: CRUDs protegidos por sessão de administrador e token CSRF.
- `api/dashboard.php`: API JSON restrita ao administrador.
- `ts/dashboard.ts` e `js/dashboard.js`: lógica da dashboard e arquivo compilado.
- `der/Der_Pedro_Rossato.pdf`: diagrama do projeto original.

Esta entrega é uma aplicação local para XAMPP. O repositório não inclui hospedagem pública; se a avaliação pedir um link do site, será preciso publicar a aplicação em um servidor PHP com MariaDB e configurar as credenciais do banco nesse servidor.
