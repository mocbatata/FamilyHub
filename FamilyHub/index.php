<?php
// ============================================
// FamilyHub - Página Inicial
// ============================================

require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'index';
$page_title   = 'Início';
$page_css     = 'style.css';

// Contar tarefas por categoria
$stmt = $pdo->query("SELECT categoria, COUNT(*) as total FROM tarefa GROUP BY categoria");
$categorias = [];
while ($row = $stmt->fetch()) {
    $categorias[$row['categoria']] = $row['total'];
}

// Buscar TODAS as tarefas com data limite para o calendário
$stmt2 = $pdo->query("
    SELECT t.id, t.titulo, t.data_limite, t.categoria, t.status, t.descricao, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    WHERE t.data_limite IS NOT NULL
    ORDER BY t.data_limite ASC
");
$tarefas_raw = $stmt2->fetchAll();

// Montar JSON de eventos para o calendário JS
$eventos = [];
$map_cor = [
    'Escola'     => '#3b82f6',
    'Saude'      => '#ef4444',
    'Financeiro' => '#22c55e',
    'Social'     => '#f59e0b',
];
foreach ($tarefas_raw as $t) {
    $cor = ($t['status'] === 'Concluída') ? '#22c55e' : ($map_cor[$t['categoria']] ?? '#3b82f6');
    $eventos[] = [
        'id'         => $t['id'],
        'title'      => $t['titulo'],
        'date'       => $t['data_limite'],
        'cor'        => $cor,
        'categoria'  => $t['categoria'],
        'status'     => $t['status'],
        'responsavel'=> $t['membro_nome'] ?? '',
        'descricao'  => $t['descricao'] ?? '',
    ];
}
$eventos_json = json_encode($eventos, JSON_UNESCAPED_UNICODE);
?>
<?php include 'includes/header.php'; ?>

<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main">

        <!-- Barra de Pesquisa -->
        <div class="search-box">
            <span class="icon">🔎</span>
            <input type="text" placeholder="Pesquisar...">
            <button>Buscar</button>
        </div>

        <!-- Cards de Categorias -->
        <section class="cards">
            <div class="card blue">
                <p class="cardsp">Escolar<br><span class="card-count"><?php echo $categorias['Escola'] ?? 0; ?></span></p>
            </div>
            <div class="card red">
                <p class="cardsp">Social<br><span class="card-count"><?php echo $categorias['Social'] ?? 0; ?></span></p>
            </div>
            <div class="card yellow">
                <p class="cardsp">Financeiro<br><span class="card-count"><?php echo $categorias['Financeiro'] ?? 0; ?></span></p>
            </div>
            <div class="card green">
                <p class="cardsp">Saúde<br><span class="card-count"><?php echo $categorias['Saude'] ?? 0; ?></span></p>
            </div>
        </section>

        <!-- Calendário + Painel do Dia -->
        <section class="calendar-section">
            <h3 class="section-title-home">📅 Calendário da Família</h3>

            <div class="cal-wrapper">

                <!-- CALENDÁRIO INTERATIVO -->
                <div class="cal-box">
                    <!-- Navegação -->
                    <div class="cal-header">
                        <button class="cal-nav" id="btn-prev">&#8249;</button>
                        <span class="cal-title" id="cal-month-title"></span>
                        <button class="cal-nav" id="btn-next">&#8250;</button>
                    </div>
                    <!-- Dias da semana -->
                    <div class="cal-weekdays">
                        <span>Dom</span><span>Seg</span><span>Ter</span>
                        <span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
                    </div>
                    <!-- Grade dos dias -->
                    <div class="cal-grid" id="cal-grid"></div>
                    <!-- Legenda -->
                    <div class="cal-legend">
                        <span><i style="background:#3b82f6"></i>Escola</span>
                        <span><i style="background:#ef4444"></i>Saúde</span>
                        <span><i style="background:#22c55e"></i>Financeiro</span>
                        <span><i style="background:#f59e0b"></i>Social</span>
                    </div>
                </div>

                <!-- PAINEL DE ATIVIDADES DO DIA -->
                <div class="day-panel" id="day-panel">
                    <div class="day-panel-header">
                        <span class="day-panel-icon">📋</span>
                        <div>
                            <div class="day-panel-title" id="day-panel-title">Selecione um dia</div>
                            <div class="day-panel-sub" id="day-panel-sub">Clique em uma data no calendário</div>
                        </div>
                    </div>
                    <div class="day-panel-list" id="day-panel-list">
                        <div class="day-empty">
                            <span>📆</span>
                            <p>Clique em um dia para ver as atividades</p>
                        </div>
                    </div>
                </div>

            </div><!-- /.cal-wrapper -->
        </section>

    </main>
</div>

<script src="js/theme.js"></script>
<script>
// ============================================================
// Dados do PHP
// ============================================================
const EVENTOS = <?php echo $eventos_json; ?>;

// ============================================================
// Estado do calendário
// ============================================================
const hoje   = new Date();
let viewYear  = hoje.getFullYear();
let viewMonth = hoje.getMonth(); // 0-11
let selectedDay = null;

const MESES = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

// ============================================================
// Utilitários
// ============================================================
function toISO(y, m, d) {
    return y + '-' + String(m+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
}
function eventosNoDia(iso) {
    return EVENTOS.filter(e => e.date === iso);
}
function formatDateLabel(iso) {
    const [y,m,d] = iso.split('-');
    return `${d}/${m}/${y}`;
}
function categoriaIcon(cat) {
    const icons = { Escola:'🎓', Saude:'🏥', Financeiro:'💰', Social:'🎉' };
    return icons[cat] || '📌';
}
function statusClass(st) {
    if (st === 'Concluída')    return 'dp-concluida';
    if (st === 'Em andamento') return 'dp-andamento';
    return 'dp-pendente';
}

// ============================================================
// Renderizar calendário
// ============================================================
function renderCalendar() {
    const grid      = document.getElementById('cal-grid');
    const titleEl   = document.getElementById('cal-month-title');
    titleEl.textContent = MESES[viewMonth] + ' ' + viewYear;
    grid.innerHTML  = '';

    const firstDay  = new Date(viewYear, viewMonth, 1).getDay();
    const daysInMonth = new Date(viewYear, viewMonth+1, 0).getDate();
    const todayISO  = toISO(hoje.getFullYear(), hoje.getMonth(), hoje.getDate());

    // Células vazias antes do dia 1
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'cal-day cal-empty';
        grid.appendChild(empty);
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const iso    = toISO(viewYear, viewMonth, d);
        const evs    = eventosNoDia(iso);
        const cell   = document.createElement('div');
        cell.className = 'cal-day';
        if (iso === todayISO)    cell.classList.add('cal-today');
        if (iso === selectedDay) cell.classList.add('cal-selected');

        let dotsHtml = '';
        // Mostrar até 3 pontos de cores únicas
        const seen = new Set();
        evs.forEach(e => {
            if (!seen.has(e.cor)) {
                seen.add(e.cor);
                dotsHtml += `<span class="cal-dot" style="background:${e.cor}"></span>`;
            }
        });

        cell.innerHTML = `
            <span class="cal-day-num">${d}</span>
            <div class="cal-dots">${dotsHtml}</div>
        `;

        cell.addEventListener('click', () => selectDay(iso));
        grid.appendChild(cell);
    }
}

// ============================================================
// Selecionar dia → atualizar painel
// ============================================================
function selectDay(iso) {
    selectedDay = iso;
    renderCalendar(); // atualiza seleção visual

    const evs     = eventosNoDia(iso);
    const titleEl = document.getElementById('day-panel-title');
    const subEl   = document.getElementById('day-panel-sub');
    const listEl  = document.getElementById('day-panel-list');

    titleEl.textContent = formatDateLabel(iso);
    subEl.textContent   = evs.length === 0
        ? 'Nenhuma atividade neste dia'
        : evs.length + (evs.length === 1 ? ' atividade' : ' atividades');

    if (evs.length === 0) {
        listEl.innerHTML = `
            <div class="day-empty">
                <span>✅</span>
                <p>Dia livre! Nenhuma atividade.</p>
            </div>`;
        return;
    }

    listEl.innerHTML = evs.map(e => `
        <div class="dp-item">
            <div class="dp-color-bar" style="background:${e.cor}"></div>
            <div class="dp-content">
                <div class="dp-top">
                    <span class="dp-icon">${categoriaIcon(e.categoria)}</span>
                    <span class="dp-title">${e.title}</span>
                </div>
                ${e.descricao ? `<div class="dp-desc">${e.descricao}</div>` : ''}
                <div class="dp-meta">
                    <span class="dp-badge ${statusClass(e.status)}">${e.status}</span>
                    <span class="dp-resp">👤 ${e.responsavel || 'N/A'}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// ============================================================
// Navegação
// ============================================================
document.getElementById('btn-prev').addEventListener('click', () => {
    viewMonth--;
    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
    renderCalendar();
});
document.getElementById('btn-next').addEventListener('click', () => {
    viewMonth++;
    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
    renderCalendar();
});

// ============================================================
// Init — selecionar hoje automaticamente
// ============================================================
renderCalendar();
const todayISO = toISO(hoje.getFullYear(), hoje.getMonth(), hoje.getDate());
selectDay(todayISO);
</script>
</body>
</html>
