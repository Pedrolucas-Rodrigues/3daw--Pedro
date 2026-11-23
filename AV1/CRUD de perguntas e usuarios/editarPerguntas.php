<?php
require_once 'conexao.php';

$msg = "";
$erro = "";

$idEditar = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id_pergunta']) ? intval($_POST['id_pergunta']) : null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idEditar) {
    $novoTipo     = $_POST['tipo'] ?? 'multipla';
    $novaPergunta = trim($_POST['pergunta'] ?? '');

    if (empty($novaPergunta)) {
        $erro = "O enunciado da pergunta não pode ficar em branco.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE perguntas SET tipo = :tipo, pergunta = :pergunta WHERE id = :id");
            $stmt->execute([
                ':tipo'     => $novoTipo,
                ':pergunta' => $novaPergunta,
                ':id'       => $idEditar
            ]);

            $stmtDel = $pdo->prepare("DELETE FROM alternativas WHERE id_pergunta = :id");
            $stmtDel->execute([':id' => $idEditar]);

            if ($novoTipo === 'multipla') {
                $opcoes = [
                    'A' => trim($_POST['a'] ?? ''),
                    'B' => trim($_POST['b'] ?? ''),
                    'C' => trim($_POST['c'] ?? ''),
                    'D' => trim($_POST['d'] ?? '')
                ];
                $correta = $_POST['correta'] ?? 'A';

                $stmtAlt = $pdo->prepare("INSERT INTO alternativas (id_pergunta, texto, letra, correta) VALUES (:id_pergunta, :texto, :letra, :correta)");
                foreach ($opcoes as $letra => $texto) {
                    $isCorreta = ($letra === $correta) ? 1 : 0;
                    $stmtAlt->execute([
                        ':id_pergunta' => $idEditar,
                        ':texto'       => $texto,
                        ':letra'       => $letra,
                        ':correta'     => $isCorreta
                    ]);
                }
            }

            $pdo->commit();
            $msg = "Pergunta #{$idEditar} alterada com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao atualizar pergunta: " . $e->getMessage();
        }
    }
}

$dadosPergunta = null;
$opcoesAtuais  = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
$corretaAtual  = 'A';

