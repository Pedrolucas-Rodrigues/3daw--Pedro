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
    <title>Alterar Aluno</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px;">
        <h2>Alterar Dados do Aluno</h2>
        
        <form action="processa_alteracao.php" method="POST">
            <input type="hidden" name="matricula" value="<?= htmlspecialchars($aluno['matricula']) ?>">

            <div class="form-group">
                <label for="matricula_display">Matrícula</label>
                <input type="text" id="matricula_display" value="<?= htmlspecialchars($aluno['matricula']) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($aluno['nome']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($aluno['email']) ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="listar_alunos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
