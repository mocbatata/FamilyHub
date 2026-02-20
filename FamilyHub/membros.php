<?php
// ============================================
// FamilyHub - Membros e cadastro
// ============================================

require_once 'includes/auth_check.php';
require_once 'config/database.php';

$current_page = 'membros';
$page_title   = 'Membros';
$page_css     = 'membros.css';

$msg = $_GET['msg'] ?? '';

// Buscar membros com estatísticas + foto
$membros = $pdo->query("
    SELECT m.*,
        (SELECT COUNT(*) FROM tarefa t WHERE t.membro_id = m.id) as total_tarefas,
        (SELECT COUNT(*) FROM tarefa t WHERE t.membro_id = m.id AND t.status = 'Concluída') as tarefas_concluidas,
        (SELECT u.email FROM usuario u WHERE u.membro_id = m.id LIMIT 1) as email
    FROM membro m
    ORDER BY m.nome
")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-membros">

            <div class="header-page">
                <h1>Membros</h1>
                <p>Gerencie os membros da sua família e seus perfis</p>
            </div>

            <div class="container">

                <?php if ($msg === 'criado'): ?>
                    <div class="alert alert-success">Membro cadastrado com sucesso!</div>
                <?php elseif ($msg === 'excluido'): ?>
                    <div class="alert alert-success">Membro removido com sucesso!</div>
                <?php elseif ($msg === 'erro'): ?>
                    <div class="alert alert-error">Erro ao processar a operação.</div>
                <?php endif; ?>

                <!-- Barra de ações -->
                <div class="actions-bar">
                    <div class="search-members">
                        <span class="icon">🔎</span>
                        <input type="text" placeholder="Buscar membro..." id="search-membro">
                    </div>
                    <?php if ($is_admin): ?>
                        <button class="btn btn-primary" id="btn-novo-membro">+ Novo Membro</button>
                    <?php endif; ?>
                </div>

                <!-- Formulário de Cadastro (apenas admin) -->
                <?php if ($is_admin): ?>
                    <div class="form-card" id="form-membro" style="display: none;">
                        <h3>Cadastrar Novo Membro</h3>
                        <form method="POST" action="api/membro_add.php" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="membro-nome">Nome Completo</label>
                                    <input type="text" id="membro-nome" name="nome" placeholder="Ex: Maria Silva" required>
                                </div>
                                <div class="form-group">
                                    <label for="membro-idade">Idade</label>
                                    <input type="number" id="membro-idade" name="idade" placeholder="Ex: 12" min="1" max="120" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="membro-email">Email (para login)</label>
                                    <input type="email" id="membro-email" name="email" placeholder="email@exemplo.com">
                                </div>
                                <div class="form-group">
                                    <label for="membro-senha">Senha (para login)</label>
                                    <input type="password" id="membro-senha" name="senha" placeholder="Mínimo 6 caracteres" minlength="6">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="membro-foto">URL da Foto</label>
                                    <input type="url" id="membro-foto" name="foto" placeholder="https://exemplo.com/foto.jpg" class="input-text">
                                    <small class="input-hint">Cole o link de uma imagem online (JPG, PNG, etc.)</small>
                                </div>
                                <div class="form-group foto-preview-wrap">
                                    <div class="foto-preview" id="foto-preview">
                                        <span class="foto-placeholder-icon">👤</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Cadastrar</button>
                                <button type="button" class="btn btn-secondary" id="btn-cancelar-membro">Cancelar</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Grid de Membros -->
                <h3 class="section-title">Membros da Família</h3>
                <div class="members-grid" id="members-grid">

                    <?php foreach ($membros as $i => $membro):
                        $is_membro_admin = in_array($membro['id'], [1, 2]);
                        $foto = $membro['foto'];
                        $tem_foto = !empty($foto) && (filter_var($foto, FILTER_VALIDATE_URL) || file_exists(__DIR__ . '/' . $foto));
                    ?>
                        <div class="member-card">
                            <!-- Avatar / Foto -->
                            <div class="member-avatar-wrap">
                                <?php if ($tem_foto): ?>
                                    <img src="<?php echo htmlspecialchars($membro['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($membro['nome']); ?>" class="member-photo">
                                <?php else: ?>
                                    <div class="member-avatar-placeholder">
                                        <?php echo strtoupper(substr($membro['nome'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="member-info">
                                <h4><?php echo htmlspecialchars($membro['nome']); ?></h4>
                                <?php if ($is_membro_admin): ?>
                                    <span class="member-role role-admin">Administrador</span>
                                <?php else: ?>
                                    <span class="member-role role-membro">Membro</span>
                                <?php endif; ?>
                                <p class="member-parentesco"><?php echo $membro['idade']; ?> anos</p>
                            </div>

                            <div class="member-stats">
                                <div class="member-stat">
                                    <span class="stat-value"><?php echo $membro['total_tarefas']; ?></span>
                                    <span class="stat-label">Atividades</span>
                                </div>
                                <div class="member-stat">
                                    <span class="stat-value"><?php echo $membro['tarefas_concluidas']; ?></span>
                                    <span class="stat-label">Concluídas</span>
                                </div>
                            </div>

                            <div class="member-contact">
                                <span><?php echo htmlspecialchars($membro['email'] ?? 'Sem email'); ?></span>
                                <span>Desde <?php echo date('d/m/Y', strtotime($membro['data_cadastro'])); ?></span>
                            </div>

                            <?php if ($is_admin): ?>
                                <div class="member-actions">
                                    <a href="api/membro_delete.php?id=<?php echo $membro['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Remover este membro?')">Remover</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </main>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/script.js"></script>
    <script>
    // Preview de foto por URL
    document.getElementById('membro-foto')?.addEventListener('input', function() {
        const preview = document.getElementById('foto-preview');
        const url = this.value.trim();
        if (url) {
            preview.innerHTML = `<img src="${url}" alt="Preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.parentElement.innerHTML='<span class=\'foto-placeholder-icon\'>⚠️</span>'">`;
        } else {
            preview.innerHTML = `<span class="foto-placeholder-icon">👤</span>`;
        }
    });

    // Busca de membro
    document.getElementById('search-membro')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.member-card').forEach(card => {
            const nome = card.querySelector('h4')?.textContent.toLowerCase() || '';
            card.style.display = nome.includes(q) ? '' : 'none';
        });
    });
    </script>
</body>
</html>
