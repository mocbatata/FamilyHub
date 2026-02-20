<?php
// ============================================
// FamilyHub - Cofrinho
// ============================================

require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'cofrinho';
$page_title   = 'Cofrinho';
$page_css     = 'cofrinho.css';

$msg = $_GET['msg'] ?? '';

// Criar tabela se não existir
$pdo->exec("
    CREATE TABLE IF NOT EXISTS cofrinho (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descricao VARCHAR(150) NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        tipo ENUM('entrada','saida') NOT NULL DEFAULT 'entrada',
        data DATE NOT NULL,
        membro_id INT,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (membro_id) REFERENCES membro(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
");

// Buscar membros para o select
$membros = $pdo->query("SELECT id, nome FROM membro ORDER BY nome")->fetchAll();

// Filtro de membro
$filtro_membro = $_GET['membro'] ?? 'todos';
$where  = [];
$params = [];
if ($filtro_membro !== 'todos' && is_numeric($filtro_membro)) {
    $where[]  = "c.membro_id = ?";
    $params[] = (int)$filtro_membro;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar movimentações
$movimentacoes = $pdo->prepare("
    SELECT c.*, m.nome as membro_nome
    FROM cofrinho c
    LEFT JOIN membro m ON c.membro_id = m.id
    $whereSQL
    ORDER BY c.data DESC, c.id DESC
");
$movimentacoes->execute($params);
$movimentacoes = $movimentacoes->fetchAll();

// Totais
$total_entrada = 0;
$total_saida   = 0;
foreach ($movimentacoes as $mov) {
    if ($mov['tipo'] === 'entrada') $total_entrada += $mov['valor'];
    else                            $total_saida   += $mov['valor'];
}
$saldo = $total_entrada - $total_saida;
?>
<?php include 'includes/header.php'; ?>

<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-cofrinho">

        <div class="header-page">
            <h1>🐷 Cofrinho da Família</h1>
            <p>Controle as economias e gastos da família</p>
        </div>

        <div class="container">

            <?php if ($msg === 'criado'): ?>
                <div class="alert alert-success">✅ Movimentação registrada com sucesso!</div>
            <?php elseif ($msg === 'excluido'): ?>
                <div class="alert alert-success">✅ Movimentação removida.</div>
            <?php elseif ($msg === 'erro'): ?>
                <div class="alert alert-error">❌ Erro ao processar. Tente novamente.</div>
            <?php endif; ?>

            <!-- Cards de resumo -->
            <div class="cofrinho-resumo">
                <div class="resumo-card resumo-saldo">
                    <div class="resumo-icon">🐷</div>
                    <div class="resumo-info">
                        <span class="resumo-label">Saldo atual</span>
                        <span class="resumo-valor <?php echo $saldo >= 0 ? 'positivo' : 'negativo'; ?>">
                            R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                <div class="resumo-card resumo-entrada">
                    <div class="resumo-icon">💚</div>
                    <div class="resumo-info">
                        <span class="resumo-label">Total entradas</span>
                        <span class="resumo-valor positivo">R$ <?php echo number_format($total_entrada, 2, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="resumo-card resumo-saida">
                    <div class="resumo-icon">❤️</div>
                    <div class="resumo-info">
                        <span class="resumo-label">Total saídas</span>
                        <span class="resumo-valor negativo">R$ <?php echo number_format($total_saida, 2, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="resumo-card resumo-movs">
                    <div class="resumo-icon">📋</div>
                    <div class="resumo-info">
                        <span class="resumo-label">Movimentações</span>
                        <span class="resumo-valor neutro"><?php echo count($movimentacoes); ?></span>
                    </div>
                </div>
            </div>

            <!-- Barra de ações -->
            <div class="actions-bar">
                <form method="GET" action="cofrinho.php" class="filters-form">
                    <div class="filter-group">
                        <label for="filter-membro">Membro</label>
                        <select id="filter-membro" name="membro" onchange="this.form.submit()">
                            <option value="todos" <?php echo $filtro_membro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <?php foreach ($membros as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo $filtro_membro == $m['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <button class="btn btn-primary" id="btn-nova-mov">+ Nova Movimentação</button>
            </div>

            <!-- Formulário nova movimentação -->
            <div class="form-card" id="form-mov" style="display:none;">
                <h3>Nova Movimentação</h3>
                <form method="POST" action="api/cofrinho_add.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mov-descricao">Descrição</label>
                            <input type="text" id="mov-descricao" name="descricao" placeholder="Ex: Mesada do Lucas" required>
                        </div>
                        <div class="form-group">
                            <label for="mov-valor">Valor (R$)</label>
                            <input type="number" id="mov-valor" name="valor" placeholder="0,00" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mov-tipo">Tipo</label>
                            <select id="mov-tipo" name="tipo" required>
                                <option value="entrada">💚 Entrada (economizei)</option>
                                <option value="saida">❤️ Saída (gastei)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mov-membro">Membro</label>
                            <select id="mov-membro" name="membro_id" required>
                                <option value="">Selecionar...</option>
                                <?php foreach ($membros as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mov-data">Data</label>
                            <input type="date" id="mov-data" name="data" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" id="btn-cancelar-mov">Cancelar</button>
                    </div>
                </form>
            </div>

            <!-- Lista de movimentações -->
            <h3 class="section-title">📋 Movimentações</h3>

            <?php if (empty($movimentacoes)): ?>
                <div class="cofrinho-vazio">
                    <span class="vazio-icon">🐷</span>
                    <p>Nenhuma movimentação ainda.</p>
                    <p>Clique em <strong>+ Nova Movimentação</strong> para começar!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Membro</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <?php if ($is_admin): ?><th>Ações</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $mov): ?>
                                <tr>
                                    <td>
                                        <?php if ($mov['tipo'] === 'entrada'): ?>
                                            <span class="tipo-badge tipo-entrada">💚 Entrada</span>
                                        <?php else: ?>
                                            <span class="tipo-badge tipo-saida">❤️ Saída</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($mov['descricao']); ?></td>
                                    <td><?php echo htmlspecialchars($mov['membro_nome'] ?? '—'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($mov['data'])); ?></td>
                                    <td class="valor-col <?php echo $mov['tipo'] === 'entrada' ? 'positivo' : 'negativo'; ?>">
                                        <?php echo $mov['tipo'] === 'entrada' ? '+' : '-'; ?>
                                        R$ <?php echo number_format($mov['valor'], 2, ',', '.'); ?>
                                    </td>
                                    <?php if ($is_admin): ?>
                                        <td>
                                            <a href="api/cofrinho_delete.php?id=<?php echo $mov['id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Remover esta movimentação?')">Remover</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script src="js/theme.js"></script>
<script src="js/script.js"></script>
<script>
document.getElementById('btn-nova-mov')?.addEventListener('click', () => {
    const form = document.getElementById('form-mov');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') form.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
document.getElementById('btn-cancelar-mov')?.addEventListener('click', () => {
    document.getElementById('form-mov').style.display = 'none';
});
</script>
</body>
</html>
