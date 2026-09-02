<?php

session_start();

$erro_cadastro = $_SESSION['erro_cadastro'] ?? '';
unset($_SESSION['erro_cadastro']);

$sucesso_cadastro = $_SESSION['sucesso_cadastro'] ?? '';
unset($_SESSION['sucesso_cadastro']);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Criar conta | TechNote</title>


    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="css/login.css"
    >

</head>


<body class="login-page">


    <!-- LADO ESQUERDO -->

    <section class="login-visual">

        <div class="login-visual-content">

            <a
                href="index.php"
                class="login-logo"
            >
                TECHNOTE
            </a>


            <div class="login-phrase">

                <span>
                    FAÇA PARTE
                </span>

                <h1>
                    DO SEU<br>
                    JEITO.
                </h1>

                <p>
                    Crie sua conta e tenha acesso
                    completo aos produtos e recursos
                    da TechNote.
                </p>

            </div>


            <div>

                <div class="login-line"></div>

                <span class="login-visual-footer">
                    PERFORMANCE · DESIGN · TECNOLOGIA
                </span>

            </div>

        </div>

    </section>


    <!-- ÁREA DO CADASTRO -->

    <section class="login-area">

        <div class="login-box">


            <div class="login-header">

                <span>
                    NOVO CLIENTE
                </span>

                <h2>
                    Criar conta
                </h2>

                <p>
                    Preencha seus dados para começar.
                </p>

            </div>


            <?php if ($erro_cadastro !== '') { ?>

                <div class="login-message login-error">

                    <strong>
                        Não foi possível criar sua conta.
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($erro_cadastro); ?>
                    </span>

                </div>

            <?php } ?>


            <?php if ($sucesso_cadastro !== '') { ?>

                <div class="login-message login-success">

                    <?php echo htmlspecialchars($sucesso_cadastro); ?>

                </div>

            <?php } ?>


            <form
                action="auth/cadastrar.php"
                method="POST"
                class="login-form"
            >


                <!-- NOME -->

                <div class="login-field">

                    <label for="nome">
                        NOME
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Digite seu nome"
                        required
                        autocomplete="name"
                    >

                </div>


                <!-- EMAIL -->

                <div class="login-field">

                    <label for="email">
                        E-MAIL
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail"
                        required
                        autocomplete="email"
                    >

                </div>


                <!-- SENHA -->

                <div class="login-field">

                    <label for="senha">
                        SENHA
                    </label>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Crie uma senha"
                        required
                        minlength="6"
                        autocomplete="new-password"
                    >

                </div>


                <!-- CONFIRMAR SENHA -->

                <div class="login-field">

                    <label for="confirmar_senha">
                        CONFIRMAR SENHA
                    </label>

                    <input
                        type="password"
                        id="confirmar_senha"
                        name="confirmar_senha"
                        placeholder="Digite a senha novamente"
                        required
                        minlength="6"
                        autocomplete="new-password"
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >

                    CRIAR CONTA

                    <span>
                        →
                    </span>

                </button>


            </form>


            <div class="login-register">

                <span>
                    Já possui uma conta?
                </span>

                <a href="login.php">
                    Fazer login
                </a>

            </div>


            <a
                href="index.php"
                class="login-back"
            >

                ← Voltar para o site

            </a>


        </div>

    </section>


</body>

</html>