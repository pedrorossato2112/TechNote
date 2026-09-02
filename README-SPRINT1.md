# TechNote - Sprint 1

## Requisitos implementados

- View analitica `vw_vendas_dashboard`.
- CTE de vendas consolidadas por produto.
- Trigger `BEFORE UPDATE` para manter preco e estoque positivos.
- API PHP protegida por sessao administrativa e com retorno JSON.
- Consumo da API com `fetch`, `async/await` e `try/catch`.
- Calculos da dashboard com `reduce`.
- Tratamento de banco vazio, API indisponivel e divisoes por zero.
- Compilacao real de `ts/dashboard.ts` para `js/dashboard.js`.

## Aplicar o banco

Abra `sprint1-banco-avancado.sql` no DBeaver e execute o script completo no banco `technote`.

## Compilar o TypeScript

No terminal, dentro de `C:\xampp\htdocs`, execute:

```text
npm install
npm run build
```

O arquivo gerado sera `js/dashboard.js`.

## Demonstracao

1. Inicie Apache e MySQL no XAMPP.
2. Entre com a conta administrativa.
3. Abra a dashboard pelo menu do perfil.
4. Mostre a aba Network do navegador carregando `api/dashboard.php`.
5. Explique o fluxo: MariaDB -> View -> PHP -> JSON -> TypeScript -> DOM.
6. Execute `npm run build` para demonstrar a compilacao sem erros.
