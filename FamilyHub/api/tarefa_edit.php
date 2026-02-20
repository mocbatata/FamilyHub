<?php
// ============================================
// FamilyHub API - Editar Tarefa (Admin Only)
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../atividades.php');
    exit;
}

require_once '../config/database.php';

$id = intval($_POST['id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria = $_POST['categoria'] ?? 'Escola';
$data_limite = $_POST['data_limite'] ?? null;
$status = $_POST['status'] ?? 'Pendente';
$membro_id = intval($_POST['membro_id'] ?? 0);

if ($id <= 0 || empty($titulo)) {
    header('Location: ../atividades.php?msg=erro');
    exit;
}

$categorias_validas = ['Escola', 'Saude', 'Financeiro', 'Social', 'Domestico'];
$status_validos = ['Pendente', 'Em andamento', 'Concluída'];

if (!in_array($categoria, $categorias_validas))
    $categoria = 'Escola';
if (!in_array($status, $status_validos))
    $status = 'Pendente';

try {
    $stmt = $pdo->prepare("
        UPDATE tarefa 
        SET titulo = ?, descricao = ?, categoria = ?, data_limite = ?, status = ?, membro_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$titulo, $descricao, $categoria, $data_limite, $status, $membro_id, $id]);

    header('Location: ../atividades.php?msg=editada');
}
catch (PDOException $e) {
    header('Location: ../atividades.php?msg=erro');
}
exit;
