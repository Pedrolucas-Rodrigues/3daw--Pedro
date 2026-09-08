<?php
require_once 'conexao.php';

$stmt = $pdo->query("SELECT matricula, nome, email FROM alunos ORDER BY matricula ASC");
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alunos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Listagem de Alunos</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th style="width: 170px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($alunos)): ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td><?= htmlspecialchars($aluno['matricula']) ?></td>
                                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                <td><?= htmlspecialchars($aluno['email']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="form_alterar.php?matricula=<?= urlencode($aluno['matricula']) ?>" class="btn btn-primary">Alterar</a>
                                        <a href="confirmar_exclusao.php?matricula=<?= urlencode($aluno['matricula']) ?>" class="btn btn-danger">Excluir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b;">Nenhum aluno encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
