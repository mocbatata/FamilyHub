-- ============================================
-- FamilyHub - Schema do Banco de Dados
-- Banco: agenda_familia
-- ============================================

CREATE DATABASE IF NOT EXISTS agenda_familia
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE agenda_familia;

-- ============================================
-- TABELA: membro
-- ============================================
CREATE TABLE IF NOT EXISTS membro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    idade INT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABELA: tarefa
-- ============================================
CREATE TABLE IF NOT EXISTS tarefa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    categoria ENUM('Escola','Saude','Financeiro','Social') NOT NULL DEFAULT 'Escola',
    data_limite DATE,
    status ENUM('Pendente','Em andamento','Concluída') NOT NULL DEFAULT 'Pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    membro_id INT,
    FOREIGN KEY (membro_id) REFERENCES membro(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABELA: usuario
-- ============================================
CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    membro_id INT,
    FOREIGN KEY (membro_id) REFERENCES membro(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- DADOS INICIAIS: Membros
-- ============================================
INSERT INTO membro (nome, idade) VALUES
('João Silva', 42),
('Maria Silva', 40),
('Lucas Silva', 16),
('Ana Silva', 14),
('Pedro Silva', 12);

-- ============================================
-- DADOS INICIAIS: Usuários (senha: admin123)
-- Hash bcrypt de 'admin123'
-- ============================================
INSERT INTO usuario (email, senha, membro_id) VALUES
('joao@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('maria@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('lucas@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('ana@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
('pedro@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5);

-- ============================================
-- DADOS INICIAIS: Tarefas
-- ============================================
INSERT INTO tarefa (titulo, descricao, categoria, data_limite, status, membro_id) VALUES
('Reunião de pais', 'Reunião semestral na escola do Lucas', 'Escola', '2026-02-20', 'Pendente', 1),
('Levar Ana ao médico', 'Consulta de rotina com pediatra', 'Saude', '2026-02-22', 'Pendente', 2),
('Fazer compras do mês', 'Compras de supermercado mensal', 'Financeiro', '2026-02-25', 'Em andamento', 1),
('Aniversário do Lucas', 'Preparar festa surpresa', 'Social', '2026-03-05', 'Pendente', 2),
('Estudar matemática', 'Preparar para prova de álgebra', 'Escola', '2026-02-21', 'Em andamento', 3),
('Consulta dentista', 'Checkup semestral', 'Saude', '2026-02-28', 'Pendente', 4),
('Pagamento de contas', 'Luz, água e internet do mês', 'Financeiro', '2026-02-27', 'Concluída', 1),
('Jogo de futebol', 'Campeonato inter-escolar', 'Social', '2026-03-01', 'Pendente', 5);
