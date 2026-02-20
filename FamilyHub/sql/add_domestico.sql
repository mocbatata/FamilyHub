-- Adicionar categoria Doméstico ao ENUM da tabela tarefa
ALTER TABLE tarefa
    MODIFY COLUMN categoria ENUM('Escola','Saude','Financeiro','Social','Domestico') NOT NULL DEFAULT 'Escola';
