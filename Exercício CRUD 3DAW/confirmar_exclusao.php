<?php
require_once 'conexao.php';

$matricula = filter_input(INPUT_GET, 'matricula', FILTER_VALIDATE_INT);

if (!$matricula) {
    header("Location: listar_alunos.php");
    exit;
}

$stmt = $pdo->prepare("SELECT matricula, nome, email FROM alunos WHERE matricula = ?");
$stmt->execute([$matricula]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    echo "<p>Aluno não encontrado. <a href='listar_alunos.php'>Voltar</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Exclusão</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 520px;">
        <div class="alert-box">
            <h3>Confirmar Exclusão</h3>
            <p>Você tem certeza de que deseja remover permanentemente este aluno do sistema?</p>
        </div>

        <ul class="details-list">
            <li><strong>Matrícula:</strong> <?= htmlspecialchars($aluno['matricula']) ?></li>
            <li><strong>Nome:</strong> <?= htmlspecialchars($aluno['nome']) ?></li>
            <li><strong>E-mail:</strong> <?= htmlspecialchars($aluno['email']) ?></li>
        </ul>

        <form action="processa_exclusao.php" method="POST">
            <input type="hidden" name="matricula" value="<?= htmlspecialchars($aluno['matricula']) ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-danger">Sim, Confirmar Exclusão</button>
                <a href="listar_alunos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
