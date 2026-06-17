<?php
session_start();
require_once 'config/conexao.php';

// Trava de segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $projeto_id = $_POST['projeto_id'];
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $passos_reproducao = trim($_POST['passos_reproducao']);
    $prioridade = $_POST['prioridade'];
    $status = $_POST['status'];
    
    $arquivo_path = "";

    // Lógica de Upload do Arquivo (Evidência)
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        // Aceita imagens e vídeos comuns
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];
        $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $extensoes_permitidas)) {
            $diretorio = 'uploads/bugs/';
            if (!is_dir($diretorio)) { mkdir($diretorio, 0777, true); }
            
            $nome_arquivo = uniqid() . '.' . $extensao;
            $caminho_destino = $diretorio . $nome_arquivo;

            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_destino)) {
                $arquivo_path = $caminho_destino;
            }
        }
    }

    if (empty($id)) {
        // NOVO REGISTRO
        $sql = "INSERT INTO bugs (projeto_id, titulo, descricao, passos_reproducao, prioridade, status, arquivo) 
                VALUES (:projeto_id, :titulo, :descricao, :passos_reproducao, :prioridade, :status, :arquivo)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':arquivo', $arquivo_path);
    } else {
        // ATUALIZAÇÃO (EDIÇÃO)
        if (!empty($arquivo_path)) {
            // Se enviou um arquivo novo, atualiza tudo incluindo o arquivo
            $sql = "UPDATE bugs SET projeto_id = :projeto_id, titulo = :titulo, descricao = :descricao, 
                    passos_reproducao = :passos_reproducao, prioridade = :prioridade, status = :status, arquivo = :arquivo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':arquivo', $arquivo_path);
        } else {
            // Se não enviou arquivo novo, atualiza apenas os textos e mantém o arquivo antigo intacto
            $sql = "UPDATE bugs SET projeto_id = :projeto_id, titulo = :titulo, descricao = :descricao, 
                    passos_reproducao = :passos_reproducao, prioridade = :prioridade, status = :status 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
        }
        $stmt->bindParam(':id', $id);
    }

    $stmt->bindParam(':projeto_id', $projeto_id);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':passos_reproducao', $passos_reproducao);
    $stmt->bindParam(':prioridade', $prioridade);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Erro ao salvar os dados da ocorrência.";
    }
}
?>