<?php

$host = "192.168.56.101";
$usuario = "root";
$senha = "";
$banco = "technote";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}