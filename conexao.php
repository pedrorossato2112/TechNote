<?php

$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASSWORD') ?: '';
$banco = getenv('DB_NAME') ?: 'technote';

$conn = new mysqli($host, $usuario, $senha, $banco);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
