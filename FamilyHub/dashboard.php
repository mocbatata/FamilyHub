<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'dashboard';
$page_title   = 'Dashboard';
$page_css     = 'dashboard.css';

// Estatísticas gerais
$total_membros      = $pdo->query("SELECT COUNT(*) FROM membro")->fetchColumn();
$tarefas_concluidas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Concluída'")->fetchColumn();
$tarefas_pendentes  = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Pendente'")->fetchColumn();
$tarefas_andamento  = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Em andamento'")->fetchColumn();
$total_tarefas      = $pdo->query("SELECT COUNT(*) FROM tarefa")->fetchColumn();

// Membros com estatísticas
$membros = $pdo->query("
    SELECT m.*,
        (SELECT COUNT(*) FROM tarefa t WHERE t.membro_id = m.id) as total_tarefas,
        (SELECT COUNT(*) FROM tarefa t WHERE t.membro_id = m.id AND t.status = 'Concluída') as tarefas_concluidas
    FROM membro m
    ORDER BY m.nome
")->fetchAll();

// Tarefas recentes
$tarefas_recentes = $pdo->query("
    SELECT t.*, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    ORDER BY t.data_criacao DESC
    LIMIT 5
")->fetchAll();

// ── Dados para os gráficos ──────────────────────────────────

// Gráfico 1: Distribuição de tarefas por membro
$dados_membros = $pdo->query("
    SELECT m.nome, COUNT(t.id) as total
    FROM membro m
    LEFT JOIN tarefa t ON t.membro_id = m.id
    GROUP BY m.id, m.nome
    ORDER BY total DESC
")->fetchAll();

// Gráfico 2 (donut): Status das tarefas
$dados_status = [
    ['label' => 'Concluídas',    'value' => (int)$tarefas_concluidas, 'color' => '#22c55e'],
    ['label' => 'Em andamento',  'value' => (int)$tarefas_andamento,  'color' => '#3b82f6'],
    ['label' => 'Pendentes',     'value' => (int)$tarefas_pendentes,  'color' => '#f59e0b'],
];

// Gráfico 3 (barras horizontais): Tarefas por categoria
$dados_categorias = $pdo->query("
    SELECT categoria, COUNT(*) as total FROM tarefa GROUP BY categoria ORDER BY total DESC
")->fetchAll();

// Cores para membros
$cores_membros = ['#00f7ff','#a78bfa','#34d399','#fb923c','#f472b6','#60a5fa'];

$membros_labels = json_encode(array_column($dados_membros, 'nome'),    JSON_UNESCAPED_UNICODE);
$membros_valores= json_encode(array_map(fn($r)=>(int)$r['total'], $dados_membros));
$membros_cores  = json_encode(array_slice($cores_membros, 0, count($dados_membros)));

$status_labels  = json_encode(array_column($dados_status, 'label'),  JSON_UNESCAPED_UNICODE);
$status_valores = json_encode(array_column($dados_status, 'value'));
$status_cores   = json_encode(array_column($dados_status, 'color'));

$cat_labels  = json_encode(array_column($dados_categorias,'categoria'), JSON_UNESCAPED_UNICODE);
$cat_valores = json_encode(array_map(fn($r)=>(int)$r['total'], $dados_categorias));
$cat_max     = max(array_column($dados_categorias,'total') ?: [1]);

$perc_conclusao = $total_tarefas > 0 ? round(($tarefas_concluidas / $total_tarefas) * 100) : 0;
?>
<?php include 'includes/header.php'; ?>

<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-dashboard">
        <div class="header-dashboard">
            <h1>Dashboard da Família</h1>
            <p>Acompanhe atividades, membros e desempenho em tempo real</p>
        </div>

        <div class="container">

            <!-- CARDS DE RESUMO -->
            <div class="dashboard-grid">
                <div class="card">
                    <h2><span class="icon">👥</span> Membros</h2>
                    <p>Cadastrados</p>
                    <div class="card-number"><?= $total_membros ?></div>
                    <div class="stats"><span class="stat-badge">Ativos: <?= $total_membros ?></span></div>
                </div>
                <div class="card">
                    <h2><span class="icon">✅</span> Concluídas</h2>
                    <p>Tarefas finalizadas</p>
                    <div class="card-number"><?= $tarefas_concluidas ?></div>
                    <div class="stats"><span class="stat-badge"><?= $perc_conclusao ?>% do total</span></div>
                </div>
                <div class="card">
                    <h2><span class="icon">⏳</span> Pendentes</h2>
                    <p>Aguardando realização</p>
                    <div class="card-number"><?= $tarefas_pendentes ?></div>
                    <div class="stats"><span class="stat-badge"><?= $tarefas_andamento ?> em andamento</span></div>
                </div>
                <div class="card">
                    <h2><span class="icon">📋</span> Total</h2>
                    <p>Todas as tarefas</p>
                    <div class="card-number"><?= $total_tarefas ?></div>
                    <div class="stats"><span class="stat-badge">4 categorias</span></div>
                </div>
            </div>

            <!-- ════ SEÇÃO DE GRÁFICOS ════ -->
            <h3 class="section-title">Estatísticas Visuais</h3>

            <div class="charts-grid">

                <!-- GRÁFICO 1: Pizza — Tarefas por membro -->
                <div class="chart-card chart-card--pizza">
                    <div class="chart-card__header">
                        <div>
                            <div class="chart-card__title">Carga por Membro</div>
                            <div class="chart-card__sub">Quem está com mais atividades</div>
                        </div>
                        <span class="chart-card__icon">🍕</span>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="chartMembros"></canvas>
                    </div>
                    <div class="chart-legend" id="legend-membros"></div>
                </div>

                <!-- GRÁFICO 2: Donut — Status das tarefas -->
                <div class="chart-card chart-card--donut">
                    <div class="chart-card__header">
                        <div>
                            <div class="chart-card__title">Status das Tarefas</div>
                            <div class="chart-card__sub">Visão geral do progresso</div>
                        </div>
                        <span class="chart-card__icon">📊</span>
                    </div>
                    <div class="chart-wrap chart-wrap--donut">
                        <canvas id="chartStatus"></canvas>
                        <div class="donut-center">
                            <span class="donut-pct"><?= $perc_conclusao ?>%</span>
                            <span class="donut-lbl">concluído</span>
                        </div>
                    </div>
                    <div class="chart-legend" id="legend-status"></div>
                </div>

                <!-- GRÁFICO 3: Barras horizontais — Por categoria -->
                <div class="chart-card chart-card--bars">
                    <div class="chart-card__header">
                        <div>
                            <div class="chart-card__title">Tarefas por Categoria</div>
                            <div class="chart-card__sub">Onde a família mais se envolve</div>
                        </div>
                        <span class="chart-card__icon">📂</span>
                    </div>
                    <div class="bars-wrap" id="bars-categorias">
                        <?php
                        $cat_cores = ['Escola'=>'#3b82f6','Saude'=>'#ef4444','Financeiro'=>'#22c55e','Social'=>'#f59e0b'];
                        $cat_icons = ['Escola'=>'🎓','Saude'=>'🏥','Financeiro'=>'💰','Social'=>'🎉'];
                        foreach ($dados_categorias as $c):
                            $pct = $cat_max > 0 ? round($c['total'] / $cat_max * 100) : 0;
                            $cor = $cat_cores[$c['categoria']] ?? '#00f7ff';
                            $ico = $cat_icons[$c['categoria']] ?? '📌';
                        ?>
                        <div class="hbar-row">
                            <div class="hbar-label">
                                <span><?= $ico ?></span>
                                <span><?= htmlspecialchars($c['categoria']) ?></span>
                            </div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="width:<?= $pct ?>%;background:<?= $cor ?>;"
                                     data-pct="<?= $pct ?>"></div>
                            </div>
                            <span class="hbar-value"><?= $c['total'] ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($dados_categorias)): ?>
                            <p style="color:#999;text-align:center;padding:20px">Sem tarefas ainda.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.charts-grid -->

            <!-- TABELA DE MEMBROS -->
            <h3 class="section-title">Membros da Família</h3>
            <div class="table-responsive">
                <table>
                    <thead><tr>
                        <th>Nome</th><th>Idade</th>
                        <th>Atividades</th><th>Concluídas</th>
                        <th>Cadastro</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($membros as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['nome']) ?></td>
                            <td><?= $m['idade'] ?> anos</td>
                            <td><?= $m['total_tarefas'] ?></td>
                            <td><?= $m['tarefas_concluidas'] ?></td>
                            <td><?= date('d/m/Y', strtotime($m['data_cadastro'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ATIVIDADES RECENTES -->
            <h3 class="section-title">Atividades Recentes</h3>
            <div class="table-responsive">
                <table>
                    <thead><tr>
                        <th>Atividade</th><th>Responsável</th>
                        <th>Categoria</th><th>Status</th><th>Data Limite</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($tarefas_recentes as $t):
                        $st_class = match($t['status']) {
                            'Concluída'    => 'badge-concluida',
                            'Em andamento' => 'badge-em-andamento',
                            default        => 'badge-pendente'
                        };
                        $st_icon = match($t['status']) {
                            'Concluída'    => '✅',
                            'Em andamento' => '🔄',
                            default        => '⏳'
                        };
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($t['titulo']) ?></td>
                            <td><?= htmlspecialchars($t['membro_nome'] ?? 'N/A') ?></td>
                            <td><span class="stat-badge"><?= htmlspecialchars($t['categoria']) ?></span></td>
                            <td><span class="badge <?= $st_class ?>"><?= $st_icon ?> <?= htmlspecialchars($t['status']) ?></span></td>
                            <td><?= $t['data_limite'] ? date('d/m/Y', strtotime($t['data_limite'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<!-- Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="js/theme.js"></script>
<script>
// ── Dados do PHP ──
const mLabels  = <?= $membros_labels ?>;
const mValues  = <?= $membros_valores ?>;
const mCores   = <?= $membros_cores ?>;
const sLabels  = <?= $status_labels ?>;
const sValues  = <?= $status_valores ?>;
const sCores   = <?= $status_cores ?>;

const isDark = () => document.body.classList.contains('dark-theme');

// Defaults globais
const fontColor = () => isDark() ? '#c0c8d8' : '#4a5568';
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

// ── helpers ──
function buildLegend(containerId, labels, cores, values) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = labels.map((l, i) => `
        <div class="legend-item">
            <span class="legend-dot" style="background:${cores[i]}"></span>
            <span class="legend-text">${l}</span>
            <span class="legend-val">${values[i]}</span>
        </div>
    `).join('');
}

// ── Gráfico 1: Pizza — Carga por membro ──
const ctx1 = document.getElementById('chartMembros').getContext('2d');
const chartMembros = new Chart(ctx1, {
    type: 'pie',
    data: {
        labels: mLabels,
        datasets: [{
            data: mValues,
            backgroundColor: mCores,
            borderWidth: 3,
            borderColor: isDark() ? '#1f1f2e' : '#ffffff',
            hoverOffset: 10,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed} tarefa${ctx.parsed !== 1 ? 's' : ''}`
                }
            }
        },
        animation: { animateScale: true, duration: 800 }
    }
});
buildLegend('legend-membros', mLabels, mCores, mValues);

// ── Gráfico 2: Donut — Status ──
const ctx2 = document.getElementById('chartStatus').getContext('2d');
const chartStatus = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: sLabels,
        datasets: [{
            data: sValues,
            backgroundColor: sCores,
            borderWidth: 3,
            borderColor: isDark() ? '#1f1f2e' : '#ffffff',
            hoverOffset: 10,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                }
            }
        },
        animation: { animateScale: true, duration: 800 }
    }
});
buildLegend('legend-status', sLabels, sCores, sValues);

// ── Barras horizontais: animar largura ──
document.querySelectorAll('.hbar-fill').forEach(bar => {
    const target = bar.dataset.pct + '%';
    bar.style.width = '0%';
    setTimeout(() => {
        bar.style.transition = 'width 0.9s cubic-bezier(.4,0,.2,1)';
        bar.style.width = target;
    }, 200);
});

// ── Atualiza bordas dos charts ao mudar tema ──
function updateChartTheme() {
    const border = isDark() ? '#1f1f2e' : '#ffffff';
    [chartMembros, chartStatus].forEach(ch => {
        ch.data.datasets[0].borderColor = border;
        ch.update();
    });
}
// Observa mudança de tema via toggle
document.getElementById('theme-toggle-page')?.addEventListener('change', () => {
    setTimeout(updateChartTheme, 50);
});
</script>
</body>
</html>
