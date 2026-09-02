<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['erro' => 'Acesso administrativo necessário.'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require_once '../conexao.php';
    $conn->set_charset('utf8mb4');

    $vendas = [];
    $resultado = $conn->query('
        SELECT
            pedido_id,
            criado_em,
            notebook_id,
            notebook_nome,
            marca_nome,
            categorias,
            quantidade,
            preco_unitario,
            subtotal
        FROM vw_vendas_dashboard
        ORDER BY criado_em DESC, pedido_id DESC
    ');

    while ($linha = $resultado->fetch_assoc()) {
        $vendas[] = [
            'pedidoId' => (int) $linha['pedido_id'],
            'criadoEm' => $linha['criado_em'],
            'produtoId' => (int) $linha['notebook_id'],
            'produto' => $linha['notebook_nome'],
            'marca' => $linha['marca_nome'],
            'categorias' => $linha['categorias'],
            'quantidade' => (int) $linha['quantidade'],
            'precoUnitario' => (float) $linha['preco_unitario'],
            'subtotal' => (float) $linha['subtotal'],
        ];
    }

    $estoque = [];
    $resultado = $conn->query('
        SELECT
            n.id,
            n.nome,
            COALESCE(m.nome, "Sem marca") AS marca,
            n.preco,
            n.estoque
        FROM notebooks n
        LEFT JOIN marcas m ON m.id = n.marca_id
        ORDER BY n.estoque ASC, n.nome ASC
    ');

    while ($linha = $resultado->fetch_assoc()) {
        $estoque[] = [
            'id' => (int) $linha['id'],
            'nome' => $linha['nome'],
            'marca' => $linha['marca'],
            'preco' => (float) $linha['preco'],
            'estoque' => (int) $linha['estoque'],
        ];
    }

    $resultado = $conn->query('
        SELECT
            (SELECT COUNT(*) FROM notebooks) AS total_produtos,
            (SELECT COALESCE(SUM(estoque), 0) FROM notebooks) AS total_estoque,
            (SELECT COUNT(*) FROM usuarios WHERE tipo = "cliente") AS total_clientes,
            (SELECT COALESCE(SUM(preco * estoque), 0) FROM notebooks) AS valor_estoque
    ');
    $resumo = $resultado->fetch_assoc();

    echo json_encode([
        'vendas' => $vendas,
        'estoque' => $estoque,
        'resumo' => [
            'totalProdutos' => (int) $resumo['total_produtos'],
            'totalEstoque' => (int) $resumo['total_estoque'],
            'totalClientes' => (int) $resumo['total_clientes'],
            'valorEstoque' => (float) $resumo['valor_estoque'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $erro) {
    error_log('Erro na API da dashboard: ' . $erro->getMessage());
    http_response_code(500);
    echo json_encode([
        'erro' => 'Não foi possível carregar os indicadores da dashboard.',
    ], JSON_UNESCAPED_UNICODE);
}
