<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechNote</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="topbar">
    <p>⚡ FRETE GRÁTIS acima de R$ 5.000 | 🚚 Envio para todo o Brasil | 🔥 Ofertas Gamer</p>
</div>

<!-- NAVBAR BOOTSTRAP -->
<nav class="navbar navbar-expand-lg navbar-dark technote-navbar sticky-top">
    <div class="container-fluid px-4">

        <a class="navbar-brand" href="/index.php">
            <div class="logo">
                <h1>TECHNOTE</h1>
                <span>HIGH PERFORMANCE MACHINES</span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav gap-3 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/notebooks.php">Notebooks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contato.php">Contato</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-carrinho" href="/carrinho.php">
                        Carrinho
                        <?php
                            $qtd = 0;
                            foreach ($_SESSION['carrinho'] ?? [] as $item) $qtd += $item['quantidade'];
                            if ($qtd > 0) echo "<span class='carrinho-badge'>$qtd</span>";
                        ?>
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<main>