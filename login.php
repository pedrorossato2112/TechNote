<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TechNote</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <main>
        <h1>Login</h1>

        <?php if ($erro): ?>
            <p><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="auth/verificar.php" method="POST">

            <div>
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >
            </div>

            <div>
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                >
            </div>

            <button type="submit">Entrar</button>

        </form>
    </main>

</body>
</html>