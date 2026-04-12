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
    visibilidade BOOLEAN DEFAULT TRUE,
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

CREATE TABLE agenda_disponivel (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'),
    horario TIME NOT NULL,
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
);

/* to deixando isso aqui comentado pq eu nao entendi a lógica de horario_inicio e horario_fim, entao fiz do meu jeitao
CREATE TABLE agenda_disponivel ( 
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    data_disponivel DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    status ENUM('livre', 'ocupado','concluido','reagendado') DEFAULT 'livre',
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
);
*/


CREATE TABLE consulta (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    id_idoso INT NOT NULL,
    id_profissional INT NOT NULL,
    data_hora DATETIME NOT NULL,
    status ENUM('agendada', 'realizada', 'cancelada') DEFAULT 'agendada', 
    resumo_atendimento TEXT,
    FOREIGN KEY (id_idoso) REFERENCES idoso(id_idoso),
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional)

);


CREATE TABLE avaliacao (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_consulta INT UNIQUE NOT NULL,
    nota INT CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT, 
    status_moderacao ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    FOREIGN KEY (id_consulta) REFERENCES consulta(id_consulta)
);

-- USUÁRIOS
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Jalim Rabei', 'jalim@email.com', 'senha123', '41999999999', '123.456.789-00', 'idoso');

INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Ghost the down cool', 'ghost@email.com', 'GhostABanda', '41966666666', '666.666.666-66', 'idoso');


-- ADIMIN
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Eduardo Guilhermino', 'edu@ggv', 'senha_hash', '41999999999', '999.999.999-99', 'admin');
-- PROFISSIONAIS

-- inserir as duas linhas.
INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('RICARDÃO DA DOR', 'ricardo.quiro@email.com', '789', '412323232323', '000.111.222-33', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado) VALUES (LAST_INSERT_ID(), 'ABC-12345', 'Quiropraxista', 'Especialista em alinhamento vertebral e alívio de dores crônicas em idosos.', TRUE, TRUE);



INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Dr NEFÁRIO', 'drnefario@gamail.com', '123456789', '41988881111', '111.222.333-44', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'CRM-PR-55667', 'Geriatra', 'Especialista em saúde preventiva e acompanhamento de doenças crônicas na terceira idade.', TRUE, TRUE, 'Curitiba, PR');




INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) VALUES ('Pedro Alvares', 'pedro.nutri@elderia.com', 'senha_hash_5', '41977772222', '222.333.444-55', 'profissional');
INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, visibilidade, documento_validado, localizacao) VALUES (LAST_INSERT_ID(), 'CRN-8-9900', 'Nutricionista', 'Foco em dietas adaptadas para idosos e controle nutricional de diabetes e hipertensão.', TRUE, TRUE, 'São José dos Pinhais, PR');
