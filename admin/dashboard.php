<?php

session_start();

require_once "../conexao.php";

// ==========================================
// PROTEÇÃO DO ADMIN
// ==========================================

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nomeAdmin = $_SESSION['usuario_nome'];

// ==========================================
// INDICADORES
// ==========================================

// Total de notebooks
$sql = "SELECT COUNT(*) AS total FROM notebooks";
$resultado = $conn->query($sql);
$totalNotebooks = $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;


// Total de pedidos
$sql = "SELECT COUNT(*) AS total FROM pedidos";
$resultado = $conn->query($sql);
$totalPedidos = $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;


// Estoque total
$sql = "SELECT COALESCE(SUM(estoque), 0) AS total FROM notebooks";
$resultado = $conn->query($sql);
$totalEstoque = $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;


// Clientes
$sql = "SELECT COUNT(*) AS total FROM usuarios WHERE tipo = 'cliente'";
$resultado = $conn->query($sql);
$totalClientes = $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;


// Valor total do estoque
$sql = "
    SELECT COALESCE(SUM(preco * estoque), 0) AS total
    FROM notebooks
";

$resultado = $conn->query($sql);
$valorEstoque = $resultado
    ? (float)$resultado->fetch_assoc()['total']
    : 0;


// Faturamento
$sql = "
    SELECT COALESCE(SUM(total), 0) AS total
    FROM pedidos
";

$resultado = $conn->query($sql);
$faturamento = $resultado
    ? (float)$resultado->fetch_assoc()['total']
    : 0;


// Ticket médio
$sql = "
    SELECT COALESCE(AVG(total), 0) AS media
    FROM pedidos
";

$resultado = $conn->query($sql);
$ticketMedio = $resultado
    ? (float)$resultado->fetch_assoc()['media']
    : 0;


// Produtos com estoque baixo
$sql = "
    SELECT COUNT(*) AS total
    FROM notebooks
    WHERE estoque <= 3
";

$resultado = $conn->query($sql);
$estoqueBaixo = $resultado
    ? (int)$resultado->fetch_assoc()['total']
    : 0;


// ==========================================
// PRODUTOS COM ESTOQUE BAIXO
// ==========================================

$produtosBaixoEstoque = [];

$sql = "
    SELECT
        n.id,
        n.nome,
        n.preco,
        n.estoque,
        m.nome AS marca
    FROM notebooks n
    LEFT JOIN marcas m ON m.id = n.marca_id
    WHERE n.estoque <= 3
    ORDER BY n.estoque ASC
    LIMIT 5
";

$resultado = $conn->query($sql);

if ($resultado) {

    while ($row = $resultado->fetch_assoc()) {

        $produtosBaixoEstoque[] = $row;

    }

}


// ==========================================
// PEDIDOS RECENTES
// ==========================================

$pedidosRecentes = [];

$sql = "
    SELECT
        id,
        nome,
        email,
        total,
        criado_em
    FROM pedidos
    ORDER BY criado_em DESC
    LIMIT 5
";

$resultado = $conn->query($sql);

if ($resultado) {

    while ($row = $resultado->fetch_assoc()) {

        $pedidosRecentes[] = $row;

    }

}


// ==========================================
// PRODUTOS MAIS VENDIDOS
// ==========================================

$produtosMaisVendidos = [];

$sql = "
    SELECT
        n.nome,
        SUM(pi.quantidade) AS quantidade
    FROM pedido_itens pi
    INNER JOIN notebooks n
        ON n.id = pi.notebook_id
    GROUP BY n.id, n.nome
    ORDER BY quantidade DESC
    LIMIT 5
";

$resultado = $conn->query($sql);

