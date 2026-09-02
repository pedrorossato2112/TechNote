<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogado = isset($_SESSION['usuario_id']);
$usuarioNome = $_SESSION['usuario_nome'] ?? '';
$usuarioAdmin = ($_SESSION['usuario_tipo'] ?? '') === 'admin';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TechNote</title>


    <link rel="preconnect" href="https://fonts.googleapis.com">


    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="/css/style.css"
    >

</head>


<body>


<!-- =====================================================
     TOPBAR
===================================================== -->

<div class="topbar">

    <p>
        ⚡ FRETE GRÁTIS acima de R$ 5.000 |
        🚚 Envio para todo o Brasil |
        🔥 Ofertas Gamer
    </p>

</div>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-expand-lg navbar-dark technote-navbar sticky-top">

    <div class="container-fluid px-4">


        <!-- LOGO -->

        <a
            class="navbar-brand"
            href="/index.php"
        >

            <div class="logo">

                <h1>
                    TECHNOTE
                </h1>

                <span>
                    HIGH PERFORMANCE MACHINES
                </span>

            </div>

        </a>


        <!-- BOTÃO MOBILE -->

        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- MENU -->

        <div
            class="collapse navbar-collapse justify-content-end"
            id="navMenu"
        >

            <ul class="navbar-nav gap-3 align-items-lg-center">


                <!-- HOME -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="/index.php"
                    >
                        Home
                    </a>

                </li>


                <!-- NOTEBOOKS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="/notebooks.php"
                    >
                        Notebooks
                    </a>

                </li>


                <!-- CONTATO -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="/contato.php"
                    >
                        Contato
                    </a>

                </li>


                <!-- CARRINHO -->

                <li class="nav-item">

                    <a
                        class="nav-link nav-carrinho"
                        href="/carrinho.php"
                    >

                        Carrinho

                        <?php

                        $qtd = 0;

                        foreach (
                            $_SESSION['carrinho'] ?? []
                            as $item
                        ) {

                            $qtd += $item['quantidade'];

                        }

                        if ($qtd > 0) {

                            echo "
                                <span class='carrinho-badge'>
                                    $qtd
                                </span>
                            ";

                        }

                        ?>

                    </a>

                </li>


                <!-- =================================================
                     USUÁRIO
                ================================================== -->

                <?php if (!$usuarioLogado) { ?>

                    <!-- VISITANTE -->

                    <li class="nav-item">

                        <a
                            href="/login.php"
                            class="btn-login-header"
                        >

                            ENTRAR

                            <span>
                                →
                            </span>

                        </a>

                    </li>


                <?php } else { ?>


                    <!-- USUÁRIO LOGADO -->

                    <li class="nav-item dropdown user-menu">

                        <a
                            href="#"
                            class="user-button dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >


                            <!-- ÍCONE -->

                            <span class="user-icon">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    fill="currentColor"
                                    viewBox="0 0 16 16"
                                >

                                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>

                                    <path
                                        fill-rule="evenodd"
                                        d="M14 14s-1-1.5-6-1.5S2 14 2 14s0-4 6-4 6 4 6 4z"
                                    />

                                </svg>

                            </span>


                            <!-- NOME -->

                            <span class="user-name">

                                <?php
                                echo htmlspecialchars($usuarioNome);
                                ?>

                            </span>


                        </a>


                        <!-- MENU DROPDOWN -->

                        <ul class="dropdown-menu dropdown-menu-end user-dropdown">


                            <li class="user-dropdown-header">

                                <span>
                                    Olá,
                                </span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars($usuarioNome);
                                    ?>
                                </strong>

                            </li>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <!-- PEDIDOS -->

                            <li>

                                <a
                                    class="dropdown-item"
                                    href="/meus_pedidos.php"
                                >

                                    <span class="dropdown-icon">
                                        📦
                                    </span>

                                    Meus pedidos

                                </a>

                            </li>


                            <!-- CONTA -->

                            <li>

                                <a
                                    class="dropdown-item"
                                    href="/minha_conta.php"
                                >

                                    <span class="dropdown-icon">
                                        ⚙️
                                    </span>

                                    Minha conta

                                </a>

                            </li>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <!-- DASHBOARD DO ADMINISTRADOR -->

                            <?php if ($usuarioAdmin) { ?>

                                <li>

                                    <a
                                        class="dropdown-item dropdown-admin"
                                        href="/admin/dashboard.php"
                                    >

                                        <span class="dropdown-icon">
                                            📊
                                        </span>

                                        Dashboard administrativa

                                    </a>

                                </li>


                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                            <?php } ?>


                            <!-- SAIR -->

                            <li>

                                <a
                                    class="dropdown-item dropdown-logout"
                                    href="/logout.php"
                                >

                                    <span class="dropdown-icon">
                                        ↪
                                    </span>

                                    Sair

                                </a>

                            </li>


                        </ul>

                    </li>


                <?php } ?>


            </ul>

        </div>

    </div>

</nav>


<main>
