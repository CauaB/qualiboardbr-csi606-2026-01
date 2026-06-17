<?php
require_once 'config/conexao.php';
$projetos = $pdo->query("SELECT * FROM projetos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $projetos_selecionados = isset($_POST['projetos']) ? $_POST['projetos'] : [];

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, 'comum')");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senha]);
        $usuario_id = $pdo->lastInsertId();

        if (!empty($projetos_selecionados)) {
            $stmt_v = $pdo->prepare("INSERT INTO usuario_projeto (usuario_id, projeto_id, status) VALUES (:uid, :pid, 'pendente')");
            foreach ($projetos_selecionados as $pid) { $stmt_v->execute(['uid' => $usuario_id, 'pid' => $pid]); }
        }
        header("Location: login.php?sucesso=1");
        exit;
    } catch (Exception $e) {
        $erro = "E-mail já está em uso.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-image" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
                <h1 class="fw-bold mb-4"><i class="bi bi-rocket-takeoff-fill"></i> Junte-se</h1>
                <p class="fs-5 text-white-50">Crie sua conta para reportar bugs e acompanhar a evolução dos projetos.</p>
            </div>
            <div class="auth-form" style="padding: 2rem 3rem;">
                <h3 class="fw-bold text-dark mb-4">Criar Conta</h3>
                
                <?php if(isset($erro)): ?> <div class="alert alert-danger border-0 shadow-sm"><?= $erro ?></div> <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small text-uppercase">Nome Completo</label>
                            <input type="text" name="nome" class="form-control auth-input" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-semibold small text-uppercase">E-mail Corporativo</label>
                            <input type="email" name="email" class="form-control auth-input" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small text-uppercase">Senha</label>
                        <input type="password" name="senha" class="form-control auth-input" required>
                    </div>
                    
                    <label class="form-label text-muted fw-semibold small text-uppercase mt-2">Deseja participar de quais projetos?</label>
                    <div class="border border-light rounded bg-light p-3 mb-4 shadow-sm" style="max-height: 140px; overflow-y: auto;">
                        <?php foreach ($projetos as $p): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="projetos[]" value="<?= $p['id'] ?>" id="p_<?= $p['id'] ?>">
                                <label class="form-check-label text-secondary" for="p_<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 mb-3">Concluir Cadastro</button>
                    <div class="text-center"><a href="login.php" class="text-decoration-none fw-bold text-muted"><i class="bi bi-arrow-left"></i> Voltar ao Login</a></div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>