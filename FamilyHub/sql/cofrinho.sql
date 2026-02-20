-- Tabela do Cofrinho da Família
CREATE TABLE IF NOT EXISTS cofrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(150) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL DEFAULT 'entrada',
    data DATE NOT NULL,
    membro_id INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membro_id) REFERENCES membro(id) ON DELETE SET NULL
) ENGINE=InnoDB;
