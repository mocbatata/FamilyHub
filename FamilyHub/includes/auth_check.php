<?php
// ============================================
// FamilyHub - Verificação de Autenticação
// ============================================

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id  = $_SESSION['usuario_id'];
$membro_id   = $_SESSION['membro_id'];
$membro_nome = $_SESSION['membro_nome'];
$is_admin    = $_SESSION['is_admin'] ?? false;

require_once __DIR__ . '/../config/database.php';

// Garantir que a coluna foto existe na tabela membro
try {
    $pdo->exec("ALTER TABLE membro ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL");
} catch (PDOException $e) {
    // Ignora se já existir ou banco não suportar IF NOT EXISTS
}

// Buscar foto e email do membro logado
try {
    $stmt_perfil = $pdo->prepare("
        SELECT m.foto, u.email
        FROM membro m
        LEFT JOIN usuario u ON u.membro_id = m.id
        WHERE m.id = ?
        LIMIT 1
    ");
    $stmt_perfil->execute([$membro_id]);
    $perfil_row   = $stmt_perfil->fetch();
    $membro_foto  = $perfil_row['foto']  ?? null;
    $membro_email = $perfil_row['email'] ?? null;
} catch (PDOException $e) {
    $membro_foto  = null;
    $membro_email = null;
}
