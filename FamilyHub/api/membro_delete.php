<?php
// ============================================
// FamilyHub API - Excluir Membro
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

require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ../membros.php?msg=erro');
    exit;
}

// Não permitir excluir a si mesmo
if ($id === ($_SESSION['membro_id'] ?? 0)) {
    header('Location: ../membros.php?msg=erro');
    exit;
}

try {
    // O CASCADE na FK do usuario vai remover o login automaticamente
    $stmt = $pdo->prepare("DELETE FROM membro WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ../membros.php?msg=excluido');
}
catch (PDOException $e) {
    header('Location: ../membros.php?msg=erro');
}
exit;
