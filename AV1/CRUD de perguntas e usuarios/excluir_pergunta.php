<?php
$arquivoPergunta     = "perguntas.txt";
$arquivoAlternativas = "alternativas.txt";

function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}

$idExcluir = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idExcluir > 0 && file_exists($arquivoPergunta)) {


    $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $perguntasManter = [];

    foreach ($linhasP as $linha) {
        $linhaLimpa = limparTexto($linha);
        if (empty($linhaLimpa)) continue;

        $dados = explode(";", $linhaLimpa);
        $idAtual = intval(limparTexto($dados[0] ?? 0));


        if ($idAtual !== $idExcluir) {
            $perguntasManter[] = [
                'idAntigo' => $idAtual,
                'tipo'     => limparTexto($dados[1] ?? ''),
                'pergunta' => limparTexto($dados[2] ?? '')
            ];
        }
    }


    usort($perguntasManter, function($a, $b) {
        return $a['idAntigo'] <=> $b['idAntigo'];
    });


    $mapaIds = []; 
    $novoConteudoPerguntas = "";
    $novoIdCounter = 1;

    foreach ($perguntasManter as $p) {
        $idAntigo = $p['idAntigo'];
        $mapaIds[$idAntigo] = $novoIdCounter; 

        $novoConteudoPerguntas .= $novoIdCounter . ";" . $p['tipo'] . ";" . $p['pergunta'] . "\n";
        $novoIdCounter++;
    }

  
    file_put_contents($arquivoPergunta, $novoConteudoPerguntas);

    if (file_exists($arquivoAlternativas)) {
        $linhasA = file($arquivoAlternativas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $novoConteudoAlternativas = "";

        foreach ($linhasA as $linha) {
            $linhaLimpa = limparTexto($linha);
            if (empty($linhaLimpa)) continue;

            $dadosA = explode(";", $linhaLimpa);
            $idPerguntaAlt = intval(limparTexto($dadosA[0] ?? 0));

            
            if (array_key_exists($idPerguntaAlt, $mapaIds)) {
                $novoIdPergunta = $mapaIds[$idPerguntaAlt];
                $textoAlt  = limparTexto($dadosA[1] ?? '');
                $letraAlt  = limparTexto($dadosA[2] ?? '');
                $corretaAlt = limparTexto($dadosA[3] ?? '0');

                $novoConteudoAlternativas .= $novoIdPergunta . ";" . $textoAlt . ";" . $letraAlt . ";" . $corretaAlt . "\n";
            }
        }

        file_put_contents($arquivoAlternativas, $novoConteudoAlternativas);
    }
}

$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
header("Location: responder.php?p=" . $p . "&excluido=1");
exit;