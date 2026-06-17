<?php
session_start();
require_once 'config/conexao.php';

if (isset($_SESSION['usuario_id']) && isset($_GET['id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $projeto_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO usuario_projeto (usuario_id, projeto_id) VALUES (:uid, :pid)");
        $stmt->execute(['uid' => $usuario_id, 'pid' => $projeto_id]);
    } catch (Exception $e) {}
}

header("Location: index.php");
exit;
?>