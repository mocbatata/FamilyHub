<!-- NavBar Lateral -->
<nav class="sidebar">
    <h2 class="logo">FamilyHub</h2>

    <!-- Toggle de Tema -->
    <div class="theme-toggle-sidebar">
        <label for="theme-toggle-page" class="toggle-label-text">Tema</label>
        <div class="toggle-wrapper">
            <input type="checkbox" id="theme-toggle-page">
            <label for="theme-toggle-page" class="toggle-label"></label>
        </div>
    </div>

    <ul>
        <li><a href="index.php"         <?php echo ($current_page ?? '') === 'index'         ? 'class="active"' : ''; ?>>🏠 Início</a></li>
        <li><a href="dashboard.php"     <?php echo ($current_page ?? '') === 'dashboard'     ? 'class="active"' : ''; ?>>📊 Dashboard</a></li>
        <li><a href="atividades.php"    <?php echo ($current_page ?? '') === 'atividades'    ? 'class="active"' : ''; ?>>📋 Gestão de atividades</a></li>
        <li><a href="membros.php"       <?php echo ($current_page ?? '') === 'membros'       ? 'class="active"' : ''; ?>>👨‍👩‍👧‍👦 Membros da família</a></li>
        <li><a href="cofrinho.php"       <?php echo ($current_page ?? '') === 'cofrinho'       ? 'class="active"' : ''; ?>>🐷 Cofrinho</a></li>
        <li><a href="notificacoes.php"  <?php echo ($current_page ?? '') === 'notificacoes'  ? 'class="active"' : ''; ?>>🔔 Compromissos</a></li>
        <li><a href="perfil.php"        <?php echo ($current_page ?? '') === 'perfil'        ? 'class="active"' : ''; ?>>⚙️ Meu Perfil</a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <?php
            $foto_path   = $membro_foto ?? null;
            $foto_existe = $foto_path && (filter_var($foto_path, FILTER_VALIDATE_URL) || file_exists(__DIR__ . '/../' . ltrim($foto_path, '/')));
            ?>
            <?php if ($foto_existe): ?>
                <img src="<?php echo htmlspecialchars($foto_path); ?>"
                     alt="Foto de <?php echo htmlspecialchars($membro_nome); ?>"
                     class="sidebar-user-photo">
            <?php else: ?>
                <span class="user-icon">👤</span>
            <?php endif; ?>
            <div class="user-text">
                <span class="user-name"><?php echo htmlspecialchars($membro_nome); ?></span>
                <?php if ($is_admin): ?>
                    <span class="admin-badge">Admin</span>
                <?php endif; ?>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">🚪 Sair</a>
    </div>
</nav>
