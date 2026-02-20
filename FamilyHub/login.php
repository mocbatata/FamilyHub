<?php
// ============================================
// FamilyHub - Página de Login
// ============================================

session_start();

// Se já estiver logado, redirecionar para index
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    }
    else {
        $stmt = $pdo->prepare('
            SELECT u.id, u.senha, u.membro_id, m.nome, m.idade
            FROM usuario u
            JOIN membro m ON u.membro_id = m.id
            WHERE u.email = ?
        ');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Determinar se é admin (id 1 ou 2 = Pai e Mãe)
            $is_admin = in_array($usuario['membro_id'], [1, 2]);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['membro_id'] = $usuario['membro_id'];
            $_SESSION['membro_nome'] = $usuario['nome'];
            $_SESSION['is_admin'] = $is_admin;

            header('Location: index.php');
            exit;
        }
        else {
            $erro = 'Email ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FamilyHub - Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>

    <!-- Animated Background -->
    <div class="bg-animated">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">🏠</div>
                <h1>FamilyHub</h1>
                <p>Gerencie a agenda da sua família</p>
            </div>

            <?php if ($erro): ?>
                <div class="login-error">
                    <span>⚠️</span> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="senha">🔒 Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-login">
                    Entrar
                    <span class="btn-arrow">→</span>
                </button>
            </form>

            <div class="login-footer">
                <p>Agenda Familiar Inteligente</p>
            </div>
        </div>
    </div>

    <script src="js/theme.js"></script>

</body>

</html>
