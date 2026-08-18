<?php
session_start();
include 'conexao.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: notebooks.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM notebooks WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$notebook = $stmt->get_result()->fetch_assoc();

if (!$notebook || $notebook['estoque'] <= 0) {
    header('Location: notebooks.php');
    exit;
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (isset($_SESSION['carrinho'][$id])) {
    // Não deixa adicionar mais do que o estoque
    if ($_SESSION['carrinho'][$id]['quantidade'] < $notebook['estoque']) {
        $_SESSION['carrinho'][$id]['quantidade']++;
    }
} else {
    $_SESSION['carrinho'][$id] = [
        'id'         => $notebook['id'],
        'nome'       => $notebook['nome'],
        'preco'      => $notebook['preco'],
        'quantidade' => 1
    ];
}

header('Location: carrinho.php');
exit;