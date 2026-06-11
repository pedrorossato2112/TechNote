<?php
session_start();
include 'conexao.php';
include 'includes/funcoes.php';

$carrinho = $_SESSION['carrinho'] ?? [];

$validacao = validarCarrinho($carrinho, $regras);
if (!$validacao['valido']) {
    header('Location: notebooks.php');
    exit;
}

$subtotal = calcularTotalCarrinho($carrinho);
$frete    = calcularFrete($subtotal, $regras);
$total    = calcularTotalComFrete($subtotal, $regras);
$erros    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $validCliente = validarDadosCliente($nome, $email);

    if (!$validCliente['valido']) {
        $erros = $validCliente['erros'];
    } else {
        $conn->begin_transaction();
        try {
            foreach ($carrinho as $item) {
                $stmt = $conn->prepare("SELECT estoque FROM notebooks WHERE id = ? FOR UPDATE");
                $stmt->bind_param("i", $item['id']);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                if ($row['estoque'] < $item['quantidade']) {
                    throw new Exception("Estoque insuficiente para {$item['nome']}.");
                }
            }

            $stmt = $conn->prepare("INSERT INTO pedidos (nome, email, total) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $nome, $email, $total);
            $stmt->execute();
            $pedido_id = $conn->insert_id;

            foreach ($carrinho as $item) {
                $stmt = $conn->prepare("INSERT INTO pedido_itens (pedido_id, notebook_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $pedido_id, $item['id'], $item['quantidade'], $item['preco']);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE notebooks SET estoque = estoque - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantidade'], $item['id']);
                $stmt->execute();
            }

            $conn->commit();
            unset($_SESSION['carrinho']);
            header("Location: pedido_confirmado.php?id=$pedido_id");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $erros[] = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<section class="titulo-pagina">
    <p>Quase lá</p>
    <h2>Finalizar Compra</h2>
</section>

<section class="finalizar-section">

    <!-- ALERT BOOTSTRAP para erros -->
    <?php if (!empty($erros)) { ?>
        <div class="alert alert-danger border-0 rounded-0" role="alert">
            <?php foreach ($erros as $erro) { ?>
                <p class="mb-0">⚠ <?php echo $erro; ?></p>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="finalizar-resumo">
        <h3>Resumo do Pedido</h3>

        <?php foreach ($carrinho as $item) { ?>
            <div class="resumo-item">
                <span><?php echo $item['nome']; ?> × <?php echo $item['quantidade']; ?></span>
                <span>R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></span>
            </div>
        <?php } ?>

        <div class="resumo-item">
            <span>Frete</span>
            <span>
                <?php if ($frete == 0) { ?>
                    <span style="color:#4caf50; font-size:13px; font-weight:700;">GRÁTIS</span>
                <?php } else { ?>
                    R$ <?php echo number_format($frete, 2, ',', '.'); ?>
                <?php } ?>
            </span>
        </div>

        <div class="resumo-total">
            <span>Total</span>
            <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
        </div>

        <?php if ($frete > 0) { ?>
            <p class="frete-aviso">
                Faltam R$ <?php echo number_format($regras['frete_gratis_acima'] - $subtotal, 2, ',', '.'); ?> para frete grátis.
            </p>
        <?php } ?>
    </div>

    <!-- FORMULÁRIO com Modal Bootstrap de confirmação -->
    <form method="POST" class="finalizar-form" id="formFinalizar">
        <input type="text" name="nome" placeholder="Seu nome completo"
               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
        <input type="email" name="email" placeholder="Seu email"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

        <button type="button" class="btn-confirmar" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
            Confirmar Pedido
        </button>
    </form>

    <!-- MODAL BOOTSTRAP -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content technote-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Confirmar pedido?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Total: <strong style="color:#ffd000;">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></p>
                    <p style="color:#555; font-size:13px;">Ao confirmar, o estoque será descontado e o pedido registrado.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-modal-confirmar" onclick="document.getElementById('formFinalizar').submit()">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

<?php include 'includes/footer.php'; ?>