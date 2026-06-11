<?php
include 'conexao.php';
include 'includes/funcoes.php';
include 'includes/header.php';

$sql = "SELECT n.*, m.nome AS marca FROM notebooks n LEFT JOIN marcas m ON n.marca_id = m.id";
$resultado = $conn->query($sql);

$notebooks = [];
while ($row = $resultado->fetch_assoc()) {
    $notebooks[] = [
        'id'      => $row['id'],
        'nome'    => $row['nome'],
        'preco'   => (float) $row['preco'],
        'estoque' => (int) $row['estoque'],
        'marca'   => $row['marca']
    ];
}

$busca      = $_GET['busca'] ?? '';
$so_estoque = isset($_GET['estoque']);

$filtrados = filtrarNotebooks($notebooks, $busca);
if ($so_estoque) {
    $filtrados = filtrarComEstoque($filtrados);
}
?>

<section class="titulo-pagina">
    <p>Catálogo</p>
    <h2>Nossos Notebooks</h2>
</section>

<!-- BUSCA -->
<section class="busca-section">
    <form method="GET" action="notebooks.php" class="busca-form">
        <input type="text" name="busca" placeholder="Buscar por nome ou marca..."
               value="<?php echo htmlspecialchars($busca); ?>">
        <label class="busca-checkbox">
            <input type="checkbox" name="estoque" <?php echo $so_estoque ? 'checked' : ''; ?>>
            Só com estoque
        </label>
        <button type="submit">Buscar</button>
        <?php if ($busca || $so_estoque) { ?>
            <a href="notebooks.php" class="btn-limpar">Limpar</a>
        <?php } ?>
    </form>
</section>

<!-- CARDS BOOTSTRAP -->
<section class="produtos">

    <?php if (empty($filtrados)) { ?>
        <div class="sem-resultados">
            <p>Nenhum notebook encontrado para "<strong><?php echo htmlspecialchars($busca); ?></strong>".</p>
            <a href="notebooks.php" class="btn">Ver todos</a>
        </div>
    <?php } ?>

    <?php foreach ($filtrados as $notebook) { ?>

        <div class="card technote-card">

            <!-- BADGE BOOTSTRAP -->
            <?php if ($notebook['estoque'] == 0) { ?>
                <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Esgotado</span>
            <?php } elseif ($notebook['estoque'] <= 3) { ?>
                <span class="badge bg-danger position-absolute top-0 start-0 m-2">Últimas unidades</span>
            <?php } else { ?>
                <span class="badge position-absolute top-0 start-0 m-2" style="background:#ffd000; color:#0a0a0a;">Em estoque</span>
            <?php } ?>

            <img src="https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=1200"
                 class="card-img-top" alt="<?php echo $notebook['nome']; ?>">

            <div class="card-body">
                <h3 class="card-title"><?php echo $notebook['nome']; ?></h3>
                <p class="card-text"><?php echo $notebook['marca']; ?> · Estoque: <?php echo $notebook['estoque']; ?> unidades</p>
                <span class="preco">R$ <?php echo number_format($notebook['preco'], 2, ',', '.'); ?></span>

                <?php if ($notebook['estoque'] > 0) { ?>
                    <a href="adicionar_carrinho.php?id=<?php echo $notebook['id']; ?>" class="btn-card">
                        Adicionar ao Carrinho
                    </a>
                <?php } else { ?>
                    <button class="btn-card" disabled style="background:#1e1e1e; color:#444; cursor:not-allowed; border-color:#1e1e1e;">
                        Esgotado
                    </button>
                <?php } ?>
            </div>

        </div>

    <?php } ?>

</section>

<?php include 'includes/footer.php'; ?>