<?php
// ============================================
// FamilyHub API - Excluir Tarefa (Admin Only)
// ============================================

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!($_SESSION['is_admin'] ?? false)) {
    header('Location: ../atividades.php?msg=sem-permissao');
    exit;
}

require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ../atividades.php?msg=erro');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM tarefa WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: ../atividades.php?msg=excluida');
}
catch (PDOException $e) {
    header('Location: ../atividades.php?msg=erro');
}
exit;
