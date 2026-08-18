<?php
session_start();
include 'conexao.php';
include 'includes/header.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT pi.*, n.nome AS notebook
    FROM pedido_itens pi
    JOIN notebooks n ON pi.notebook_id = n.id
    WHERE pi.pedido_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$itens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<section class="titulo-pagina">
    <p>Pedido #<?php echo $pedido['id']; ?></p>
    <h2>Compra Confirmada!</h2>
</section>

<section class="confirmado-section">

    <div class="confirmado-box">

        <p class="confirmado-msg">
            Obrigado, <strong><?php echo htmlspecialchars($pedido['nome']); ?></strong>!
            Seu pedido foi recebido e será processado em breve.
            Uma confirmação será enviada para <strong><?php echo htmlspecialchars($pedido['email']); ?></strong>.
        </p>

        <div class="resumo-final">

            <?php foreach ($itens as $item) { ?>
                <div class="resumo-item">
                    <span><?php echo $item['notebook']; ?> × <?php echo $item['quantidade']; ?></span>
                    <span>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></span>
                </div>
            <?php } ?>

            <div class="resumo-total">
                <span>Total pago</span>
                <strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong>
            </div>

        </div>

        <a href="notebooks.php" class="btn">Continuar Comprando</a>

    </div>

</section>

<?php include 'includes/footer.php'; ?>