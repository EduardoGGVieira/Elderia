-- Rodar SQL no prompt de comando/ powershell: mysql -u root;
-- source C:/xampp/htdocs/Elderia/Banco/banco_elderia.sql;

CREATE DATABASE IF NOT EXISTS elderia;
USE elderia;


CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, 
    telefone VARCHAR(20),
    cpf VARCHAR(14) UNIQUE NOT NULL,
    tipo_usuario ENUM('idoso', 'profissional', 'admin') NOT NULL, 
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- pega o horario atual do sistema e salva automaticamente quando o usuário é criado
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
    visibilidade BOOLEAN DEFAULT TRUE, -- precisa ser visível para aparecer na lista de profissionais, o profissional pode escolher se quer ou não aparecer publicamente, tem q add isso no cadastro do profissional

    data_emissao DATE,
    url_documento VARCHAR(255), 
    documento_foto VARCHAR(255),

    documento_validado BOOLEAN DEFAULT FALSE,
    localizacao VARCHAR(300), 
    FOREIGN KEY (id_profissional) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);


CREATE TABLE certificado (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY, 
    id_profissional INT NOT NULL,
    titulo VARCHAR(100), 
    data_emissao DATE,
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE,
    url_documento VARCHAR(255)
);
 

 -- tutu nao sabe qual é qual

-- CREATE TABLE agenda_disponivel (
--     id_agenda INT AUTO_INCREMENT PRIMARY KEY,
--     id_profissional INT NOT NULL,
--     dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'),
--     horario TIME NOT NULL,
--     FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
-- );


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

-- USUÁRIOS
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Jalim Rabei', 'jalim@email.com', 'senha123', '41999999999', '123.456.789-00', 'idoso');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Ghost the down cool', 'ghost@email.com', 'GhostABanda', '41966666666', '666.666.666-66', 'idoso');


-- ADIMIN
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Eduardo Guilhermino', 'edu@ggv', '$2y$10$CaMhbwmQxvfYesMhwT7.DuByPCew4X8FGOYJpk52Z2B1u8akmLYFS', '41999999999', '999.999.999-99', 'admin');

-- PROFISSIONAIS

-- inserir as duas linhas.
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Ricardão da DOR', 'ricardo.quiro@email.com', '789', '412323232323', '000.111.222-33', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'ABC-12345', 'Quiropraxista', 'Acabo com qualquer dor.', TRUE, TRUE ,'Curitiba, PR');



INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Dr NEFÁRIO', 'drnefario@gamail.com', '123456789', '41988881111', '111.222.333-44', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'CRM-PR-55667', 'Geriatra', 'Especialista em saúde preventiva e acompanhamento de doenças crônicas na terceira idade.', TRUE, TRUE, 'Curitiba, PR');




INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Pedro Alvares', 'pedro.nutri@elderia.com', 'senha_hash_5', '41977772222', '222.333.444-55', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'CRN-8-9900', 'Nutricionista', 'Foco em dietas adaptadas para idosos e controle nutricional de diabetes e hipertensão.', TRUE, TRUE, 'São José dos Pinhais, PR');


INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Fernanda Alves', 'fernanda.nutri@email.com', '789', '41977665544', '333.444.555-66', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'NUTRI-11223', 'Nutricionista', 'Ajudo idosos a manterem uma alimentação saudável e equilibrada.', TRUE, TRUE, 'Curitiba, PR');





-- Select para ver todos os dados de um idoso no banco

-- SELECT  
--     u.id_usuario,
--     u.nome,
--     u.email,
--     u.telefone,
--     u.cpf,
--     i.data_nascimento,
--     i.possui_acessibilidade,
--     i.necessidades_acessibilidade,
--     i.informacoes_saude,
--     i.alergias 
--     FROM usuario u INNER JOIN idoso i ON u.id_usuario = i.id_idoso WHERE u.tipo_usuario = 'idoso';



-- SELECT 
--     u.id_usuario,
--     u.nome,
--     u.email,
--     u.telefone,
--     u.cpf,
--     p.registro_profissional,
--     p.especialidade,
--     p.biografia,
--     p.visibilidade,
--     p.documento_validado,
--     p.localizacao
-- FROM usuario u
-- INNER JOIN profissional p 
--     ON u.id_usuario = p.id_profissional
-- WHERE u.tipo_usuario = 'profissional';