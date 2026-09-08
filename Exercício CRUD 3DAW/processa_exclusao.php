<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = filter_input(INPUT_POST, 'matricula', FILTER_VALIDATE_INT);

    if ($matricula) {
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE matricula = ?");
        $stmt->execute([$matricula]);
    }
}

header("Location: listar_alunos.php");
exit;
