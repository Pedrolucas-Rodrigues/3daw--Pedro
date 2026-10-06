<?php
$arquivoPergunta     = "perguntas.txt";
$arquivoAlternativas = "alternativas.txt";

$msg = "";
$erro = "";

// Limpa caracteres invisíveis de codificação (BOM UTF-8)
function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}

// Obtém o próximo ID incremental
function obterProximoId($arquivo) {
    if (!file_exists($arquivo)) {
        return 1;
    }

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $maiorId = 0;

    foreach ($linhas as $linha) {
        $linhaLimpa = limparTexto($linha);
        if (empty($linhaLimpa)) continue;

        $dados = explode(";", $linhaLimpa);
        $idAtual = intval(limparTexto($dados[0] ?? 0));

        if ($idAtual > $maiorId) {
            $maiorId = $idAtual;
        }
    }

    return $maiorId + 1;
}

if (isset($_GET['sucesso']) && isset($_GET['id'])) {
    $idCadastrado = intval($_GET['id']);
    $msg = "Pergunta #{$idCadastrado} cadastrada com sucesso!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo     = $_POST['tipo'] ?? 'multipla';
    $pergunta = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['pergunta'] ?? ''));

    if (empty($pergunta)) {
        $erro = "Por favor, digite o enunciado da pergunta.";
    } else {
        $novoId = obterProximoId($arquivoPergunta);

        // Salva em perguntas.txt
        $linhaPergunta = $novoId . ";" . $tipo . ";" . $pergunta . "\n";
        file_put_contents($arquivoPergunta, $linhaPergunta, FILE_APPEND);

        if ($tipo === 'multipla') {
            $altA    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['a'] ?? ''));
            $altB    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['b'] ?? ''));
            $altC    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['c'] ?? ''));
            $altD    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['d'] ?? ''));
            $correta = $_POST['correta'] ?? 'A';

            $opcoes = ['A' => $altA, 'B' => $altB, 'C' => $altC, 'D' => $altD];
            $linhasAlternativas = "";

            foreach ($opcoes as $letra => $texto) {
                $isCorreta = ($letra === $correta) ? 1 : 0;
                $linhasAlternativas .= $novoId . ";" . $texto . ";" . $letra . ";" . $isCorreta . "\n";
            }

            file_put_contents($arquivoAlternativas, $linhasAlternativas, FILE_APPEND);
        }
        header("Location: criar_pergunta.php?sucesso=1&id=" . $novoId);
        exit;
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
                <br><br>
            </div>
        <?php } ?>

        <?php if (!empty($erro)) { ?>
            <div class="alerta erro"> <?php echo $erro; ?></div>
        <?php } ?>

        <form action="criar_pergunta.php" method="POST">
            <label for="tipo">Tipo de Pergunta:</label>
            <select name="tipo" id="tipo" onchange="alternarCampos(this.value)" required>
                <option value="multipla">Múltipla Escolha</option>
                <option value="dissertativa">Dissertativa</option>
            </select>

            <br><br>

            <label for="pergunta">Pergunta / Enunciado:</label>
            <textarea name="pergunta" id="pergunta" rows="4" style="width: 100%;" required placeholder="Digite a sua pergunta aqui..."></textarea>

            <br><br>

            <!-- BLOCO DE ALTERNATIVAS -->
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
                blocoMultipla.style.display = 'none';
                inputs.forEach(i => i.removeAttribute('required'));
            } else {
                blocoMultipla.style.display = 'block';
                inputs.forEach(i => i.setAttribute('required', 'true'));
            }
        }
    </script>

</body>
</html>