<?php
// ============================================
// FamilyHub API - Atualizar Perfil
// ============================================

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../perfil.php');
    exit;
}

require_once '../config/database.php';

$usuario_id = $_SESSION['usuario_id'];
$membro_id  = $_SESSION['membro_id'];
$email      = trim($_POST['email'] ?? '');
$nova_senha = $_POST['nova_senha'] ?? '';
$conf_senha = $_POST['confirmar_senha'] ?? '';

// Validar senhas
if ($nova_senha !== '') {
    if (strlen($nova_senha) < 6) {
        header('Location: ../perfil.php?erro=senha_curta');
        exit;
    }
    if ($nova_senha !== $conf_senha) {
        header('Location: ../perfil.php?erro=senha');
        exit;
    }
}

try {
    $pdo->beginTransaction();

    // --- Foto (URL) ---
    if (isset($_POST['foto_url'])) {
        $foto_url = trim($_POST['foto_url']);
        if ($foto_url === '' || filter_var($foto_url, FILTER_VALIDATE_URL)) {
            $pdo->prepare("UPDATE membro SET foto = ? WHERE id = ?")
                ->execute([$foto_url ?: null, $membro_id]);
        }
    }

    // --- Email ---
    if ($email !== '') {
        // Verificar se email já existe em outro usuário
        $chk = $pdo->prepare("SELECT id FROM usuario WHERE email = ? AND id != ?");
        $chk->execute([$email, $usuario_id]);
        if ($chk->fetch()) {
            $pdo->rollBack();
            header('Location: ../perfil.php?erro=email_uso');
            exit;
        }
        $pdo->prepare("UPDATE usuario SET email = ? WHERE id = ?")
            ->execute([$email, $usuario_id]);
    }

    // --- Senha ---
    if ($nova_senha !== '') {
        $hash = password_hash($nova_senha, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE usuario SET senha = ? WHERE id = ?")
            ->execute([$hash, $usuario_id]);
    }

    $pdo->commit();
    header('Location: ../perfil.php?msg=ok');

} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: ../perfil.php?erro=geral');
}
exit;