if ($resultado) {

    while ($row = $resultado->fetch_assoc()) {

        $produtosMaisVendidos[] = $row;

    }

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | TechNote</title>


    <!-- GOOGLE FONTS -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- CSS DO TECHNOTE -->

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body class="dashboard-body">


<!-- ==========================================
     TOPBAR
========================================== -->

<div class="topbar dashboard-topbar">

    <p>
        ⚡ PAINEL ADMINISTRATIVO
        &nbsp;|&nbsp;
        TECHNOTE
    </p>

</div>


<!-- ==========================================
     NAVBAR
========================================== -->

<nav class="navbar navbar-expand-lg navbar-dark technote-navbar dashboard-navbar">

    <div class="container-fluid px-4">

        <a
            class="navbar-brand"
            href="../index.php"
        >

            <div class="logo">

                <h1>TECHNOTE</h1>

                <span>
                    ADMIN PANEL
                </span>

            </div>

        </a>


        <div class="dashboard-user">

            <div class="dashboard-user-info">

                <span>
                    Administrador
                </span>

                <strong>
                    <?php echo htmlspecialchars($nomeAdmin); ?>
                </strong>

            </div>


            <a
                href="../index.php"
                class="dashboard-nav-link"
            >
                Ver site
            </a>


            <a
                href="../logout.php"
                class="dashboard-logout"
            >
                Sair
            </a>

        </div>

    </div>

</nav>


<!-- ==========================================
     CONTEÚDO
========================================== -->

<main class="dashboard-container">


    <!-- CABEÇALHO -->

    <section class="dashboard-heading">

        <div>

            <span class="dashboard-tag">
                PAINEL DE CONTROLE
            </span>

            <h2>
                Dashboard
            </h2>

            <p>
                Acompanhe os principais dados da TechNote
                em um só lugar.
            </p>

        </div>

        <div class="dashboard-date">

            <span>
                STATUS DO SISTEMA
            </span>

            <strong>
                ● ONLINE
            </strong>

        </div>

    </section>


    <!-- ======================================
         INDICADORES PRINCIPAIS
    ======================================= -->

    <section class="dashboard-indicators">


        <!-- NOTEBOOKS -->

        <div class="dashboard-card">

            <div class="dashboard-card-top">

                <span>
                    PRODUTOS
                </span>

                <div class="dashboard-icon">
                    💻
                </div>

            </div>

            <strong class="dashboard-number">
                <?php echo $totalNotebooks; ?>
            </strong>

            <p>
                notebooks cadastrados
            </p>

        </div>


        <!-- PEDIDOS -->

        <div class="dashboard-card">

            <div class="dashboard-card-top">

                <span>
                    PEDIDOS
                </span>

                <div class="dashboard-icon">
                    🛒
                </div>

            </div>

            <strong class="dashboard-number">
                <?php echo $totalPedidos; ?>
            </strong>

            <p>
                pedidos registrados
            </p>

        </div>


        <!-- ESTOQUE -->

        <div class="dashboard-card">

            <div class="dashboard-card-top">

                <span>
                    ESTOQUE
                </span>

                <div class="dashboard-icon">
                    📦
                </div>

            </div>

            <strong class="dashboard-number">
                <?php echo $totalEstoque; ?>
            </strong>

            <p>
                unidades disponíveis
            </p>

        </div>


        <!-- CLIENTES -->

        <div class="dashboard-card">

            <div class="dashboard-card-top">

                <span>
                    CLIENTES
                </span>

                <div class="dashboard-icon">
                    👤
                </div>

            </div>

            <strong class="dashboard-number">
                <?php echo $totalClientes; ?>
            </strong>

            <p>
                clientes cadastrados
            </p>

        </div>

    </section>


    <!-- ======================================
         INDICADORES FINANCEIROS
    ======================================= -->

    <section class="dashboard-secondary">


        <div class="dashboard-secondary-card">

            <span>
                VALOR DO ESTOQUE
            </span>

            <strong>
                R$
                <?php
                echo number_format(
                    $valorEstoque,
                    2,
                    ',',
                    '.'
                );
                ?>
            </strong>

            <p>
                valor estimado dos produtos disponíveis
            </p>

        </div>


        <div class="dashboard-secondary-card">

            <span>
                FATURAMENTO
            </span>

            <strong>
                R$
                <?php
                echo number_format(
                    $faturamento,
                    2,
                    ',',
                    '.'
                );
                ?>
            </strong>

            <p>
                total registrado em pedidos
            </p>

        </div>


        <div class="dashboard-secondary-card">

            <span>
                TICKET MÉDIO
            </span>

            <strong>
                R$
                <?php
                echo number_format(
                    $ticketMedio,
                    2,
                    ',',
                    '.'
                );
                ?>
            </strong>

            <p>
                valor médio por pedido
            </p>

        </div>


        <div class="dashboard-secondary-card dashboard-warning">

            <span>
                ESTOQUE CRÍTICO
            </span>

            <strong>
                <?php echo $estoqueBaixo; ?>
            </strong>

            <p>
                produtos com 3 ou menos unidades
            </p>

        </div>

    </section>


    <!-- ======================================
         ÁREA PRINCIPAL
    ======================================= -->

    <section class="dashboard-grid">


        <!-- ESTOQUE BAIXO -->

        <div class="dashboard-panel">

            <div class="dashboard-panel-header">

                <div>

                    <span>
                        ATENÇÃO
                    </span>

                    <h3>
                        Estoque baixo
                    </h3>

                </div>

                <div class="panel-count">
                    <?php echo count($produtosBaixoEstoque); ?>
                </div>

            </div>


            <?php if (empty($produtosBaixoEstoque)) { ?>

                <div class="dashboard-empty">

                    <span>
                        ✓
                    </span>

                    <p>
                        Nenhum produto com estoque crítico.
                    </p>

                </div>

            <?php } else { ?>

                <div class="dashboard-list">

                    <?php foreach ($produtosBaixoEstoque as $produto) { ?>

                        <div class="dashboard-list-item">

                            <div>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $produto['nome']
                                    );
                                    ?>
                                </strong>

                                <span>
                                    <?php
                                    echo htmlspecialchars(
                                        $produto['marca'] ?? 'Sem marca'
                                    );
                                    ?>
                                </span>

                            </div>

                            <div class="stock-number">

                                <?php echo (int)$produto['estoque']; ?>

                                <small>
                                    un.
                                </small>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>


        <!-- PEDIDOS RECENTES -->

        <div class="dashboard-panel">

            <div class="dashboard-panel-header">

                <div>

                    <span>
                        VENDAS
                    </span>

                    <h3>
                        Pedidos recentes
                    </h3>

                </div>

                <div class="panel-count">
                    <?php echo count($pedidosRecentes); ?>
                </div>

            </div>


            <?php if (empty($pedidosRecentes)) { ?>

                <div class="dashboard-empty">

                    <span>
                        —
                    </span>

                    <p>
                        Nenhum pedido registrado ainda.
                    </p>

                </div>

            <?php } else { ?>

                <div class="dashboard-list">

                    <?php foreach ($pedidosRecentes as $pedido) { ?>

                        <div class="dashboard-list-item">

                            <div>

                                <strong>
                                    #<?php echo $pedido['id']; ?>
                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $pedido['nome']
                                    );
                                    ?>
                                </strong>

                                <span>
                                    <?php
                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $pedido['criado_em']
                                        )
                                    );
                                    ?>
                                </span>

                            </div>

                            <div class="order-value">

                                R$
                                <?php
                                echo number_format(
                                    $pedido['total'],
                                    2,
                                    ',',
                                    '.'
                                );
                                ?>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    </section>


    <!-- ======================================
         PRODUTOS MAIS VENDIDOS
    ======================================= -->

    <section class="dashboard-panel dashboard-ranking">

        <div class="dashboard-panel-header">

            <div>

                <span>
                    DESEMPENHO
                </span>

                <h3>
                    Produtos mais vendidos
                </h3>

            </div>

        </div>


        <?php if (empty($produtosMaisVendidos)) { ?>

            <div class="dashboard-empty">

                <span>
                    ★
                </span>

                <p>
                    Ainda não existem vendas suficientes
                    para gerar um ranking.
                </p>

            </div>

        <?php } else { ?>

            <div class="ranking-list">

                <?php

                $posicao = 1;

                foreach ($produtosMaisVendidos as $produto) {

                ?>

                    <div class="ranking-item">

                        <div class="ranking-position">

                            <?php
                            echo str_pad(
                                $posicao,
                                2,
                                '0',
                                STR_PAD_LEFT
                            );
                            ?>

                        </div>


                        <div class="ranking-product">

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $produto['nome']
                                );
                                ?>
                            </strong>

                            <span>
                                <?php
                                echo (int)$produto['quantidade'];
                                ?>
                                unidades vendidas
                            </span>

                        </div>


                        <div class="ranking-bar">

                            <div
                                style="
                                    width:
                                    <?php
                                    $maiorQuantidade =
                                        (int)$produtosMaisVendidos[0]['quantidade'];

                                    $largura =
                                        $maiorQuantidade > 0
                                        ? (
                                            (
                                                (int)$produto['quantidade']
                                                /
                                                $maiorQuantidade
                                            ) * 100
                                        )
                                        : 0;

                                    echo $largura;
                                    ?>%;
                                "
                            ></div>

                        </div>

                    </div>

                <?php

                    $posicao++;

                }

                ?>

            </div>

        <?php } ?>

    </section>


    <!-- ======================================
         AÇÕES RÁPIDAS
    ======================================= -->

    <section class="dashboard-actions">

        <div class="dashboard-action-intro">

            <span>
                ADMINISTRAÇÃO
            </span>

            <h3>
                Ações rápidas
            </h3>

            <p>
                Acesse rapidamente as principais
                áreas do sistema.
            </p>

        </div>


        <a
            href="../notebooks.php"
            class="dashboard-action"
        >

            <span>
                💻
            </span>

            <strong>
                Produtos
            </strong>

            <small>
                Ver catálogo
            </small>

        </a>


        <a
            href="../index.php"
            class="dashboard-action"
        >

            <span>
                🌐
            </span>

            <strong>
                Site
            </strong>

            <small>
                Voltar ao site
            </small>

        </a>


        <a
            href="../logout.php"
            class="dashboard-action dashboard-action-danger"
        >

            <span>
                ↪
            </span>

            <strong>
                Sair
            </strong>

            <small>
                Encerrar sessão
            </small>

        </a>

    </section>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>