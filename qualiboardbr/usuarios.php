<?php
session_start();
require_once 'config/conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'admin') { header("Location: index.php"); exit; }

if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];
    $uid = $_GET['uid'];
    if ($acao === 'aprovar' && isset($_GET['pid'])) {
        $stmt = $pdo->prepare("UPDATE usuario_projeto SET status = 'aprovado' WHERE usuario_id = ? AND projeto_id = ?");
        $stmt->execute([$uid, $_GET['pid']]);
    } elseif ($acao === 'rejeitar' && isset($_GET['pid'])) {
        $stmt = $pdo->prepare("DELETE FROM usuario_projeto WHERE usuario_id = ? AND projeto_id = ?");
        $stmt->execute([$uid, $_GET['pid']]);
    } elseif ($acao === 'excluir_user') {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$uid]);
    }
    header("Location: usuarios.php?msg=sucesso");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] === 'editar_user') {
    $id_edit = $_POST['id'];
    $nome_edit = trim($_POST['nome']);
    $email_edit = trim($_POST['email']);
    $perfil_edit = $_POST['perfil'];
    $stmt_update = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?");
    $stmt_update->execute([$nome_edit, $email_edit, $perfil_edit, $id_edit]);
    header("Location: usuarios.php?msg=editado");
    exit;
}

$pendentes = $pdo->query("SELECT up.usuario_id, up.projeto_id, u.nome AS usuario, p.nome AS projeto FROM usuario_projeto up JOIN usuarios u ON up.usuario_id = u.id JOIN projetos p ON up.projeto_id = p.id WHERE up.status = 'pendente'")->fetchAll(PDO::FETCH_ASSOC);
$todos_usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - QualiBoardBR</title>
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
                <a href="index.php" class="btn btn-sm btn-outline-light border-0 fw-bold"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'editado'): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show"><i class="bi bi-check2-circle me-2"></i>Usuário atualizado com sucesso! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Administração da Equipe</h3>
            <p class="text-muted small">Aprove acessos e gerencie os perfis dos membros do sistema.</p>
        </div>

        <?php if(count($pendentes) > 0): ?>
            <h5 class="mb-3 text-warning fw-bold"><i class="bi bi-clock-history me-1"></i> Solicitações Pendentes</h5>
            <div class="card shadow-sm border-0 rounded-4 mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive border-0">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-warning bg-opacity-10 text-warning">
                                <tr><th class="ps-4 rounded-top-4">Usuário</th><th>Deseja acessar o projeto:</th><th class="text-end pe-4 rounded-top-4">Ações</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($pendentes as $req): ?>
                                    <tr class="border-bottom">
                                        <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($req['usuario']) ?></td>
                                        <td class="text-secondary fw-medium"><?= htmlspecialchars($req['projeto']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="?acao=aprovar&uid=<?= $req['usuario_id'] ?>&pid=<?= $req['projeto_id'] ?>" class="btn btn-sm btn-success fw-bold px-3 shadow-sm me-1 rounded-pill">Aprovar</a>
                                            <a href="?acao=rejeitar&uid=<?= $req['usuario_id'] ?>&pid=<?= $req['projeto_id'] ?>" class="btn btn-sm btn-light text-danger fw-bold px-3 shadow-sm rounded-pill border">Rejeitar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h5 class="mb-3 text-primary fw-bold"><i class="bi bi-person-lines-fill me-1"></i> Todos os Usuários</h5>
        <div class="card shadow-sm border-0 rounded-4 mb-5">
            <div class="card-body p-0">
                <div class="table-responsive border-0">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr><th class="ps-4">ID</th><th>Nome</th><th>E-mail</th><th>Perfil</th><th class="text-end pe-4">Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($todos_usuarios as $u): ?>
                                <tr class="border-bottom">
                                    <td class="ps-4 text-muted fw-bold small">#<?= str_pad($u['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($u['nome']) ?></td>
                                    <td class="text-secondary fw-medium"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php $badge_bg = $u['perfil'] == 'admin' ? 'bg-dark text-white' : 'bg-primary bg-opacity-10 text-primary border border-primary-subtle'; ?>
                                        <span class="badge <?= $badge_bg ?> px-3 py-2 rounded-pill fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            <?= strtoupper($u['perfil']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-white text-primary rounded-circle shadow-sm border border-light-subtle me-1" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $u['id'] ?>" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                                            <a href="?acao=excluir_user&uid=<?= $u['id'] ?>" class="btn btn-sm btn-white text-danger rounded-circle shadow-sm border border-light-subtle" onclick="return confirm('Tem certeza que deseja excluir este usuário?')" title="Excluir">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalEditar<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-header bg-primary text-white border-0">
                                                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear me-2"></i></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body text-start p-4">
                                                    <input type="hidden" name="acao" value="editar_user">
                                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fw-bold small text-uppercase">Nome Completo</label>
                                                        <input type="text" name="nome" class="form-control bg-light border-0 py-2 fw-medium" value="<?= htmlspecialchars($u['nome']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fw-bold small text-uppercase">E-mail Corporativo</label>
                                                        <input type="email" name="email" class="form-control bg-light border-0 py-2 fw-medium" value="<?= htmlspecialchars($u['email']) ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label text-muted fw-bold small text-uppercase">Nível de Acesso</label>
                                                        <select name="perfil" class="form-select bg-light border-0 py-2 fw-medium" required>
                                                            <option value="comum" <?= $u['perfil'] == 'comum' ? 'selected' : '' ?>>Comum (Acesso restrito)</option>
                                                            <option value="admin" <?= $u['perfil'] == 'admin' ? 'selected' : '' ?>>Admin (Acesso total)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                                                    <button type="button" class="btn btn-white border px-4 fw-medium text-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>