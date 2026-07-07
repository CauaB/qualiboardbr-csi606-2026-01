<?php
// Configurações do seu banco local (XAMPP padrão)
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Conecta ao MySQL sem selecionar banco ainda
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Cria o banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS qualiboard_br CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE qualiboard_br");

    // 2. Cria a tabela usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        perfil ENUM('admin', 'comum') DEFAULT 'comum',
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Cria a tabela projetos
    $pdo->exec("CREATE TABLE IF NOT EXISTS projetos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        imagem VARCHAR(500) DEFAULT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Cria a tabela usuario_projeto (Tabela intermediária N:M)
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuario_projeto (
        usuario_id INT NOT NULL,
        projeto_id INT NOT NULL,
        status ENUM('pendente', 'aprovado') DEFAULT 'pendente',
        PRIMARY KEY (usuario_id, projeto_id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    // 5. Cria a tabela bugs
    $pdo->exec("CREATE TABLE IF NOT EXISTS bugs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        projeto_id INT NOT NULL,
        titulo VARCHAR(150) NOT NULL,
        descricao TEXT NOT NULL,
        passos_reproducao TEXT,
        prioridade ENUM('Baixa', 'Média', 'Alta') DEFAULT 'Média',
        status ENUM('Aberto', 'Em Correção', 'Corrigido') DEFAULT 'Aberto',
        arquivo VARCHAR(500) DEFAULT NULL,
        data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
    )");

    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    echo "<h2 style='color: #10b981;'>Sucesso! Banco e tabelas criados.</h2>";
    echo "<p>Toda a estrutura do banco <strong>qualiboard_br</strong> está pronta.</p>";
    echo "<p>Agora você pode acessar o arquivo <strong>povoar.php</strong> para inserir os dados de teste.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h2 style='color: red; font-family: sans-serif;'>Erro ao criar o banco: " . $e->getMessage() . "</h2>";
}
?>