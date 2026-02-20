<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: ../login.php'); exit; }
if (!($_SESSION['is_admin'] ?? false)) { header('Location: ../cofrinho.php?msg=erro'); exit; }

require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ../cofrinho.php'); exit; }

try {
    $pdo->prepare("DELETE FROM cofrinho WHERE id = ?")->execute([$id]);
    header('Location: ../cofrinho.php?msg=excluido');
} catch (PDOException $e) {
    header('Location: ../cofrinho.php?msg=erro');
}
exit;
