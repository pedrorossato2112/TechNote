<?php

// ============================================================
// ARRAY ESTRUTURADO - Regras de negócio centralizadas
// ============================================================

$regras = [
    'frete_gratis_acima' => 5000.00,
    'frete_fixo'         => 150.00,
    'desconto_percentual'=> 10,
    'estoque_minimo'     => 1,
    'preco_minimo'       => 100.00
];

$categorias_labels = [
    1 => 'Gamer',
    2 => 'Trabalho',
    3 => 'Estudo'
];

// ============================================================
// FUNÇÕES DE PROCESSAMENTO
// ============================================================


function calcularFrete($total, $regras) {
    if ($total <= 0) {
        return 0;
    }
    if ($total >= $regras['frete_gratis_acima']) {
        return 0;
    }
    return $regras['frete_fixo'];
}


function aplicarDesconto($valor, $percentual) {
    if ($valor <= 0 || $percentual <= 0) {
        return $valor;
    }
    $desconto = $valor * ($percentual / 100);
    return $valor - $desconto;
}


function calcularTotalCarrinho($carrinho) {
    $total = 0;
    foreach ($carrinho as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
    return $total;
}


function calcularTotalComFrete($total, $regras) {
    $frete = calcularFrete($total, $regras);
    return $total + $frete;
}

// ============================================================
// VALIDAÇÃO DE REGRAS DE NEGÓCIO
// ============================================================

function validarCarrinho($carrinho, $regras) {
    $erros = [];

    if (empty($carrinho)) {
        $erros[] = 'O carrinho está vazio.';
        return ['valido' => false, 'erros' => $erros];
    }

    foreach ($carrinho as $item) {
        if ($item['quantidade'] <= 0) {
            $erros[] = "Quantidade inválida para {$item['nome']}.";
        }
        if ($item['preco'] < $regras['preco_minimo']) {
            $erros[] = "Preço inválido para {$item['nome']}.";
        }
    }

    return [
        'valido' => empty($erros),
        'erros'  => $erros
    ];
}

function validarDadosCliente($nome, $email) {
    $erros = [];

    if (empty(trim($nome))) {
        $erros[] = 'Nome é obrigatório.';
    }

    if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }

    return [
        'valido' => empty($erros),
        'erros'  => $erros
    ];
}

// ============================================================
// LÓGICA DE PESQUISA E FILTRO
// ============================================================

function filtrarNotebooks($notebooks, $busca) {
    if (empty(trim($busca))) {
        return $notebooks;
    }

    $busca = strtolower(trim($busca));
    $resultado = [];

    foreach ($notebooks as $notebook) {
        $nome  = strtolower($notebook['nome']);
        $marca = strtolower($notebook['marca'] ?? '');

        if (strpos($nome, $busca) !== false || strpos($marca, $busca) !== false) {
            $resultado[] = $notebook;
        }
    }

    return $resultado;
}

function filtrarPorPreco($notebooks, $min = 0, $max = PHP_INT_MAX) {
    if ($min < 0 || $max < 0 || $min > $max) {
        return $notebooks;
    }

    $resultado = [];
    foreach ($notebooks as $notebook) {
        if ($notebook['preco'] >= $min && $notebook['preco'] <= $max) {
            $resultado[] = $notebook;
        }
    }

    return $resultado;
}


function filtrarComEstoque($notebooks) {
    $resultado = [];
    foreach ($notebooks as $notebook) {
        if ($notebook['estoque'] >= 1) {
            $resultado[] = $notebook;
        }
    }
    return $resultado;
}