<?php
// ============================================
// FamilyHub API - Adicionar Tarefa
// Admin: pode tudo. Membro: pode adicionar (mas não editar/excluir)
// ============================================

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Ambos admin e membro podem adicionar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../atividades.php');
    exit;
}

require_once '../config/database.php';

$titulo     = trim($_POST['titulo']     ?? '');
$descricao  = trim($_POST['descricao']  ?? '');
$categoria  = $_POST['categoria']       ?? 'Escola';
$data_limite= $_POST['data_limite']     ?? null;
$status     = $_POST['status']          ?? 'Pendente';
$membro_id  = intval($_POST['membro_id']?? 0);

if (empty($titulo) || $membro_id <= 0) {
    header('Location: ../atividades.php?msg=erro');
    exit;
}

$categorias_validas = ['Escola','Saude','Financeiro','Social','Domestico'];
$status_validos     = ['Pendente','Em andamento','Concluída'];

if (!in_array($categoria, $categorias_validas)) $categoria = 'Escola';
if (!in_array($status,    $status_validos))     $status    = 'Pendente';

try {
    $stmt = $pdo->prepare("
        INSERT INTO tarefa (titulo, descricao, categoria, data_limite, status, membro_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$titulo, $descricao, $categoria, $data_limite ?: null, $status, $membro_id]);
    header('Location: ../atividades.php?msg=criada');
} catch (PDOException $e) {
    header('Location: ../atividades.php?msg=erro');
}
exit;
