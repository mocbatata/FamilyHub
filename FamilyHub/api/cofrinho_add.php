<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: ../login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../cofrinho.php'); exit; }

require_once '../config/database.php';

$descricao = trim($_POST['descricao'] ?? '');
$valor     = floatval($_POST['valor']  ?? 0);
$tipo      = $_POST['tipo']      ?? 'entrada';
$data      = $_POST['data']      ?? date('Y-m-d');
$membro_id = intval($_POST['membro_id'] ?? 0);

if (empty($descricao) || $valor <= 0 || $membro_id <= 0) {
    header('Location: ../cofrinho.php?msg=erro'); exit;
}
if (!in_array($tipo, ['entrada', 'saida'])) $tipo = 'entrada';

try {
    $pdo->prepare("INSERT INTO cofrinho (descricao, valor, tipo, data, membro_id) VALUES (?,?,?,?,?)")
        ->execute([$descricao, $valor, $tipo, $data, $membro_id]);
    header('Location: ../cofrinho.php?msg=criado');
} catch (PDOException $e) {
    header('Location: ../cofrinho.php?msg=erro');
}
exit;
