-- Executar no MySQL Workbench para adicionar suporte a foto nos membros
USE agenda_familia;
ALTER TABLE membro ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL;
