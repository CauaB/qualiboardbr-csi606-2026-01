<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'config/conexao.php';
$is_admin = ($_SESSION['usuario_perfil'] === 'admin');

// AÇÕES VIA POST (Novo Projeto ou Editar Projeto)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    if ($is_admin) {
        $acao = $_POST['acao'];
        $nome = trim($_POST['nome']);
        $descricao = trim($_POST['descricao']);
        $imagem = "";

        // Lógica de Upload da Imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $extensoes = ['jpg', 'jpeg', 'png'];
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (in_array($extensao, $extensoes)) {
                $dir = 'uploads/';
                if (!is_dir($dir)) { mkdir($dir, 0777, true); }
                $caminho = $dir . uniqid() . '.' . $extensao;
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) { $imagem = $caminho; }
            }
        }

        if ($acao === 'novo_projeto') {
            if (empty($imagem)) { $imagem = "https://ui-avatars.com/api/?name=" . urlencode($nome) . "&background=random&color=fff&size=400&font-size=0.33"; }
            $stmt = $pdo->prepare("INSERT INTO projetos (nome, descricao, imagem) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $descricao, $imagem]);
            header("Location: projetos.php?msg=criado"); exit;
        } 
        elseif ($acao === 'editar_projeto') {
            $id_edit = $_POST['id'];
            if (empty($imagem)) {
                $stmt_img = $pdo->prepare("SELECT imagem FROM projetos WHERE id = ?");
                $stmt_img->execute([$id_edit]);
                $imagem = $stmt_img->fetchColumn();
            }
            $stmt = $pdo->prepare("UPDATE projetos SET nome = ?, descricao = ?, imagem = ? WHERE id = ?");
            $stmt->execute([$nome, $descricao, $imagem, $id_edit]);
            header("Location: projetos.php?msg=editado"); exit;
        }
    }
}

