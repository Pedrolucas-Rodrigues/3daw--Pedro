CREATE DATABASE IF NOT EXISTS sistema_perguntas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_perguntas;

CREATE TABLE perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL,
    pergunta TEXT NOT NULL
);

CREATE TABLE alternativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pergunta INT NOT NULL,
    texto TEXT NOT NULL,
    letra CHAR(1) NOT NULL,
    correta TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_pergunta) REFERENCES perguntas(id) ON DELETE CASCADE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL
);