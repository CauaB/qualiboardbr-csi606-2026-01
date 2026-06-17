<?php
// setup.php - ACESSE ESTA PÁGINA UMA VEZ PARA CRIAR O ADMIN
require_once 'config/conexao.php';

$nome = "Administrador";
$email = "admin@qualiboard.com.br";
$senha = "admin123"; // A senha que você vai digitar no login
$senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Criptografa a senha

try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
    $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha_hash]);
    echo "<h1>Usuário criado com sucesso!</h1><p>Email: $email | Senha: $senha</p><a href='login.php'>Ir para o Login</a>";
} catch (PDOException $e) {
    echo "Erro (ou usuário já existe): " . $e->getMessage();
}
?>