// Buscas no Banco
$projetos = $pdo->query("SELECT * FROM projetos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$membros_query = $pdo->query("SELECT up.projeto_id, u.nome, u.perfil FROM usuario_projeto up JOIN usuarios u ON up.usuario_id = u.id WHERE up.status = 'aprovado' AND u.perfil != 'admin'")->fetchAll(PDO::FETCH_ASSOC);
$membros_por_projeto = [];
foreach ($membros_query as $m) { $membros_por_projeto[$m['projeto_id']][] = $m; }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Projetos - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .card-img-top { height: 160px; object-fit: cover; }
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }
        .btn-edit-float { position: absolute; top: 10px; right: 10px; z-index: 10; opacity: 0.9; border: none; }
        .btn-edit-float:hover { opacity: 1; transform: scale(1.1); }
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
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'criado'): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4"><i class="bi bi-check2-circle me-2"></i>Projeto criado com sucesso! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'editado'): ?>
            <div class="alert alert-info border-0 shadow-sm alert-dismissible fade show rounded-4"><i class="bi bi-pencil-square me-2"></i>Projeto atualizado com sucesso! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Projetos</h3>
                <p class="text-muted small">Visualize as informações e equipes de cada sistema.</p>
            </div>
            <?php if ($is_admin): ?>
                <button type="button" class="btn btn-primary fw-bold shadow-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#modalNovoProjeto">
                    <i class="bi bi-plus-lg me-1"></i> Novo Projeto
                </button>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($projetos as $p): ?>
                <?php 
                    $img_src = !empty($p['imagem']) ? htmlspecialchars($p['imagem']) : "https://ui-avatars.com/api/?name=".urlencode($p['nome'])."&background=0d6efd&color=fff&size=400"; 
                    $membros = isset($membros_por_projeto[$p['id']]) ? $membros_por_projeto[$p['id']] : [];
                    $num_membros = count($membros);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 hover-lift position-relative overflow-hidden">
                        
                        <?php if ($is_admin): ?>
                            <button type="button" class="btn btn-light text-primary btn-sm rounded-circle shadow-sm btn-edit-float" data-bs-toggle="modal" data-bs-target="#modalEditarProjeto<?= $p['id'] ?>" title="Editar Projeto">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                        <?php endif; ?>

                        <img src="<?= $img_src ?>" class="card-img-top" alt="Capa do Projeto">
                        
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($p['nome']) ?></h5>
                            <p class="card-text text-secondary small mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($p['descricao']) ?: 'Nenhuma descrição fornecida.' ?>
                            </p>
                            
                            <h6 class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem;"><i class="bi bi-people-fill me-1"></i> Equipe Operacional</h6>
                            <div>
                                <?php if ($num_membros > 0): ?>
                                    <button class="btn btn-sm btn-light text-primary border border-primary-subtle fw-medium shadow-sm w-100 text-start rounded-3" data-bs-toggle="modal" data-bs-target="#modalEquipe<?= $p['id'] ?>">
                                        <i class="bi bi-person-lines-fill me-2"></i> Ver Integrantes (<?= $num_membros ?>)
                                    </button>
                                <?php else: ?>
                                    <div class="p-2 bg-light rounded-3 border text-muted small text-center fst-italic">Nenhum membro vinculado.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($num_membros > 0): ?>
                <div class="modal fade" id="modalEquipe<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content border-0 shadow rounded-4">
                            <div class="modal-header bg-light border-bottom-0 pb-0 rounded-top-4">
                                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-diagram-3-fill me-2"></i>Equipe: <?= htmlspecialchars($p['nome']) ?></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-3">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($membros as $membro): ?>
                                        <li class="list-group-item px-0 py-2 border-0 d-flex align-items-center bg-transparent">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-weight: bold;">
                                                <?= strtoupper(substr($membro['nome'], 0, 1)) ?>
                                            </div>
                                            <span class="fw-medium text-secondary"><?= htmlspecialchars($membro['nome']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                <div class="modal fade" id="modalEditarProjeto<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow rounded-4">
                            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Editar Projeto</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body p-4 text-start">
                                    <input type="hidden" name="acao" value="editar_projeto">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-bold small text-uppercase">Nome do Produto/Projeto <span class="text-danger">*</span></label>
                                        <input type="text" name="nome" class="form-control bg-light border-0 py-2 rounded-3" value="<?= htmlspecialchars($p['nome']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-bold small text-uppercase">Descrição Curta</label>
                                        <textarea name="descricao" class="form-control bg-light border-0 py-2 rounded-3" rows="3"><?= htmlspecialchars($p['descricao']) ?></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fw-bold small text-uppercase">Nova Imagem de Capa (JPG, PNG)</label>
                                        <input type="file" name="imagem" class="form-control bg-light border-0 py-2 rounded-3" accept=".jpg, .jpeg, .png">
                                        <div class="form-text small">Deixe em branco para manter a imagem atual.</div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                                    <button type="button" class="btn btn-white text-secondary px-4 fw-medium border" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Salvar Alterações</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($is_admin): ?>
    <div class="modal fade" id="modalNovoProjeto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-2"></i>Cadastrar Novo Projeto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4 text-start">
                        <input type="hidden" name="acao" value="novo_projeto">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small text-uppercase">Nome do Produto/Projeto <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control bg-light border-0 py-2 rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small text-uppercase">Descrição Curta</label>
                            <textarea name="descricao" class="form-control bg-light border-0 py-2 rounded-3" rows="3"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-muted fw-bold small text-uppercase">Imagem de Capa (JPG, PNG)</label>
                            <input type="file" name="imagem" class="form-control bg-light border-0 py-2 rounded-3" accept=".jpg, .jpeg, .png">
                            <div class="form-text small">Selecione um arquivo ou deixe em branco para capa automática.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 rounded-bottom-4">
                        <button type="button" class="btn btn-white text-secondary px-4 fw-medium border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Cadastrar Projeto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>