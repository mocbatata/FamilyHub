<?php
// ============================================
// FamilyHub - Gestão de Atividades
// ============================================

require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'atividades';
$page_title   = 'Gestão de Atividades';
$page_css     = 'atividades.css';

$msg = $_GET['msg'] ?? '';

// Filtros
$filtro_categoria = $_GET['categoria'] ?? 'todas';
$filtro_status    = $_GET['status']    ?? 'todos';

$where  = [];
$params = [];

if ($filtro_categoria !== 'todas') {
    $where[]  = "t.categoria = ?";
    $params[] = $filtro_categoria;
}
if ($filtro_status !== 'todos') {
    $status_map = [
        'pendente'     => 'Pendente',
        'em-andamento' => 'Em andamento',
        'concluida'    => 'Concluída'
    ];
    if (isset($status_map[$filtro_status])) {
        $where[]  = "t.status = ?";
        $params[] = $status_map[$filtro_status];
    }
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT t.*, m.nome as membro_nome
    FROM tarefa t
    LEFT JOIN membro m ON t.membro_id = m.id
    {$where_sql}
    ORDER BY t.data_criacao DESC
");
$stmt->execute($params);
$tarefas = $stmt->fetchAll();

// Estatísticas
$total    = $pdo->query("SELECT COUNT(*) FROM tarefa")->fetchColumn();
$concluidas = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Concluída'")->fetchColumn();
$pendentes  = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Pendente'")->fetchColumn();
$andamento  = $pdo->query("SELECT COUNT(*) FROM tarefa WHERE status = 'Em andamento'")->fetchColumn();

// Membros para o formulário
$membros = $pdo->query("SELECT id, nome FROM membro ORDER BY nome")->fetchAll();

// Permissões:
// Admin: adicionar + editar + excluir
// Membro: APENAS adicionar
$pode_adicionar = true;           // todos podem
$pode_editar    = $is_admin;      // só admin
$pode_excluir   = $is_admin;      // só admin
?>
<?php include 'includes/header.php'; ?>

<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-atividades">

            <div class="header-page">
                <h1>📋 Gestão de Atividades</h1>
                <p>Crie, edite e acompanhe todas as atividades da família</p>
            </div>

            <div class="container">

                <?php if ($msg === 'criada'): ?>
                    <div class="alert alert-success">✅ Tarefa criada com sucesso!</div>
                <?php elseif ($msg === 'editada'): ?>
                    <div class="alert alert-success">✅ Tarefa atualizada com sucesso!</div>
                <?php elseif ($msg === 'excluida'): ?>
                    <div class="alert alert-success">✅ Tarefa excluída com sucesso!</div>
                <?php elseif ($msg === 'sem-permissao'): ?>
                    <div class="alert alert-error">⚠️ Você não tem permissão para esta ação.</div>
                <?php elseif ($msg === 'erro'): ?>
                    <div class="alert alert-error">⚠️ Erro ao processar a operação.</div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="filters-bar">
                    <form method="GET" action="atividades.php" class="filters-form">
                        <div class="filter-group">
                            <label for="filter-categoria">Categoria</label>
                            <select id="filter-categoria" name="categoria" onchange="this.form.submit()">
                                <option value="todas"      <?php echo $filtro_categoria === 'todas'      ? 'selected' : ''; ?>>Todas</option>
                                <option value="Escola"     <?php echo $filtro_categoria === 'Escola'     ? 'selected' : ''; ?>>Escolar</option>
                                <option value="Social"     <?php echo $filtro_categoria === 'Social'     ? 'selected' : ''; ?>>Social</option>
                                <option value="Financeiro" <?php echo $filtro_categoria === 'Financeiro' ? 'selected' : ''; ?>>Financeiro</option>
                                <option value="Saude"      <?php echo $filtro_categoria === 'Saude'      ? 'selected' : ''; ?>>Saúde</option>
                                <option value="Domestico"  <?php echo $filtro_categoria === 'Domestico'  ? 'selected' : ''; ?>>Doméstico</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filter-status">Status</label>
                            <select id="filter-status" name="status" onchange="this.form.submit()">
                                <option value="todos"        <?php echo $filtro_status === 'todos'        ? 'selected' : ''; ?>>Todos</option>
                                <option value="pendente"     <?php echo $filtro_status === 'pendente'     ? 'selected' : ''; ?>>Pendente</option>
                                <option value="em-andamento" <?php echo $filtro_status === 'em-andamento' ? 'selected' : ''; ?>>Em andamento</option>
                                <option value="concluida"    <?php echo $filtro_status === 'concluida'    ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>
                    </form>
                    <!-- Botão Nova Atividade: todos podem ver e clicar -->
                    <button class="btn btn-primary" id="btn-nova-atividade">+ Nova Atividade</button>
                </div>

                <!-- Cards resumo -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo $total; ?></span>
                            <span class="stat-label">Total</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo $concluidas; ?></span>
                            <span class="stat-label">Concluídas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo $pendentes; ?></span>
                            <span class="stat-label">Pendentes</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🔄</div>
                        <div class="stat-info">
                            <span class="stat-number"><?php echo $andamento; ?></span>
                            <span class="stat-label">Em andamento</span>
                        </div>
                    </div>
                </div>

                <!-- Formulário Nova Atividade (todos podem adicionar) -->
                <div class="form-card" id="form-atividade" style="display: none;">
                    <h3>Nova Atividade</h3>
                    <form method="POST" action="api/tarefa_add.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="atividade-titulo">Título da Atividade</label>
                                <input type="text" id="atividade-titulo" name="titulo" placeholder="Ex: Fazer compras do mês" required>
                            </div>
                            <div class="form-group">
                                <label for="atividade-membro">Responsável</label>
                                <select id="atividade-membro" name="membro_id" required>
                                    <option value="">Selecionar...</option>
                                    <?php foreach ($membros as $m): ?>
                                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="atividade-categoria">Categoria</label>
                                <select id="atividade-categoria" name="categoria" required>
                                    <option value="Escola">Escola</option>
                                    <option value="Saude">Saúde</option>
                                    <option value="Financeiro">Financeiro</option>
                                    <option value="Social">Social</option>
                                    <option value="Domestico">Doméstico</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="atividade-status">Status</label>
                                <select id="atividade-status" name="status">
                                    <option value="Pendente">Pendente</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Concluída">Concluída</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="atividade-data">Data Limite</label>
                                <input type="date" id="atividade-data" name="data_limite" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="atividade-descricao">Descrição</label>
                            <textarea id="atividade-descricao" name="descricao" rows="3" placeholder="Descreva a atividade..."></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Salvar Atividade</button>
                            <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
                        </div>
                    </form>
                </div>

                <!-- Formulário de Edição (só admin) -->
                <?php if ($pode_editar): ?>
                    <div class="form-card" id="form-editar-atividade" style="display: none;">
                        <h3>Editar Atividade</h3>
                        <form method="POST" action="api/tarefa_edit.php">
                            <input type="hidden" id="edit-id" name="id">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-titulo">Título</label>
                                    <input type="text" id="edit-titulo" name="titulo" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-membro">Responsável</label>
                                    <select id="edit-membro" name="membro_id" required>
                                        <?php foreach ($membros as $m): ?>
                                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nome']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-categoria">Categoria</label>
                                    <select id="edit-categoria" name="categoria">
                                        <option value="Escola">Escola</option>
                                        <option value="Saude">Saúde</option>
                                        <option value="Financeiro">Financeiro</option>
                                        <option value="Social">Social</option>
                                        <option value="Domestico">Doméstico</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-status">Status</label>
                                    <select id="edit-status" name="status">
                                        <option value="Pendente">Pendente</option>
                                        <option value="Em andamento">Em andamento</option>
                                        <option value="Concluída">Concluída</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-data">Data Limite</label>
                                    <input type="date" id="edit-data" name="data_limite">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-descricao">Descrição</label>
                                <textarea id="edit-descricao" name="descricao" rows="3"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Atualizar</button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('form-editar-atividade').style.display='none'">Cancelar</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Tabela de Atividades -->
                <h3 class="section-title">📝 Lista de Atividades</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Atividade</th>
                                <th>Responsável</th>
                                <th>Categoria</th>
                                <th>Data Limite</th>
                                <th>Status</th>
                                <?php if ($pode_editar || $pode_excluir): ?>
                                    <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tarefas)): ?>
                                <tr>
                                    <td colspan="<?php echo ($pode_editar || $pode_excluir) ? 6 : 5; ?>" style="text-align:center;padding:30px;color:#999;">
                                        Nenhuma atividade encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tarefas as $tarefa): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tarefa['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($tarefa['membro_nome'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                                $cat_class = match($tarefa['categoria']) {
                                                    'Escola'     => 'badge-escolar',
                                                    'Saude'      => 'badge-saude',
                                                    'Financeiro' => 'badge-financeiro',
                                                    'Social'     => 'badge-social',
                                                    'Domestico'  => 'badge-domestica',
                                                    default      => ''
                                                };
                                            ?>
                                            <span class="badge <?php echo $cat_class; ?>"><?php echo htmlspecialchars($tarefa['categoria']); ?></span>
                                        </td>
                                        <td><?php echo $tarefa['data_limite'] ? date('d/m/Y', strtotime($tarefa['data_limite'])) : '—'; ?></td>
                                        <td>
                                            <?php
                                                $st_class = match($tarefa['status']) {
                                                    'Concluída'    => 'badge-concluida',
                                                    'Em andamento' => 'badge-em-andamento',
                                                    default        => 'badge-pendente'
                                                };
                                                $st_icon = match($tarefa['status']) {
                                                    'Concluída'    => '✅',
                                                    'Em andamento' => '🔄',
                                                    default        => '⏳'
                                                };
                                            ?>
                                            <span class="badge <?php echo $st_class; ?>"><?php echo $st_icon . ' ' . htmlspecialchars($tarefa['status']); ?></span>
                                        </td>
                                        <?php if ($pode_editar || $pode_excluir): ?>
                                            <td>
                                                <?php if ($pode_editar): ?>
                                                    <button class="btn btn-secondary btn-sm" onclick="editarTarefa(<?php echo htmlspecialchars(json_encode($tarefa)); ?>)">Editar</button>
                                                <?php endif; ?>
                                                <?php if ($pode_excluir): ?>
                                                    <a href="api/tarefa_delete.php?id=<?php echo $tarefa['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir esta tarefa?')">Excluir</a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/script.js"></script>
    <script>
        function editarTarefa(tarefa) {
            document.getElementById('form-editar-atividade').style.display = 'block';
            document.getElementById('form-atividade').style.display = 'none';
            document.getElementById('edit-id').value       = tarefa.id;
            document.getElementById('edit-titulo').value   = tarefa.titulo;
            document.getElementById('edit-membro').value   = tarefa.membro_id;
            document.getElementById('edit-categoria').value= tarefa.categoria;
            document.getElementById('edit-status').value   = tarefa.status;
            document.getElementById('edit-data').value     = tarefa.data_limite;
            document.getElementById('edit-descricao').value= tarefa.descricao || '';
            document.getElementById('form-editar-atividade').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
