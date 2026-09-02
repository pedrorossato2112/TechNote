<?php

session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$erros = [];
$sucesso = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $conn->prepare('SELECT nome, email, tipo, criado_em FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION = [];
    session_destroy();
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $erros[] = 'Sua sessão expirou. Atualize a página e tente novamente.';
    }

    if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
        $erros[] = 'Informe um nome entre 3 e 100 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erros[] = 'Informe um e-mail válido.';
    }

    if ($novaSenha !== '' && strlen($novaSenha) < 6) {
        $erros[] = 'A nova senha deve ter pelo menos 6 caracteres.';
    }

    if ($novaSenha !== $confirmarSenha) {
        $erros[] = 'A confirmação da nova senha não corresponde.';
    }

    if (empty($erros)) {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
        $stmt->bind_param('si', $email, $usuarioId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $erros[] = 'Este e-mail já está sendo usado por outra conta.';
        }
        $stmt->close();
    }

    if (empty($erros)) {
        $emailAnterior = $usuario['email'];
        $conn->begin_transaction();

        try {
            if ($novaSenha !== '') {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?');
                $stmt->bind_param('sssi', $nome, $email, $senhaHash, $usuarioId);
            } else {
                $stmt = $conn->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
                $stmt->bind_param('ssi', $nome, $email, $usuarioId);
            }

            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare('UPDATE pedidos SET nome = ?, email = ? WHERE email = ?');
            $stmt->bind_param('sss', $nome, $email, $emailAnterior);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
            $sucesso = 'Seus dados foram atualizados com sucesso.';
        } catch (Throwable $erro) {
            $conn->rollback();
            $erros[] = 'Não foi possível atualizar sua conta. Tente novamente.';
        }
    }
}

include 'includes/header.php';
?>

<section class="titulo-pagina conta-titulo">
    <p>Área do cliente</p>
    <h2>Minha Conta</h2>
</section>

<section class="conta-section">
    <aside class="conta-resumo">
        <div class="conta-avatar">
            <?php echo htmlspecialchars(mb_strtoupper(mb_substr($usuario['nome'], 0, 1))); ?>
        </div>

        <span>Perfil <?php echo $usuario['tipo'] === 'admin' ? 'administrativo' : 'de cliente'; ?></span>
        <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
        <p><?php echo htmlspecialchars($usuario['email']); ?></p>

        <div class="conta-desde">
            Membro desde
            <strong><?php echo date('m/Y', strtotime($usuario['criado_em'])); ?></strong>
        </div>

        <a href="/meus_pedidos.php" class="conta-pedidos-link">Ver meus pedidos →</a>
    </aside>

    <div class="conta-form-card">
        <div class="conta-card-heading">
            <span>DADOS PESSOAIS</span>
            <h3>Informações da conta</h3>
            <p>Atualize seus dados. Deixe os campos de senha vazios para manter a senha atual.</p>
        </div>

        <?php if ($sucesso !== '') { ?>
            <div class="conta-alert conta-alert-success" role="status">
                ✓ <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php } ?>

        <?php if (!empty($erros)) { ?>
            <div class="conta-alert conta-alert-error" role="alert">
                <?php foreach ($erros as $erro) { ?>
                    <p>⚠ <?php echo htmlspecialchars($erro); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" class="conta-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label>
                <span>Nome completo</span>
                <input type="text" name="nome" maxlength="100" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </label>

            <label>
                <span>E-mail</span>
                <input type="email" name="email" maxlength="150" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </label>

            <div class="conta-form-grid">
                <label>
                    <span>Nova senha</span>
                    <input type="password" name="nova_senha" minlength="6" autocomplete="new-password" placeholder="Mínimo de 6 caracteres">
                </label>

                <label>
                    <span>Confirmar nova senha</span>
                    <input type="password" name="confirmar_senha" minlength="6" autocomplete="new-password" placeholder="Repita a nova senha">
                </label>
            </div>

            <button type="submit" class="conta-submit">Salvar alterações</button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
