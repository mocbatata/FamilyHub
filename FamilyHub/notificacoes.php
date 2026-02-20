<?php
// ============================================
// FamilyHub - Notificações
// ============================================

require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'notificacoes';
$page_title = 'Notificações';
$page_css = 'notificacoes.css';

// Próximos compromissos (tarefas pendentes/em andamento com data futura, ordenadas por data)
$compromissos = $pdo->query("
    SELECT t.*, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    WHERE t.status != 'Concluída' AND t.data_limite >= CURDATE()
    ORDER BY t.data_limite ASC
    LIMIT 6
")->fetchAll();

// Tarefas vencidas (data passou e ainda não concluída)
$vencidas = $pdo->query("
    SELECT t.*, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    WHERE t.status != 'Concluída' AND t.data_limite < CURDATE()
    ORDER BY t.data_limite DESC
")->fetchAll();

// Tarefas concluídas recentemente
$concluidas = $pdo->query("
    SELECT t.*, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    WHERE t.status = 'Concluída'
    ORDER BY t.data_criacao DESC
    LIMIT 5
")->fetchAll();

// Função para calcular urgência
function calcularUrgencia($data_limite)
{
    $hoje = new DateTime();
    $limite = new DateTime($data_limite);
    $diff = $hoje->diff($limite)->days;
    if ($diff <= 2)
        return 'urgente';
    if ($diff <= 7)
        return 'normal';
    return 'baixa';
}

// Ícones por categoria
function iconCategoria($categoria)
{
    switch ($categoria) {
        case 'Escola':
            return '🎓 Escolar';
        case 'Saude':
            return '🏥 Saúde';
        case 'Financeiro':
            return '💰 Financeiro';
        case 'Social':
            return '🎉 Social';
        default:
            return '📌 ' . $categoria;
    }
}
?>
<?php include 'includes/header.php'; ?>

<body>

    <div class="layout">

        <?php include 'includes/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="main-notificacoes">

            <div class="header-page">
                <h1>🔔 Notificação de Compromissos</h1>
                <p>Fique por dentro de todos os compromissos e lembretes da família</p>
            </div>

            <div class="container">

                <!-- Próximos Compromissos -->
                <h3 class="section-title">📅 Próximos Compromissos</h3>
                <div class="compromissos-grid">
                    <?php if (empty($compromissos)): ?>
                        <div class="compromisso-card normal">
                            <p style="text-align:center; padding:20px; color:#999;">Nenhum compromisso futuro encontrado.</p>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($compromissos as $comp):
        $urgencia = calcularUrgencia($comp['data_limite']);
?>
                            <div class="compromisso-card <?php echo $urgencia; ?>">
                                <div class="compromisso-header">
                                    <span class="compromisso-tipo"><?php echo iconCategoria($comp['categoria']); ?></span>
                                    <span class="urgencia-badge urgencia-<?php echo $urgencia; ?>">
                                        <?php echo ucfirst($urgencia); ?>
                                    </span>
                                </div>
                                <h4><?php echo htmlspecialchars($comp['titulo']); ?></h4>
                                <p class="compromisso-desc"><?php echo htmlspecialchars($comp['descricao'] ?? ''); ?></p>
                                <div class="compromisso-meta">
                                    <span class="compromisso-data">📅 <?php echo date('d/m/Y', strtotime($comp['data_limite'])); ?></span>
                                    <span class="compromisso-hora">📌 <?php echo htmlspecialchars($comp['status']); ?></span>
                                </div>
                                <div class="compromisso-responsavel">👤 <?php echo htmlspecialchars($comp['membro_nome'] ?? 'N/A'); ?></div>
                            </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>

                <!-- Notificações: Tarefas Vencidas -->
                <?php if (!empty($vencidas)): ?>
                    <h3 class="section-title">⚠️ Tarefas Vencidas</h3>
                    <div class="notificacoes-list">
                        <?php foreach ($vencidas as $v): ?>
                            <div class="notificacao-item notificacao-nova">
                                <div class="notificacao-icon">🔴</div>
                                <div class="notificacao-content">
                                    <h4>Tarefa vencida: <?php echo htmlspecialchars($v['titulo']); ?></h4>
                                    <p><?php echo htmlspecialchars($v['membro_nome'] ?? 'N/A'); ?> — Data limite era <?php echo date('d/m/Y', strtotime($v['data_limite'])); ?></p>
                                    <span class="notificacao-tempo"><?php echo htmlspecialchars($v['categoria']); ?></span>
                                </div>
                            </div>
                        <?php
    endforeach; ?>
                    </div>
                <?php
endif; ?>

                <!-- Notificações: Concluídas Recentemente -->
                <h3 class="section-title">🔔 Atividades Concluídas Recentemente</h3>
                <div class="notificacoes-list">
                    <?php if (empty($concluidas)): ?>
                        <div class="notificacao-item">
                            <div class="notificacao-icon">📭</div>
                            <div class="notificacao-content">
                                <h4>Nenhuma atividade concluída ainda</h4>
                                <p>As atividades concluídas aparecerão aqui</p>
                            </div>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($concluidas as $c): ?>
                            <div class="notificacao-item">
                                <div class="notificacao-icon">🟢</div>
                                <div class="notificacao-content">
                                    <h4>Atividade concluída: <?php echo htmlspecialchars($c['titulo']); ?></h4>
                                    <p><?php echo htmlspecialchars($c['membro_nome'] ?? 'N/A'); ?> completou a atividade</p>
                                    <span class="notificacao-tempo"><?php echo htmlspecialchars($c['categoria']); ?></span>
                                </div>
                            </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>

            </div>
        </main>

    </div>

    <script src="js/theme.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
