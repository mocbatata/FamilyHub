<?php
// ============================================
// FamilyHub API - Adicionar Membro (com foto)
// ============================================

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!($_SESSION['is_admin'] ?? false)) {
    header('Location: ../membros.php?msg=erro');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../membros.php');
    exit;
}

require_once '../config/database.php';

$nome   = trim($_POST['nome']  ?? '');
$idade  = intval($_POST['idade'] ?? 0);
$email  = trim($_POST['email'] ?? '');
$senha  = $_POST['senha'] ?? '';
$foto_path = trim($_POST['foto'] ?? '');

if (empty($nome) || $idade <= 0) {
    header('Location: ../membros.php?msg=erro');
    exit;
}

// Validar se é uma URL válida ou vazio
if ($foto_path !== '' && !filter_var($foto_path, FILTER_VALIDATE_URL)) {
    // Se não for URL, tratamos como nulo ou mantemos se for um path local legado
    // Mas para novos cadastros, forçamos URL ou vazio
    if (!file_exists(__DIR__ . '/../' . $foto_path)) {
        $foto_path = null;
    }
}

if (empty($foto_path)) {
    $foto_path = null;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO membro (nome, idade, foto) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $idade, $foto_path]);
    $membro_id = $pdo->lastInsertId();

    if (!empty($email) && !empty($senha)) {
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuario (email, senha, membro_id) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, $membro_id]);
    }

    $pdo->commit();
    header('Location: ../membros.php?msg=criado');
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: ../membros.php?msg=erro');
}
exit;
