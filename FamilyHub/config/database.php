<?php
$db_host = 'localhost:3306';
$db_name = 'agenda_familia';
$db_user = 'root';
$db_pass = 'Senai@118';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="text-align:center;padding:50px;font-family:Segoe UI,sans-serif;">
        <h2>⚠️ Erro de Conexão</h2>
        <p>Não foi possível conectar ao banco de dados.</p>
        <p style="color:#999;font-size:13px;">' . $e->getMessage() . '</p>
    </div>');
}
