<?php
$arquivoPergunta     = "perguntas.txt";
$arquivoAlternativas = "alternativas.txt";

$msg = "";
$erro = "";


$idEditar = $_GET['id'] ?? $_POST['id_pergunta'] ?? null;


function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idEditar) {
    $novoTipo     = $_POST['tipo'] ?? 'multipla';
    $novaPergunta = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['pergunta'] ?? ''));

    if (empty($novaPergunta)) {
        $erro = "O enunciado da pergunta não pode ficar em branco.";
    } else {
        
        if (file_exists($arquivoPergunta)) {
            $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES);
            $novasLinhasP = [];

            foreach ($linhasP as $linha) {
                $linhaLimpa = limparTexto($linha);
                if ($linhaLimpa === '') continue;

                $dados = explode(";", $linhaLimpa);
                $idLinha = limparTexto($dados[0] ?? '');

                if ($idLinha == $idEditar) {
                    $novasLinhasP[] = $idEditar . ";" . $novoTipo . ";" . $novaPergunta;
                } else {
                    $novasLinhasP[] = $linhaLimpa;
                }
            }
            file_put_contents($arquivoPergunta, implode("\n", $novasLinhasP) . "\n");
        }

       
        if (file_exists($arquivoAlternativas)) {
            $linhasA = file($arquivoAlternativas, FILE_IGNORE_NEW_LINES);
            $novasLinhasA = [];

            foreach ($linhasA as $linha) {
                $linhaLimpa = limparTexto($linha);
                if ($linhaLimpa === '') continue;

                $dadosA = explode(";", $linhaLimpa);
                $idLinhaA = limparTexto($dadosA[0] ?? '');

                if ($idLinhaA != $idEditar) {
                    $novasLinhasA[] = $linhaLimpa;
                }
            }

            if ($novoTipo === 'multipla') {
                $altA    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['a'] ?? ''));
                $altB    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['b'] ?? ''));
                $altC    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['c'] ?? ''));
                $altD    = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['d'] ?? ''));
                $correta = $_POST['correta'] ?? 'A';

                $opcoes = ['A' => $altA, 'B' => $altB, 'C' => $altC, 'D' => $altD];
                foreach ($opcoes as $letra => $texto) {
                    $isCorreta = ($letra === $correta) ? 1 : 0;
                    $novasLinhasA[] = $idEditar . ";" . $texto . ";" . $letra . ";" . $isCorreta;
                }
            }

            file_put_contents($arquivoAlternativas, implode("\n", $novasLinhasA) . "\n");
        }

        $msg = "Pergunta #{$idEditar} alterada com sucesso!";
    }
}


$dadosPergunta = null;
$opcoesAtuais  = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
$corretaAtual  = 'A';

if ($idEditar) {
    if (file_exists($arquivoPergunta)) {
        $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhasP as $linha) {
            $linhaLimpa = limparTexto($linha);
            $dados = explode(";", $linhaLimpa);
            $idLinha = limparTexto($dados[0] ?? '');

            if ($idLinha == $idEditar) {
                $dadosPergunta = [
                    'id'       => $idLinha,
                    'tipo'     => limparTexto($dados[1] ?? 'multipla'),
                    'pergunta' => limparTexto($dados[2] ?? '')
                ];
                break;
            }
        }
    }

    if ($dadosPergunta && file_exists($arquivoAlternativas)) {
        $linhasA = file($arquivoAlternativas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhasA as $linha) {
            $linhaLimpa = limparTexto($linha);
            $dadosA = explode(";", $linhaLimpa);
            $idLinhaA = limparTexto($dadosA[0] ?? '');

            if ($idLinhaA == $idEditar && count($dadosA) >= 4) {
                $letra = limparTexto($dadosA[2]);
                $opcoesAtuais[$letra] = limparTexto($dadosA[1]);
                if (limparTexto($dadosA[3]) == '1') {
                    $corretaAtual = $letra;
                }
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

        <?php 
      
        if (!$idEditar) { 
        ?>
            <h1>Selecione uma Pergunta para Editar</h1>

            <?php
            $perguntas = [];
            if (file_exists($arquivoPergunta)) {
                $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                foreach ($linhasP as $linha) {
                    $linhaLimpa = limparTexto($linha);
                    if ($linhaLimpa === '') continue;

                    $dados = explode(";", $linhaLimpa);
                    
                    if (count($dados) >= 3) {
                        $id = limparTexto($dados[0]);
                        
                       
                        if (is_numeric($id)) {
                            $perguntas[] = [
                                'id'       => $id,
                                'tipo'     => limparTexto($dados[1]),
                                'pergunta' => limparTexto($dados[2])
                            ];
                        }
                    }
                }
            }

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
            <?php 
            } else { 
            ?>
                <p class="sem-dados">Nenhuma pergunta encontrada no arquivo <code>perguntas.txt</code>. <a href="criar_pergunta.php">Cadastrar Pergunta</a></p>
            <?php } ?>

        <?php 

        } else { 
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
                <textarea name="pergunta" id="pergunta" rows="4" style="width: 100%;" required><?php echo htmlspecialchars($dadosPergunta['pergunta']); ?></textarea>

                <br><br>

                <div id="bloco-multipla" style="display: <?php echo $dadosPergunta['tipo'] === 'multipla' ? 'block' : 'none'; ?>;">
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