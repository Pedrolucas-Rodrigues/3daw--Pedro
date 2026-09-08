<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = filter_input(INPUT_POST, 'matricula', FILTER_VALIDATE_INT);
    $nome      = trim(filter_input(INPUT_POST, 'nome', FILTER_DEFAULT) ?? '');
    $email     = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');

    if ($matricula && !empty($nome) && $email) {
        $stmt = $pdo->prepare("UPDATE alunos SET nome = ?, email = ? WHERE matricula = ?");
        $stmt->execute([$nome, $email, $matricula]);
    }
}

header("Location: listar_alunos.php");
exit;
