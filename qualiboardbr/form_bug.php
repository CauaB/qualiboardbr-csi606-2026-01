<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'config/conexao.php';
$usuario_id = $_SESSION['usuario_id'];
$is_admin = ($_SESSION['usuario_perfil'] === 'admin');
$id = isset($_GET['id']) ? $_GET['id'] : null;
$bug = null;

if ($id) {
    $stmt_bug = $pdo->prepare("SELECT * FROM bugs WHERE id = :id");
    $stmt_bug->execute(['id' => $id]);
    $bug = $stmt_bug->fetch(PDO::FETCH_ASSOC);
    if (!$bug) { header("Location: index.php"); exit; }
    if (!$is_admin) {
        $stmt_check = $pdo->prepare("SELECT 1 FROM usuario_projeto WHERE usuario_id = ? AND projeto_id = ? AND status = 'aprovado'");
        $stmt_check->execute([$usuario_id, $bug['projeto_id']]);
        if (!$stmt_check->fetch()) { header("Location: index.php"); exit; }
    }
}
if ($is_admin) { $projetos = $pdo->query("SELECT * FROM projetos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC); } 
else {
    $stmt_proj = $pdo->prepare("SELECT p.* FROM projetos p JOIN usuario_projeto up ON p.id = up.projeto_id WHERE up.usuario_id = :uid AND up.status = 'aprovado' ORDER BY p.nome");
    $stmt_proj->execute(['uid' => $usuario_id]);
    $projetos = $stmt_proj->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Novo' ?> Bug - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="container py-1">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                    <i class="bi bi-bug-fill"></i>
                </div>
                QualiBoard<span class="text-primary">BR</span>
            </a>
            <div class="d-flex text-white align-items-center gap-3">
                <a href="index.php" class="btn btn-sm btn-outline-light border-0 fw-bold"><i class="bi bi-arrow-left me-1"></i> Cancelar</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">
                        <?= $id ? 'Editar Ocorrência <span class="text-primary">#'.str_pad($id, 4, '0', STR_PAD_LEFT).'</span>' : 'Registrar Falha' ?>
                    </h3>
                    <p class="text-muted small">Preencha os detalhes técnicos para envio à equipe.</p>
                </div>
                
                <?php if (count($projetos) === 0): ?>
                    <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4">
                        <h5 class="fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Acesso Restrito</h5>
                        <p class="mb-0 text-secondary">Você ainda não possui acesso aprovado a nenhum projeto no sistema.</p>
                    </div>
                <?php else: ?>
                    <form action="acao_bug.php" method="POST" enctype="multipart/form-data" class="card p-4 p-md-5 border-0 shadow-sm rounded-4">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase">Projeto Relacionado <span class="text-danger">*</span></label>
                            <select name="projeto_id" class="form-select bg-light border-0 py-2 text-dark fw-medium rounded-3" required>
                                <option value="">Selecione o projeto...</option>
                                <?php foreach ($projetos as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($bug && $bug['projeto_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase">Título do Defeito <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" class="form-control bg-light border-0 py-2 text-dark fw-medium rounded-3" value="<?= $bug ? htmlspecialchars($bug['titulo']) : '' ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase">Descrição Detalhada <span class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control bg-light border-0 py-2 text-dark fw-medium rounded-3" rows="4" required><?= $bug ? htmlspecialchars($bug['descricao']) : '' ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase">Passos para Reproduzir</label>
                            <textarea name="passos_reproducao" class="form-control bg-light border-0 py-2 text-dark fw-medium rounded-3" rows="3"><?= $bug ? htmlspecialchars($bug['passos_reproducao']) : '' ?></textarea>
                        </div>

                        <div class="mb-4 p-4 bg-light rounded-4 border border-light-subtle">
                            <label class="form-label text-muted fw-bold small text-uppercase mb-3"><i class="bi bi-paperclip me-1"></i> Evidência Visual</label>
                            <input type="file" name="arquivo" class="form-control bg-white border py-2 rounded-3" accept="image/*, video/mp4, video/webm">
                            
                            <?php if ($bug && !empty($bug['arquivo'])): ?>
                                <div class="form-text mt-3 text-secondary d-flex align-items-center">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle me-2 px-2 py-1"><i class="bi bi-check-circle-fill"></i> Anexo ativo</span>
                                    <a href="<?= htmlspecialchars($bug['arquivo']) ?>" target="_blank" class="fw-bold text-decoration-none text-primary">Ver Mídia Atual</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row mb-5 mt-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label text-muted fw-bold small text-uppercase">Prioridade <span class="text-danger">*</span></label>
                                <select name="prioridade" class="form-select bg-light border-0 py-2 text-dark fw-medium rounded-3" required>
                                    <option value="Baixa" <?= ($bug && $bug['prioridade'] == 'Baixa') ? 'selected' : '' ?>>Baixa</option>
                                    <option value="Média" <?= ($bug && $bug['prioridade'] == 'Média') ? 'selected' : '' ?>>Média</option>
                                    <option value="Alta" <?= ($bug && $bug['prioridade'] == 'Alta') ? 'selected' : '' ?>>Alta</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold small text-uppercase">Status</label>
                                <select name="status" class="form-select bg-light border-0 py-2 text-dark fw-medium rounded-3">
                                    <option value="Aberto" <?= ($bug && $bug['status'] == 'Aberto') ? 'selected' : '' ?>>Aberto</option>
                                    <option value="Em Correção" <?= ($bug && $bug['status'] == 'Em Correção') ? 'selected' : '' ?>>Em Correção</option>
                                    <option value="Corrigido" <?= ($bug && $bug['status'] == 'Corrigido') ? 'selected' : '' ?>>Corrigido</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm rounded-3"><i class="bi bi-save-fill me-2"></i><?= $id ? 'Salvar Alterações' : 'Registrar Falha' ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>