if ($idEditar) {
    $stmt = $pdo->prepare("SELECT * FROM perguntas WHERE id = :id");
    $stmt->execute([':id' => $idEditar]);
    $dadosPergunta = $stmt->fetch();

    if ($dadosPergunta) {
        $stmtAlt = $pdo->prepare("SELECT * FROM alternativas WHERE id_pergunta = :id");
        $stmtAlt->execute([':id' => $idEditar]);
        $alternativas = $stmtAlt->fetchAll();

        foreach ($alternativas as $alt) {
            $letra = $alt['letra'];
            $opcoesAtuais[$letra] = $alt['texto'];
            if ($alt['correta'] == 1) {
                $corretaAtual = $letra;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar / Editar Perguntas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <a href="index.html" class="btn-voltar">← Voltar para o Menu Principal</a>

        <?php if (!empty($msg)) { ?>
            <div class="alerta sucesso"><?php echo $msg; ?></div>
        <?php } ?>

        <?php if (!empty($erro)) { ?>
            <div class="alerta erro"><?php echo $erro; ?></div>
        <?php } ?>

        <?php if (!$idEditar) { ?>
            <h1>Selecione uma Pergunta para Editar</h1>

            <?php
            $stmtLista = $pdo->query("SELECT * FROM perguntas ORDER BY id ASC");
            $perguntas = $stmtLista->fetchAll();

            if (!empty($perguntas)) {
            ?>
                <table class="tabela-perguntas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Pergunta</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perguntas as $p) { ?>
                            <tr>
                                <td>#<?php echo $p['id']; ?></td>
                                <td>
                                    <span class="badge">
                                        <?php echo $p['tipo'] === 'multipla' ? 'Múltipla Escolha' : 'Dissertativa'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($p['pergunta']); ?></td>
                                <td>
                                    <a href="editarPerguntas.php?id=<?php echo $p['id']; ?>" class="btn-editar">Editar</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="sem-dados">Nenhuma pergunta encontrada no banco de dados. <a href="criar_pergunta.php">Cadastrar Pergunta</a></p>
            <?php } ?>

        <?php } else { 
            if (!$dadosPergunta) {
                echo "<div class='alerta erro'>Erro: A pergunta #{$idEditar} não foi encontrada. <a href='editarPerguntas.php'>Ver todas as perguntas</a></div>";
            } else {
        ?>
            <h1>Editar Pergunta #<?php echo $dadosPergunta['id']; ?></h1>

            <form action="editarPerguntas.php?id=<?php echo $dadosPergunta['id']; ?>" method="POST">
                <input type="hidden" name="id_pergunta" value="<?php echo $dadosPergunta['id']; ?>">

                <label for="tipo">Tipo de Pergunta:</label>
                <select name="tipo" id="tipo" onchange="alternarCampos(this.value)" required>
                    <option value="multipla" <?php echo $dadosPergunta['tipo'] === 'multipla' ? 'selected' : ''; ?>>Múltipla Escolha</option>
                    <option value="dissertativa" <?php echo $dadosPergunta['tipo'] === 'dissertativa' ? 'selected' : ''; ?>>Dissertativa</option>
                </select>

                <br><br>

                <label for="pergunta">Pergunta / Enunciado:</label>
                <textarea name="pergunta" id="pergunta" rows="4" class="textarea-full" required><?php echo htmlspecialchars($dadosPergunta['pergunta']); ?></textarea>

                <br><br>

                <div id="bloco-multipla" class="<?php echo $dadosPergunta['tipo'] === 'multipla' ? '' : 'oculto'; ?>">
                    <h3>Alternativas</h3>
                    
                    <label for="a">[A]:</label>
                    <input type="text" name="a" id="a" value="<?php echo htmlspecialchars($opcoesAtuais['A']); ?>">
                    <br><br>

                    <label for="b">[B]:</label>
                    <input type="text" name="b" id="b" value="<?php echo htmlspecialchars($opcoesAtuais['B']); ?>">
                    <br><br>

                    <label for="c">[C]:</label>
                    <input type="text" name="c" id="c" value="<?php echo htmlspecialchars($opcoesAtuais['C']); ?>">
                    <br><br>

                    <label for="d">[D]:</label>
                    <input type="text" name="d" id="d" value="<?php echo htmlspecialchars($opcoesAtuais['D']); ?>">
                    <br><br>

                    <label for="correta">Qual é a alternativa correta?</label>
                    <select name="correta" id="correta">
                        <option value="A" <?php echo $corretaAtual === 'A' ? 'selected' : ''; ?>>Alternativa A</option>
                        <option value="B" <?php echo $corretaAtual === 'B' ? 'selected' : ''; ?>>Alternativa B</option>
                        <option value="C" <?php echo $corretaAtual === 'C' ? 'selected' : ''; ?>>Alternativa C</option>
                        <option value="D" <?php echo $corretaAtual === 'D' ? 'selected' : ''; ?>>Alternativa D</option>
                    </select>
                </div>

                <br><br>
                <input type="submit" value="Salvar Alterações">
                <a href="editarPerguntas.php" class="btn-cancelar">Voltar para a Lista</a>
            </form>
        <?php 
            }
        } 
        ?>
    </div>

    <script>
        function alternarCampos(tipo) {
            const blocoMultipla = document.getElementById('bloco-multipla');
            const inputs = blocoMultipla.querySelectorAll('input[type="text"]');
            
            if (tipo === 'dissertativa') {
                blocoMultipla.classList.add('oculto');
                inputs.forEach(i => i.removeAttribute('required'));
            } else {
                blocoMultipla.classList.remove('oculto');
                inputs.forEach(i => i.setAttribute('required', 'true'));
            }
        }
    </script>

</body>
</html>