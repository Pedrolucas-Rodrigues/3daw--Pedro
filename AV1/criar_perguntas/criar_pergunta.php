<?php
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pergunta = $_POST["pergunta"] ?? '';
    $altA     = $_POST["a"] ?? '';
    $altB     = $_POST["b"] ?? '';
    $altC     = $_POST["c"] ?? '';
    $altD     = $_POST["d"] ?? '';
    $correta  = $_POST["correta"] ?? ''; 

    // Limpa quebras de linha e caracteres conflitantes das entradas
    $pergunta = str_replace([";", "\r", "\n"], ["", " ", " "], $pergunta);
    $altA     = str_replace([";", "\r", "\n"], ["", " ", " "], $altA);
    $altB     = str_replace([";", "\r", "\n"], ["", " ", " "], $altB);
    $altC     = str_replace([";", "\r", "\n"], ["", " ", " "], $altC);
    $altD     = str_replace([";", "\r", "\n"], ["", " ", " "], $altD);

    $arquivoPergunta     = "perguntas.txt";
    $arquivoAlternativas = "alternativas.txt"; // Corrigido o nome da variável

    // 1. Cria cabeçalho de perguntas se não existir
    if (!file_exists($arquivoPergunta)) {
        $arqP = fopen($arquivoPergunta, "w") or die("Erro ao criar arquivo de perguntas");
        fwrite($arqP, "id;pergunta\n");
        fclose($arqP); // Corrigido $arqPergunta -> $arqP
    }

    // Define o ID com base no número de linhas
    $linhasP = file($arquivoPergunta);
    $novoId  = count($linhasP);

    // Grava a nova pergunta
    $linhaP = $novoId . ";" . $pergunta . "\n";
    $arqP   = fopen($arquivoPergunta, "a") or die("Erro ao abrir arquivo de perguntas");
    fwrite($arqP, $linhaP);
    fclose($arqP);

    // 2. Cria cabeçalho de alternativas se não existir
    if (!file_exists($arquivoAlternativas)) {
        $arqA = fopen($arquivoAlternativas, "w") or die("Erro ao criar arquivo de alternativas");
        fwrite($arqA, "id_pergunta;texto_alternativa;letra;correta\n");
        fclose($arqA);
    }

    $opcoes = [
        'A' => $altA,
        'B' => $altB,
        'C' => $altC,
        'D' => $altD
    ];

    // Grava as alternativas
    $arqA = fopen($arquivoAlternativas, "a") or die("Erro ao abrir arquivo de alternativas");
    
    foreach ($opcoes as $letra => $texto) {
        $isCorreta = ($letra === $correta) ? 1 : 0;
        $linhaA    = $novoId . ";" . $texto . ";" . $letra . ";" . $isCorreta . "\n";
        fwrite($arqA, $linhaA);
    }
    
    fclose($arqA);

    $msg = "Pergunta e alternativas cadastradas com sucesso nos dois arquivos!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Perguntas</title>
</head>
<body>

    <div class="container">
        <h1>Cadastro</h1>
        <p>Insira a pergunta e suas respostas:</p>

        <form action="" method="POST">
            <label for="pergunta">Pergunta:</label>
            <input type="text" name="pergunta" id="pergunta" required>

            <label for="a">[A]:</label>
            <input 
            type="text" 
            name="a" 
            id="a" 
            required
            >

            <label for="b">[B]:</label>
            <input 
             
             type="text"
             name="b" 
             id="b" required
             >

            <label for="c">[C]:</label>
            <input 
            
            type="text" 
            name="c" 
            id="c" required
            >

            <label for="d">[D]:</label>
            <input 
            type="text" 
            name="d" 
            id="d" 
            required
            >

            <label for="correta">Qual é a alternativa correta?</label>
            <select name="correta" id="correta" required>

                <option value="A">Alternativa A</option>
                <option value="B">Alternativa B</option>
                <option value="C">Alternativa C</option>
                <option value="D">Alternativa D</option>
            </select>
            
            <input type="submit" value="Salvar Pergunta e Respostas">
        </form>

        <?php if (!empty($msg)) { ?>
            <p class="mensagem"><?php echo $msg; ?></p>
        <?php } ?>
    </div>

</body>
</html>