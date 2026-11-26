<?php
header('Content-Type: application/json; charset=utf-8');
require 'conexao.php';


$dados = json_decode(file_get_contents('php://input'), true);

$nome = trim($dados['nome'] ?? '');
$codigo = strtoupper(trim($dados['codigo'] ?? ''));
$carga_horaria = filter_var($dados['carga_horaria'] ?? 0, FILTER_VALIDATE_INT);

if (empty($nome) || empty($codigo) || !$carga_horaria || $carga_horaria <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha todos os campos corretamente.']);
    exit;
}

try {
    $sql = "INSERT INTO disciplinas (nome, codigo, carga_horaria) VALUES (:nome, :codigo, :carga_horaria)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':codigo' => $codigo,
        ':carga_horaria' => $carga_horaria
    ]);

    echo json_encode([
        'status' => 'sucesso', 
        'mensagem' => 'Disciplina cadastrada com sucesso!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (\PDOException $e) {
    // Código 23000 indica violação de chave única (ex: código da disciplina duplicado)
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Já existe uma disciplina com este código.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar no banco de dados.']);
    }
}