<?php

session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

$email = $_SESSION['usuario_email'] ?? '';
$pedidos = [];

$sql = '
    SELECT
        p.id,
        p.total,
        p.criado_em,
        pi.quantidade,
        pi.preco_unitario,
        n.nome AS notebook
    FROM pedidos p
    LEFT JOIN pedido_itens pi ON pi.pedido_id = p.id
    LEFT JOIN notebooks n ON n.id = pi.notebook_id
    WHERE p.email = ?
    ORDER BY p.criado_em DESC, p.id DESC, pi.id ASC
';

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$resultado = $stmt->get_result();

while ($linha = $resultado->fetch_assoc()) {
    $pedidoId = (int) $linha['id'];

    if (!isset($pedidos[$pedidoId])) {
        $pedidos[$pedidoId] = [
            'id' => $pedidoId,
            'total' => (float) $linha['total'],
            'criado_em' => $linha['criado_em'],
            'itens' => [],
        ];
    }

    if ($linha['notebook'] !== null) {
        $pedidos[$pedidoId]['itens'][] = [
            'nome' => $linha['notebook'],
            'quantidade' => (int) $linha['quantidade'],
            'preco_unitario' => (float) $linha['preco_unitario'],
        ];
    }
}

$stmt->close();
include 'includes/header.php';
?>

<section class="titulo-pagina pedidos-titulo">
    <p>Área do cliente</p>
    <h2>Meus Pedidos</h2>
</section>

<section class="pedidos-section">
    <div class="pedidos-heading">
        <div>
            <span>HISTÓRICO DE COMPRAS</span>
            <h3><?php echo count($pedidos); ?> pedido<?php echo count($pedidos) === 1 ? '' : 's'; ?> encontrado<?php echo count($pedidos) === 1 ? '' : 's'; ?></h3>
        </div>

        <a href="/notebooks.php" class="pedidos-comprar">Continuar comprando</a>
    </div>

    <?php if (empty($pedidos)) { ?>
        <div class="pedidos-vazio">
            <span>📦</span>
            <h3>Nenhum pedido por aqui</h3>
            <p>Quando você finalizar uma compra, o pedido aparecerá nesta página.</p>
            <a href="/notebooks.php" class="btn">Conhecer notebooks</a>
        </div>
    <?php } else { ?>
        <div class="pedidos-lista">
            <?php foreach ($pedidos as $pedido) { ?>
                <article class="pedido-card">
                    <header class="pedido-card-header">
                        <div>
                            <span>PEDIDO</span>
                            <strong>#<?php echo str_pad((string) $pedido['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                        </div>

                        <div>
                            <span>REALIZADO EM</span>
                            <strong><?php echo date('d/m/Y \à\s H:i', strtotime($pedido['criado_em'])); ?></strong>
                        </div>

                        <div class="pedido-status">
                            <span>STATUS</span>
                            <strong>● Recebido</strong>
                        </div>

                        <div class="pedido-total">
                            <span>TOTAL</span>
                            <strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong>
                        </div>
                    </header>

                    <div class="pedido-itens">
                        <?php if (empty($pedido['itens'])) { ?>
                            <p class="pedido-sem-itens">Os itens deste pedido não estão disponíveis.</p>
                        <?php } else { ?>
                            <?php foreach ($pedido['itens'] as $item) { ?>
                                <div class="pedido-item">
                                    <div class="pedido-item-icon">💻</div>
                                    <div class="pedido-item-info">
                                        <strong><?php echo htmlspecialchars($item['nome']); ?></strong>
                                        <span><?php echo $item['quantidade']; ?> unidade<?php echo $item['quantidade'] === 1 ? '' : 's'; ?></span>
                                    </div>
                                    <strong class="pedido-item-valor">
                                        R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                                    </strong>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<?php include 'includes/footer.php'; ?>
