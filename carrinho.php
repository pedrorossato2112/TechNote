<?php
session_start();
include 'conexao.php';
include 'includes/header.php';

$carrinho = $_SESSION['carrinho'] ?? [];
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>

<section class="titulo-pagina">
    <p>Sua seleção</p>
    <h2>Carrinho</h2>
</section>

<section class="carrinho-section">

    <?php if (empty($carrinho)) { ?>

        <div class="carrinho-vazio">
            <p>Seu carrinho está vazio.</p>
            <a href="notebooks.php" class="btn">Ver Notebooks</a>
        </div>

    <?php } else { ?>

        <div class="carrinho-itens">

            <?php foreach ($carrinho as $item) { ?>

                <div class="carrinho-item">

                    <div class="carrinho-item-info">
                        <h3><?php echo $item['nome']; ?></h3>
                        <p>Quantidade: <?php echo $item['quantidade']; ?></p>
                    </div>

                    <div class="carrinho-item-preco">
                        <span>R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></span>
                        <a href="remover_carrinho.php?id=<?php echo $item['id']; ?>" class="btn-remover">Remover</a>
                    </div>

                </div>

            <?php } ?>

        </div>

        <div class="carrinho-total">
            <p>Total: <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></p>
        </div>

        <div class="carrinho-actions">
            <a href="notebooks.php" class="btn-secundario">Continuar Comprando</a>
            <a href="finalizar.php" class="btn">Finalizar Compra</a>
        </div>

    <?php } ?>

</section>

<?php include 'includes/footer.php'; ?>