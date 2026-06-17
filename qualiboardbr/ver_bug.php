<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = ($_SESSION['usuario_perfil'] === 'admin');
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT b.*, p.nome AS projeto_nome FROM bugs b JOIN projetos p ON b.projeto_id = p.id WHERE b.id = ?");
$stmt->execute([$id]);
$bug = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bug) { header("Location: index.php"); exit; }
if (!$is_admin) {
    $stmt_check = $pdo->prepare("SELECT 1 FROM usuario_projeto WHERE usuario_id = ? AND projeto_id = ? AND status = 'aprovado'");
    $stmt_check->execute([$usuario_id, $bug['projeto_id']]);
    if (!$stmt_check->fetch()) { header("Location: index.php"); exit; }
}

$is_video = $is_image = false;
if (!empty($bug['arquivo'])) {
    $ext = strtolower(pathinfo($bug['arquivo'], PATHINFO_EXTENSION));
    if (in_array($ext, ['mp4', 'webm'])) { $is_video = true; }
    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $is_image = true; }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bug #<?= $bug['id'] ?> - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        .bug-evidence { max-width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; }
        .info-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.5px; margin-bottom: 0.3rem; }
    </style>
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
                <a href="index.php" class="btn btn-sm btn-outline-light border-0 fw-bold"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3 py-1 fw-medium"><i class="bi bi-folder-fill me-1"></i> <?= htmlspecialchars($bug['projeto_nome']) ?></span>
                                <span class="text-muted fw-bold">Ticket #<?= str_pad($bug['id'], 4, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <h2 class="fw-bold text-dark mb-0"><?= htmlspecialchars($bug['titulo']) ?></h2>
                        </div>
                        <a href="form_bug.php?id=<?= $bug['id'] ?>" class="btn btn-outline-primary fw-bold shadow-sm rounded-3"><i class="bi bi-pencil-fill me-1"></i> Editar</a>
                    </div>

                    <div class="row bg-light rounded-4 p-4 mb-4 border border-light-subtle g-3">
                        <div class="col-md-4 border-end border-light-subtle">
                            <div class="info-label">Status Atual</div>
                            <?php $corStatus = $bug['status'] == 'Corrigido' ? 'success' : ($bug['status'] == 'Em Correção' ? 'primary' : 'secondary'); ?>
                            <span class="text-<?= $corStatus ?> fw-bold fs-5"><i class="bi bi-circle-fill me-1" style="font-size: 0.6rem; vertical-align: middle;"></i> <?= mb_strtoupper($bug['status']) ?></span>
                        </div>
                        <div class="col-md-4 border-end border-light-subtle">
                            <div class="info-label">Prioridade</div>
                            <?php 
                                $corPrio = $bug['prioridade'] == 'Alta' ? 'danger' : ($bug['prioridade'] == 'Média' ? 'warning' : 'info');
                                $iconePrio = $bug['prioridade'] == 'Alta' ? 'bi-fire' : 'bi-exclamation-circle';
                            ?>
                            <span class="text-<?= $corPrio ?> fw-bold fs-5"><i class="bi <?= $iconePrio ?> me-1"></i> <?= $bug['prioridade'] ?></span>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Aberto Em</div>
                            <span class="text-dark fw-bold fs-5"><i class="bi bi-calendar-event me-1 text-muted"></i> <?= date('d/m/Y', strtotime($bug['data_registro'])) ?></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="info-label text-primary"><i class="bi bi-card-text me-1"></i> Detalhamento</h6>
                        <div class="bg-white p-0 text-dark" style="font-size: 1rem; line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($bug['descricao'])) ?>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="info-label text-primary"><i class="bi bi-list-ol me-1"></i> Reprodução</h6>
                        <?php if(!empty($bug['passos_reproducao'])): ?>
                            <div class="bg-light p-4 rounded-4 text-secondary border border-light-subtle" style="line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($bug['passos_reproducao'])) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted fst-italic small">Nenhum passo fornecido.</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h6 class="info-label text-primary mb-3"><i class="bi bi-paperclip me-1"></i> Evidência Anexada</h6>
                        <?php if (empty($bug['arquivo'])): ?>
                            <div class="p-4 bg-light rounded-4 text-center text-muted border border-dashed border-light-subtle">
                                <i class="bi bi-image text-secondary opacity-25 d-block mb-2" style="font-size: 2rem;"></i>
                                Sem anexos.
                            </div>
                        <?php else: ?>
                            <div class="text-center bg-light p-3 rounded-4 border border-light-subtle">
                                <?php if ($is_image): ?>
                                    <img src="<?= htmlspecialchars($bug['arquivo']) ?>" class="bug-evidence shadow-sm" alt="Evidência do Bug">
                                <?php elseif ($is_video): ?>
                                    <video controls class="bug-evidence shadow-sm" style="max-height: 500px; width: 100%;">
                                        <source src="<?= htmlspecialchars($bug['arquivo']) ?>" type="video/<?= $ext ?>">
                                    </video>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($bug['arquivo']) ?>" class="btn btn-outline-primary fw-bold" target="_blank"><i class="bi bi-download me-1"></i> Baixar Arquivo Anexado</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>