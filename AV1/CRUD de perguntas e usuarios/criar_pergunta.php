<?php
require_once 'conexao.php';

$msg = "";
$erro = "";

if (isset($_GET['sucesso']) && isset($_GET['id'])) {
    $idCadastrado = intval($_GET['id']);
    $msg = "Pergunta #{$idCadastrado} cadastrada com sucesso!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo     = $_POST['tipo'] ?? 'multipla';
    $pergunta = trim($_POST['pergunta'] ?? '');

    if (empty($pergunta)) {
        $erro = "Por favor, digite o enunciado da pergunta.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO perguntas (tipo, pergunta) VALUES (:tipo, :pergunta)");
            $stmt->execute([
                ':tipo'     => $tipo,
                ':pergunta' => $pergunta
            ]);
            $novoId = $pdo->lastInsertId();

            if ($tipo === 'multipla') {
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
                        ':id_pergunta' => $novoId,
                        ':texto'       => $texto,
                        ':letra'       => $letra,
                        ':correta'     => $isCorreta
                    ]);
                }
            }

            $pdo->commit();
            header("Location: criar_pergunta.php?sucesso=1&id=" . $novoId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao cadastrar pergunta: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Nova Pergunta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <a href="index.html" class="btn-voltar">← Voltar para o Menu Principal</a>

        <h1>Cadastrar Pergunta</h1>

        <?php if (!empty($msg)) { ?>
            <div class="alerta sucesso">
                <?php echo $msg; ?>
            </div>
        <?php } ?>

        <?php if (!empty($erro)) { ?>
            <div class="alerta erro"><?php echo $erro; ?></div>
        <?php } ?>

        <form action="criar_pergunta.php" method="POST">
            <label for="tipo">Tipo de Pergunta:</label>
            <select name="tipo" id="tipo" onchange="alternarCampos(this.value)" required>
                <option value="multipla">Múltipla Escolha</option>
                <option value="dissertativa">Dissertativa</option>
            </select>

            <br><br>

            <label for="pergunta">Pergunta / Enunciado:</label>
            <textarea name="pergunta" id="pergunta" rows="4" class="textarea-full" required placeholder="Digite a sua pergunta aqui..."></textarea>

            <br><br>

            <div id="bloco-multipla">
                <h3>Alternativas</h3>
                
                <label for="a">[A]:</label>
                <input type="text" name="a" id="a" required>
                <br><br>

                <label for="b">[B]:</label>
                <input type="text" name="b" id="b" required>
                <br><br>

                <label for="c">[C]:</label>
                <input type="text" name="c" id="c" required>
                <br><br>

                <label for="d">[D]:</label>
                <input type="text" name="d" id="d" required>
                <br><br>

                <label for="correta">Qual é a alternativa correta?</label>
                <select name="correta" id="correta">
                    <option value="A">Alternativa A</option>
                    <option value="B">Alternativa B</option>
                    <option value="C">Alternativa C</option>
                    <option value="D">Alternativa D</option>
                </select>
            </div>

            <br><br>
            <input type="submit" value="Salvar Pergunta">
        </form>
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