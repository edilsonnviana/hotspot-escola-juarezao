<?php
// Configurações centralizadas do Banco de Dados
$host = 'localhost';
$db   = 'rede_escola_juarezao';
$user = 'professor';
$pass = 'senha123'; // Altere se houver senha no seu MySQL/MariaDB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Erro crítico de conexão com o banco de dados: " . $e->getMessage());
}
?>