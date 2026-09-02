<?php

session_start();

$erro_login = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | TechNote</title>

    <!-- GOOGLE FONTS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

<link
    href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet"
>

    <!-- CSS PRINCIPAL -->

    <link
    rel="stylesheet"
    href="css/login.css"
>

</head>


<body class="login-page">


    <!-- ==========================================
         LADO ESQUERDO
    =========================================== -->

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
                    TECNOLOGIA
                </span>

                <h1>
                    DO SEU<br>
                    JEITO.
                </h1>

                <p>
                    Encontre o notebook ideal para
                    acompanhar sua rotina.
                </p>

            </div>

            <div class="login-line"></div>

            <span class="login-visual-footer">
                PERFORMANCE · DESIGN · TECNOLOGIA
            </span>

        </div>

    </section>


    <!-- ==========================================
         LADO DIREITO / FORMULÁRIO
    =========================================== -->

    <section class="login-area">

        <div class="login-box">


            <!-- CABEÇALHO -->

            <div class="login-header">

                <span>
                    BEM-VINDO DE VOLTA
                </span>

                <h2>
                    Entrar
                </h2>

                <p>
                    Acesse sua conta para continuar.
                </p>

            </div>


            <!-- MENSAGEM DE ERRO -->

            <?php if ($erro_login !== '') { ?>

    <div class="login-message login-error">

        <strong>
            Não foi possível entrar.
        </strong>

        <span>
            <?php echo htmlspecialchars($erro_login); ?>
        </span>

    </div>

<?php } ?>


            <!-- MENSAGEM DE LOGOUT -->

            <?php if (isset($_GET['logout'])) { ?>

                <div class="login-message login-success">

                    Você saiu da sua conta com sucesso.

                </div>

            <?php } ?>


            <!-- FORMULÁRIO -->

            <form
    action="auth/verificar.php"
    method="POST"
    class="login-form"
>


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

                    <div class="login-label-row">

                        <label for="senha">
                            SENHA
                        </label>

                        <a href="#">
                            Esqueci minha senha
                        </a>

                    </div>

                    <input
                        type="password"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        required
                        autocomplete="current-password"
                    >

                </div>


                <!-- BOTÃO -->

                <button
                    type="submit"
                    class="login-button"
                >

                    ENTRAR

                    <span>
                        →
                    </span>

                </button>


            </form>


            <!-- CADASTRO -->

            <div class="login-register">

                <span>
                    Ainda não possui uma conta?
                </span>

                <a href="cadastro.php">
                    Criar minha conta
                </a>

            </div>


            <!-- VOLTAR -->

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