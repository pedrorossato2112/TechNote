<?php

session_start();

require_once "../conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$email = trim($_POST["email"] ?? '');
$senha = $_POST["senha"] ?? '';

if ($email === '' || $senha === '') {
    $_SESSION['erro_login'] = "Preencha todos os campos.";
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT id, nome, email, senha, tipo 
        FROM usuarios 
        WHERE email = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['erro_login'] = "Erro ao consultar o banco de dados.";
    header("Location: ../login.php");
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
    header("Location: ../login.php");
    exit;
}

session_regenerate_id(true);

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_tipo'] = $usuario['tipo'];

$stmt->close();

if ($usuario['tipo'] === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit;
}

header("Location: ../index.php");
exit;