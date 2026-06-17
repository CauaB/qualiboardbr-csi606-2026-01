<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = ($_SESSION['usuario_perfil'] === 'admin');
$projeto_id_filtro = isset($_GET['projeto_id']) ? $_GET['projeto_id'] : '';

$stmt_meus_proj = $pdo->prepare("SELECT projeto_id FROM usuario_projeto WHERE usuario_id = ? AND status = 'aprovado'");
$stmt_meus_proj->execute([$usuario_id]);
$meus_projetos = $stmt_meus_proj->fetchAll(PDO::FETCH_COLUMN);

$projetos = $pdo->query("SELECT * FROM projetos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$sql_bugs = "SELECT b.*, p.nome AS projeto_nome FROM bugs b JOIN projetos p ON b.projeto_id = p.id";
if ($projeto_id_filtro) { $sql_bugs .= " WHERE b.projeto_id = :projeto_id"; }
$sql_bugs .= " ORDER BY FIELD(b.prioridade, 'Alta', 'Média', 'Baixa'), b.data_registro DESC";

$stmt = $pdo->prepare($sql_bugs);
if ($projeto_id_filtro) { $stmt->bindParam(':projeto_id', $projeto_id_filtro); }
$stmt->execute();
$bugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// LÓGICA DE INTELIGÊNCIA DOS DADOS (Para os KPIs e Gráficos)
$kpi_abertos = $kpi_correcao = $kpi_corrigidos = 0;
$prio_alta = $prio_media = $prio_baixa = 0;

foreach ($bugs as $b) {
    // Contagem de Status
    if ($b['status'] == 'Aberto') $kpi_abertos++;
    elseif ($b['status'] == 'Em Correção') $kpi_correcao++;
    elseif ($b['status'] == 'Corrigido') $kpi_corrigidos++;
    
    // Contagem de Prioridades
    if ($b['prioridade'] == 'Alta') $prio_alta++;
    elseif ($b['prioridade'] == 'Média') $prio_media++;
    elseif ($b['prioridade'] == 'Baixa') $prio_baixa++;
}
$kpi_total = count($bugs);

if (isset($_GET['solicitar_projeto_id']) && !$is_admin) {
    $req_proj = $_GET['solicitar_projeto_id'];
    $stmt_req = $pdo->prepare("INSERT IGNORE INTO usuario_projeto (usuario_id, projeto_id, status) VALUES (?, ?, 'pendente')");
    $stmt_req->execute([$usuario_id, $req_proj]);
    header("Location: index.php?msg=solicitado");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - QualiBoardBR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <span class="text-light fw-medium"><i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-light border-0 fw-bold"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'solicitado'): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4"><i class="bi bi-check2-circle me-2"></i>Solicitação enviada! Aguarde a aprovação de um Administrador. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row mb-4 align-items-end">
            <div class="col-md-7">
                <h3 class="fw-bold text-dark mb-3">Visão Geral da Qualidade</h3>
                <div class="d-flex gap-2">
                    <form method="GET" class="d-flex w-75 shadow-sm rounded bg-white">
                        <select name="projeto_id" class="form-select border-0 py-2 text-secondary fw-medium" onchange="this.form.submit()">
                            <option value="">📋 Filtrar: Todos os Projetos</option>
                            <?php foreach ($projetos as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $projeto_id_filtro == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    
                    <?php if (!$is_admin): ?>
                        <div class="dropdown shadow-sm">
                            <button class="btn btn-white border-0 bg-white dropdown-toggle h-100 px-3 text-secondary fw-medium rounded-3" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-add text-primary"></i>
                            </button>
                            <ul class="dropdown-menu border-0 shadow mt-2 rounded-4">
                                <?php foreach ($projetos as $p): ?>
                                    <?php if (!in_array($p['id'], $meus_projetos)): ?>
                                        <li><a class="dropdown-item py-2 fw-medium text-secondary" href="?solicitar_projeto_id=<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-5 text-end mt-3 mt-md-0">
                <div class="d-inline-flex gap-2 p-1 bg-white rounded-3 shadow-sm border border-light-subtle">
                    <?php if ($is_admin): ?>
                        <a href="usuarios.php" class="btn btn-light border-0 fw-bold text-secondary hover-primary"><i class="bi bi-people-fill"></i> Usuários</a>
                    <?php endif; ?>
                    <a href="projetos.php" class="btn btn-light border-0 fw-bold text-secondary hover-warning"><i class="bi bi-folder-fill text-warning"></i> Projetos</a>
                    <a href="form_bug.php" class="btn btn-primary fw-bold px-3 shadow-sm rounded-3"><i class="bi bi-plus-lg me-1"></i> Novo Bug</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="kpi-card p-3 p-xl-4 d-flex align-items-center gap-3 shadow-sm border-0 rounded-4">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-bug-fill"></i></div>
                    <div>
                        <div class="kpi-title">Total Listado</div>
                        <div class="kpi-value"><?= $kpi_total ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card p-3 p-xl-4 d-flex align-items-center gap-3 shadow-sm border-0 rounded-4">
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-octagon-fill"></i></div>
                    <div>
                        <div class="kpi-title">Abertos</div>
                        <div class="kpi-value text-danger"><?= $kpi_abertos ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card p-3 p-xl-4 d-flex align-items-center gap-3 shadow-sm border-0 rounded-4">
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-tools"></i></div>
                    <div>
                        <div class="kpi-title">Em Correção</div>
                        <div class="kpi-value text-warning"><?= $kpi_correcao ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card p-3 p-xl-4 d-flex align-items-center gap-3 shadow-sm border-0 rounded-4">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <div class="kpi-title">Corrigidos</div>
                        <div class="kpi-value text-success"><?= $kpi_corrigidos ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($kpi_total > 0): ?>
        <div class="row g-4 mb-4">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-secondary text-uppercase small mb-4"><i class="bi bi-pie-chart-fill me-2"></i>Distribuição por Status</h6>
                        <div style="height: 250px; position: relative;" class="d-flex justify-content-center">
                            <canvas id="graficoStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-secondary text-uppercase small mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Volume por Prioridade</h6>
                        <div style="height: 250px; position: relative;">
                            <canvas id="graficoPrioridade"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 mb-5">
            <div class="card-body p-0">
                <div class="table-responsive border-0 rounded-4">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light text-secondary border-bottom border-light-subtle">
                            <tr>
                                <th class="ps-4 py-3">Ticket</th>
                                <th class="py-3">Projeto</th>
                                <th class="py-3">Título do Defeito</th>
                                <th class="py-3">Prioridade</th>
                                <th class="py-3">Status</th>
                                <th class="text-end pe-4 py-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($kpi_total > 0): ?>
                                <?php foreach ($bugs as $bug): 
                                    $pode_editar = $is_admin || in_array($bug['projeto_id'], $meus_projetos);
                                    $classe_linha = $pode_editar ? '' : 'linha-bloqueada';
                                ?>
                                    <tr class="border-bottom border-light-subtle <?= $classe_linha ?>">
                                        <td class="ps-4 text-muted fw-bold small">#<?= str_pad($bug['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <span class="d-inline-flex align-items-center gap-2 bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-1 fw-medium" style="font-size: 0.8rem;">
                                                <i class="bi bi-folder2-open"></i> <?= htmlspecialchars($bug['projeto_nome']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($bug['titulo']) ?></td>
                                        <td>
                                            <?php 
                                                $corPrio = $bug['prioridade'] == 'Alta' ? 'danger' : ($bug['prioridade'] == 'Média' ? 'warning' : 'info');
                                                $iconePrio = $bug['prioridade'] == 'Alta' ? 'bi-fire' : 'bi-exclamation-circle';
                                            ?>
                                            <span class="badge bg-<?= $corPrio ?> bg-opacity-10 text-<?= $corPrio ?> px-2 py-1 rounded fw-bold border border-<?= $corPrio ?>-subtle"><i class="bi <?= $iconePrio ?> me-1"></i><?= $bug['prioridade'] ?></span>
                                        </td>
                                        <td>
                                            <?php $corStatus = $bug['status'] == 'Corrigido' ? 'success' : ($bug['status'] == 'Em Correção' ? 'primary' : 'secondary'); ?>
                                            <span class="text-<?= $corStatus ?> fw-bold small"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i> <?= mb_strtoupper($bug['status']) ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if($pode_editar): ?>
                                                <a href="ver_bug.php?id=<?= $bug['id'] ?>" class="btn btn-sm btn-white text-info rounded-circle shadow-sm border border-light-subtle me-1 hover-lift" title="Visualizar Detalhes"><i class="bi bi-eye-fill"></i></a>
                                                <a href="form_bug.php?id=<?= $bug['id'] ?>" class="btn btn-sm btn-white text-primary rounded-circle shadow-sm border border-light-subtle hover-lift" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                            <?php else: ?>
                                                <i class="bi bi-lock-fill text-muted fs-5" title="Sem acesso"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 80px; height: 80px;">
                                            <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark">Nenhum bug encontrado!</h5>
                                        <p class="text-muted">A qualidade está garantida.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($kpi_total > 0): ?>
    <script>
        // Configuração Global da Fonte para os gráficos
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. GRÁFICO DE STATUS (Doughnut)
        const ctxStatus = document.getElementById('graficoStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Abertos', 'Em Correção', 'Corrigidos'],
                datasets: [{
                    data: [<?= $kpi_abertos ?>, <?= $kpi_correcao ?>, <?= $kpi_corrigidos ?>],
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Deixa o gráfico mais "fino" e elegante
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: '600' } } }
                }
            }
        });

        // 2. GRÁFICO DE PRIORIDADE (Bar)
        const ctxPrio = document.getElementById('graficoPrioridade').getContext('2d');
        new Chart(ctxPrio, {
            type: 'bar',
            data: {
                labels: ['Baixa', 'Média', 'Alta'],
                datasets: [{
                    label: 'Quantidade de Ocorrências',
                    data: [<?= $prio_baixa ?>, <?= $prio_media ?>, <?= $prio_alta ?>],
                    backgroundColor: ['#0dcaf0', '#f59e0b', '#ef4444'],
                    borderRadius: 6, // Arredonda as pontas das barras
                    barThickness: 40 // Controla a grossura das barras
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 },
                        grid: { borderDash: [5, 5], color: '#f1f5f9', drawBorder: false }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { weight: '600' } }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>