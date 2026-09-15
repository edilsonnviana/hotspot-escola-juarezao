CREATE DATABASE IF NOT EXISTS rede_escola_juarezao DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rede_escola_juarezao;

CREATE TABLE IF NOT EXISTS alunos_autorizados (
    matricula VARCHAR(20) PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    turma VARCHAR(50),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hotspot_conexoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(20) NOT NULL,
    ip_cliente VARCHAR(45),
    mac_cliente VARCHAR(20),
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aluno_conexao 
        FOREIGN KEY (matricula) 
        REFERENCES alunos_autorizados(matricula) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuário padrão da secretaria (Senha: secretaria123)
INSERT INTO usuarios_admin (usuario, senha_hash) VALUES 
('secretaria', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm');