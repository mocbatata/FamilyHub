<?php
// ============================================
// FamilyHub - Meu Perfil
// ============================================

require_once 'includes/auth_check.php';

$current_page = 'perfil';
$page_title   = 'Meu Perfil';
$page_css     = 'perfil.css';

$msg   = $_GET['msg']   ?? '';
$erro  = $_GET['erro']  ?? '';

?>
<?php include 'includes/header.php'; ?>

<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-perfil">

        <div class="header-page">
            <h1>⚙️ Meu Perfil</h1>
            <p>Gerencie suas informações pessoais e de acesso</p>
        </div>

        <div class="container">

            <?php if ($msg === 'ok'): ?>
                <div class="alert alert-success">✅ Perfil atualizado com sucesso!</div>
            <?php elseif ($erro === 'senha'): ?>
                <div class="alert alert-error">❌ As senhas não coincidem. Tente novamente.</div>
            <?php elseif ($erro === 'senha_curta'): ?>
                <div class="alert alert-error">❌ A senha deve ter pelo menos 6 caracteres.</div>
            <?php elseif ($erro === 'email_uso'): ?>
                <div class="alert alert-error">❌ Este email já está sendo usado por outro usuário.</div>
            <?php elseif ($erro === 'foto'): ?>
                <div class="alert alert-error">❌ Erro ao salvar a foto. Verifique o formato e tamanho (máx. 2MB).</div>
            <?php elseif ($erro === 'geral'): ?>
                <div class="alert alert-error">❌ Erro ao salvar. Tente novamente.</div>
            <?php endif; ?>

            <form method="POST" action="api/perfil_update.php" class="perfil-form">

                <!-- Foto de Perfil -->
                <div class="perfil-card">
                    <h3 class="perfil-section-title">📷 Foto de Perfil</h3>
                    <div class="foto-area">
                        <div class="foto-atual" id="foto-atual">
                            <?php
                            $tem_foto = $membro_foto && (filter_var($membro_foto, FILTER_VALIDATE_URL) || file_exists(__DIR__ . '/' . ltrim($membro_foto, '/')));
                            ?>
                            <?php if ($tem_foto): ?>
                                <img src="<?php echo htmlspecialchars($membro_foto); ?>" alt="Foto atual" id="foto-preview-img">
                            <?php else: ?>
                                <div class="foto-placeholder" id="foto-placeholder">
                                    <?php echo strtoupper(substr($membro_nome, 0, 1)); ?>
                                </div>
                                <img src="" alt="Preview" id="foto-preview-img" style="display:none;">
                            <?php endif; ?>
                        </div>
                        <div class="foto-upload-area">
                            <label for="foto_url">🔗 URL da foto de perfil</label>
                            <input type="url" id="foto_url" name="foto_url"
                                   value="<?php echo htmlspecialchars($membro_foto ?? ''); ?>"
                                   placeholder="https://exemplo.com/minha-foto.jpg"
                                   style="width:100%; margin-top:6px;">
                            <small class="input-hint">Cole o link de uma imagem online (JPG, PNG, etc.)</small>
                        </div>
                    </div>
                </div>

                <!-- Dados pessoais -->
                <div class="perfil-card">
                    <h3 class="perfil-section-title">👤 Dados de Acesso</h3>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($membro_email ?? ''); ?>"
                               placeholder="seu@email.com">
                    </div>
                </div>

                <!-- Alterar senha -->
                <div class="perfil-card">
                    <h3 class="perfil-section-title">🔒 Alterar Senha</h3>
                    <p class="perfil-hint">Deixe em branco para não alterar a senha.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nova_senha">Nova senha</label>
                            <input type="password" id="nova_senha" name="nova_senha"
                                   placeholder="Mínimo 6 caracteres" minlength="6" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar nova senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha"
                                   placeholder="Repita a senha" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="senha-match" id="senha-match"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btn-salvar">💾 Salvar alterações</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script src="js/theme.js"></script>
<script src="js/script.js"></script>
<script>
// Preview de foto por URL
document.getElementById('foto_url')?.addEventListener('input', function () {
    const url = this.value.trim();
    const img = document.getElementById('foto-preview-img');
    const placeholder = document.getElementById('foto-placeholder');
    if (url) {
        img.src = url;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    } else {
        img.src = '';
        img.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
    }
});

// Validação de senha em tempo real
const novaSenha     = document.getElementById('nova_senha');
const confirmarSenha = document.getElementById('confirmar_senha');
const senhaMatch    = document.getElementById('senha-match');

function checkSenha() {
    if (!novaSenha.value && !confirmarSenha.value) {
        senhaMatch.textContent = '';
        senhaMatch.className = 'senha-match';
        return;
    }
    if (novaSenha.value.length > 0 && novaSenha.value.length < 6) {
        senhaMatch.textContent = '⚠️ Mínimo 6 caracteres';
        senhaMatch.className = 'senha-match warn';
        return;
    }
    if (confirmarSenha.value && novaSenha.value !== confirmarSenha.value) {
        senhaMatch.textContent = '❌ As senhas não coincidem';
        senhaMatch.className = 'senha-match error';
    } else if (confirmarSenha.value && novaSenha.value === confirmarSenha.value) {
        senhaMatch.textContent = '✅ Senhas coincidem';
        senhaMatch.className = 'senha-match ok';
    } else {
        senhaMatch.textContent = '';
        senhaMatch.className = 'senha-match';
    }
}

novaSenha?.addEventListener('input', checkSenha);
confirmarSenha?.addEventListener('input', checkSenha);

// Bloquear submit se senhas não batem
document.querySelector('form')?.addEventListener('submit', function (e) {
    const n = novaSenha.value;
    const c = confirmarSenha.value;
    if (n && n !== c) {
        e.preventDefault();
        senhaMatch.textContent = '❌ As senhas não coincidem';
        senhaMatch.className = 'senha-match error';
        confirmarSenha.focus();
    }
});
</script>
</body>
</html>
