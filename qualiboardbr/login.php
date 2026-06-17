<?php
session_start();
if (isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-image">
                <h1 class="fw-bold mb-4"><i class="bi bi-bug-fill"></i> QualiBoardBR</h1>
                <p class="fs-5 text-white-50">Centralize o registro e acompanhamento de falhas técnicas com excelência e organização.</p>
            </div>
            
            <div class="auth-form">
                <h3 class="fw-bold text-dark mb-2">Bem-vindo!</h3>
                <p class="text-muted mb-4">Insira suas credenciais para acessar o painel.</p>

                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> Conta criada! Faça seu login.</div>
                <?php endif; ?>
                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle-fill me-2"></i> E-mail ou senha incorretos.</div>
                <?php endif; ?>

                <form action="valida_login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small text-uppercase">E-mail Corporativo</label>
                        <input type="email" name="email" class="form-control auth-input" placeholder="seu.email@empresa.com.br" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-semibold small text-uppercase">Senha</label>
                        <input type="password" name="senha" class="form-control auth-input" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-4">Entrar no Sistema</button>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted">Ainda não tem acesso?</span> 
                        <a href="cadastro.php" class="text-decoration-none fw-bold text-primary">Criar uma conta</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>