-- Rodar SQL no prompt de comando/ powershell: mysql -u root;
-- source C:/xampp/htdocs/Elderia/Banco/banco_elderia.sql;

DROP DATABASE IF EXISTS elderia;
CREATE DATABASE elderia;
USE elderia;

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14) UNIQUE NOT NULL,
    tipo_usuario ENUM('idoso', 'profissional', 'admin') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE idoso (
    id_idoso INT PRIMARY KEY,
    data_nascimento DATE,
    possui_acessibilidade BOOLEAN DEFAULT FALSE,
    necessidades_acessibilidade TEXT,
    informacoes_saude TEXT,
    alergias VARCHAR(300),
    FOREIGN KEY (id_idoso) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE profissional (
    id_profissional INT PRIMARY KEY,
    registro_profissional VARCHAR(50) UNIQUE NOT NULL,
    especialidade VARCHAR(100),
    biografia TEXT,
    visibilidade BOOLEAN DEFAULT FALSE,
    localizacao VARCHAR(300),

    documentacao_numero VARCHAR(80),
    documentacao_url VARCHAR(255),
    documentacao_status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente',

    FOREIGN KEY (id_profissional) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE certificado (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    titulo VARCHAR(100),
    data_emissao DATE,
    url_documento VARCHAR(255),
    status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente',
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
);

CREATE TABLE agenda_disponivel (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    data_hora DATETIME NOT NULL,
    status ENUM('livre','agendado') DEFAULT 'livre',
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
);

CREATE TABLE consulta (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    id_idoso INT NOT NULL,
    id_profissional INT NOT NULL,
    data_hora DATETIME NOT NULL,
    status ENUM('agendada', 'confirmada', 'recusada', 'realizada', 'cancelada') DEFAULT 'agendada',
    resumo_atendimento TEXT,
    FOREIGN KEY (id_idoso) REFERENCES idoso(id_idoso),
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional)
);

CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    id_usuario INT NOT NULL,
    nota INT CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    status_moderacao ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- senha 1234
INSERT INTO usuario (id_usuario, nome, email, senha, telefone, cpf, tipo_usuario) VALUES
(8,'Eduardo Guilhermino IDOSO','eduardoggv@gmail.com', '$2y$10$CaMhbwmQxvfYesMhwT7.DuByPCew4X8FGOYJpk52Z2B1u8akmLYFS','41988889999', '888.777.666-55', 'idoso');

INSERT INTO idoso VALUES
(8, '1948-06-15', TRUE, 'Necessita cuidados gostosos', 'Hipertensão,faz uso contínuo de REELS do insta.', 'Alergia a veganos e enzos');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Eduardo Guilhermino PROFISSIONAL', 'dudu@gmail.com', '$2y$10$CaMhbwmQxvfYesMhwT7.DuByPCew4X8FGOYJpk52Z2B1u8akmLYFS', '67676767676', '123.269.789-35', 'profissional');

INSERT INTO profissional
(id_profissional, registro_profissional, especialidade, biografia, visibilidade, localizacao, documentacao_numero, documentacao_url, documentacao_status)
VALUES
(LAST_INSERT_ID(), 'SEX-6969', 'Quiropraxista', 'Faço você nascer novamente.', TRUE, 'Araucária, PR', 'RG-123456', NULL, 'pendente');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Eduardo Guilhermino ADMIN', 'edu@ggv', '$2y$10$CaMhbwmQxvfYesMhwT7.DuByPCew4X8FGOYJpk52Z2B1u8akmLYFS', '41999999999', '999.999.999-99', 'admin');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Ricardão da DOR', 'ricardo.quiro@email.com', '789', '412323232323', '000.111.222-33', 'profissional');

INSERT INTO profissional
(id_profissional, registro_profissional, especialidade, biografia, visibilidade, localizacao, documentacao_numero, documentacao_url, documentacao_status)
VALUES
(LAST_INSERT_ID(), 'ABC-12345', 'Quiropraxista', 'Acabo com qualquer dor.', TRUE, 'Curitiba, PR', 'RG-999999', NULL, 'pendente');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Dr NEFÁRIO', 'drnefario@gamail.com', '123456789', '41988881111', '111.222.333-44', 'profissional');

INSERT INTO profissional
(id_profissional, registro_profissional, especialidade, biografia, visibilidade, localizacao, documentacao_numero, documentacao_url, documentacao_status)
VALUES
(LAST_INSERT_ID(), 'CRM-PR-55667', 'Geriatra', 'Especialista em saúde preventiva e acompanhamento de doenças crônicas na terceira idade.', TRUE, 'Curitiba, PR', 'CRM-PR-55667', NULL, 'pendente');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Pedro Alvares', 'pedro.nutri@elderia.com', 'senha_hash_5', '41977772222', '222.333.444-55', 'profissional');

INSERT INTO profissional
(id_profissional, registro_profissional, especialidade, biografia, visibilidade, localizacao, documentacao_numero, documentacao_url, documentacao_status)
VALUES
(LAST_INSERT_ID(), 'CRN-8-9900', 'Nutricionista', 'Foco em dietas adaptadas para idosos e controle nutricional de diabetes e hipertensão.', TRUE, 'São José dos Pinhais, PR', 'CRN-8-9900', NULL, 'pendente');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES
('Fernanda Alves', 'fernanda.nutri@email.com', '789', '41977665544', '333.444.555-66', 'profissional');

INSERT INTO profissional
(id_profissional, registro_profissional, especialidade, biografia, visibilidade, localizacao, documentacao_numero, documentacao_url, documentacao_status)
VALUES
(LAST_INSERT_ID(), 'NUTRI-11223', 'Nutricionista', 'Ajudo idosos a manterem uma alimentação saudável e equilibrada.', TRUE, 'Curitiba, PR', 'NUTRI-11223', NULL, 'pendente');

INSERT INTO avaliacao (`id_profissional`, `id_usuario`, `nota`, `comentario`, `status_moderacao`) VALUES
(9, 8, 5, 'Excelente atendimento de quiropraxia, muito cuidadoso e profissional!', 'aprovada'),
(11, 8, 4, 'O atendimento tirou minhas dores nas costas rapidinho. Recomendo.', 'aprovada'),
(12, 8, 5, 'Excelente médico geriatra. Explicou detalhadamente todas as medicações.', 'aprovada'),
(13, 8, 5, 'O plano de nutrição adaptado ajudou muito na minha disposição diária.', 'aprovada'),
(14, 8, 4, 'Muito atenciosa e paciente para montar a dieta do meu mês.', 'aprovada'),
(11, 8, 3, 'O tratamento foi bom, mas a consulta atrasou alguns minutos.', 'pendente');

SET @id_eduardo_prof = (SELECT id_usuario FROM usuario WHERE email = 'dudu@gmail.com' LIMIT 1);

INSERT INTO agenda_disponivel (id_profissional, data_hora, status) VALUES
(@id_eduardo_prof, '2026-05-20 09:00:00', 'livre'),
(@id_eduardo_prof, '2026-05-21 10:30:00', 'livre'),
(@id_eduardo_prof, '2026-05-22 14:00:00', 'livre'),
(@id_eduardo_prof, '2026-05-23 08:30:00', 'livre'),
(@id_eduardo_prof, '2026-05-24 11:00:00', 'livre'),
(@id_eduardo_prof, '2026-05-25 15:30:00', 'livre'),
(@id_eduardo_prof, '2026-05-26 10:00:00', 'livre'),
(@id_eduardo_prof, '2026-05-27 16:00:00', 'livre');