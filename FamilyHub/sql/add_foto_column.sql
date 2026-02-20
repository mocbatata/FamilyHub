-- Rodar este SQL caso a coluna foto ainda não exista na tabela membro
ALTER TABLE membro ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL;
