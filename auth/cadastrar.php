<?php

session_start();

require_once "../conexao.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../cadastro.php");

    exit;
}


$nome = trim($_POST["nome"] ?? '');
$email = trim($_POST["email"] ?? '');
$senha = $_POST["senha"] ?? '';
$confirmar_senha = $_POST["confirmar_senha"] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DOS CAMPOS
|--------------------------------------------------------------------------
*/

if (
    $nome === '' ||
    $email === '' ||
    $senha === '' ||
    $confirmar_senha === ''
) {

    $_SESSION['erro_cadastro'] =
        "Preencha todos os campos.";

    header("Location: ../cadastro.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO E-MAIL
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['erro_cadastro'] =
        "Digite um e-mail válido.";

    header("Location: ../cadastro.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DA SENHA
|--------------------------------------------------------------------------
*/

if (strlen($senha) < 6) {

    $_SESSION['erro_cadastro'] =
        "A senha deve possuir pelo menos 6 caracteres.";

    header("Location: ../cadastro.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| CONFIRMAÇÃO DA SENHA
|--------------------------------------------------------------------------
*/

if ($senha !== $confirmar_senha) {

    $_SESSION['erro_cadastro'] =
        "As senhas não coincidem.";

    header("Location: ../cadastro.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| VERIFICAR SE O E-MAIL JÁ EXISTE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM usuarios
    WHERE email = ?
    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    $_SESSION['erro_cadastro'] =
        "Erro ao consultar o banco de dados.";

    header("Location: ../cadastro.php");

    exit;
}


$stmt->bind_param("s", $email);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows > 0) {

    $stmt->close();

    $_SESSION['erro_cadastro'] =
        "Este e-mail já está cadastrado.";

    header("Location: ../cadastro.php");

    exit;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| CRIPTOGRAFAR SENHA
|--------------------------------------------------------------------------
*/

$senha_hash = password_hash(
    $senha,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| CRIAR CLIENTE
|--------------------------------------------------------------------------
*/

$tipo = "cliente";


$sql = "
    INSERT INTO usuarios
    (nome, email, senha, tipo, criado_em)
    VALUES (?, ?, ?, ?, NOW())
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    $_SESSION['erro_cadastro'] =
        "Erro ao preparar o cadastro.";

    header("Location: ../cadastro.php");

    exit;
}


$stmt->bind_param(
    "ssss",
    $nome,
    $email,
    $senha_hash,
    $tipo
);


if (!$stmt->execute()) {

    $stmt->close();

    $_SESSION['erro_cadastro'] =
        "Não foi possível criar a conta.";

    header("Location: ../cadastro.php");

    exit;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| CADASTRO REALIZADO
|--------------------------------------------------------------------------
*/

$_SESSION['sucesso_cadastro'] =
    "Conta criada com sucesso! Agora faça login.";


header("Location: ../cadastro.php");

exit;