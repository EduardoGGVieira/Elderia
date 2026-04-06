    -- Criação do Banco de Dados Elderia


-- execute source C:/xampp/htdocs/Elderia/Elderia/Banco/banco_elderia.sql

sql execute source C:\xampp\htdocs\Elderia\Elderia\Banco\banco_elderia.sql;



CREATE DATABASE IF NOT EXISTS elderia;
USE elderia;


CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, 
    telefone VARCHAR(20),

    tipo_usuario ENUM('idoso', 'profissional', 'admin') NOT NULL, 

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);


CREATE TABLE idoso (
    id_idoso INT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    data_nascimento DATE,
    FOREIGN KEY (id_idoso) REFERENCES usuario(id_usuario) ON DELETE CASCADE



    necessidades_acessibilidade TEXT, 
    informacoes_saude TEXT, 
);


CREATE TABLE profissional (
    id_profissional INT PRIMARY KEY,
    registro_profissional VARCHAR(50) UNIQUE NOT NULL,
    especialidade VARCHAR(100),
    biografia TEXT,
    documento_validado BOOLEAN DEFAULT FALSE, 
    FOREIGN KEY (id_profissional) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    


    visibilidade BOOLEAN DEFAULT FALSE --nao pode ser boolean tem que trocar

);


CREATE TABLE certificado (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY, --certificado tem id?????
    id_profissional INT NOT NULL,
    titulo VARCHAR(100), --faculdade???
    data_emissao DATE,
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE,


    url_documento VARCHAR(255) -- Caminho para o arquivo
    
    
);


CREATE TABLE agenda_disponivel ( 
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_profissional INT NOT NULL,
    data_disponivel DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    status ENUM('livre', 'ocupado') DEFAULT 'livre',
    FOREIGN KEY (id_profissional) REFERENCES profissional(id_profissional) ON DELETE CASCADE
);


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
    FOREIGN KEY (id_consulta) REFERENCES consulta(id_consulta) ON DELETE CASCADE
);