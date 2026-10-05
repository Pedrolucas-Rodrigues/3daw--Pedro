<?php
$arquivoPergunta     = "perguntas.txt";
$arquivoAlternativas = "alternativas.txt";

function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}

$idExcluir = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idExcluir > 0 && file_exists($arquivoPergunta)) {

    // ------------------------------------------------------------------------
    // 1. CARREGA E LE REMOVENDO A PERGUNTA SELECIONADA
    // ------------------------------------------------------------------------
    $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $perguntasManter = [];

    foreach ($linhasP as $linha) {
        $linhaLimpa = limparTexto($linha);
        if (empty($linhaLimpa)) continue;

        $dados = explode(";", $linhaLimpa);
        $idAtual = intval(limparTexto($dados[0] ?? 0));

        // Se NÃO for a pergunta que queremos excluir, mantém na lista
        if ($idAtual !== $idExcluir) {
            $perguntasManter[] = [
                'idAntigo' => $idAtual,
                'tipo'     => limparTexto($dados[1] ?? ''),
                'pergunta' => limparTexto($dados[2] ?? '')
            ];
        }
    }

    // Ordena pelo ID antigo para garantir a sequência correta
    usort($perguntasManter, function($a, $b) {
        return $a['idAntigo'] <=> $b['idAntigo'];
    });

    // ------------------------------------------------------------------------
    // 2. REINDEXA OS IDS E MAPEIA O DE-PARA (DE ID ANTIGO PARA NOVO ID)
    // ------------------------------------------------------------------------
    $mapaIds = []; // Exemplo: [1 => 1, 3 => 2, 4 => 3]
    $novoConteudoPerguntas = "";
    $novoIdCounter = 1;

    foreach ($perguntasManter as $p) {
        $idAntigo = $p['idAntigo'];
        $mapaIds[$idAntigo] = $novoIdCounter; // Registra o de-para

        $novoConteudoPerguntas .= $novoIdCounter . ";" . $p['tipo'] . ";" . $p['pergunta'] . "\n";
        $novoIdCounter++;
    }

    // Sobrescreve o perguntas.txt com a nova sequência de IDs
    file_put_contents($arquivoPergunta, $novoConteudoPerguntas);

    // ------------------------------------------------------------------------
    // 3. ATUALIZA O ARQUIVO DE ALTERNATIVAS COM OS NOVOS IDS
    // ------------------------------------------------------------------------
    if (file_exists($arquivoAlternativas)) {
        $linhasA = file($arquivoAlternativas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $novoConteudoAlternativas = "";

        foreach ($linhasA as $linha) {
            $linhaLimpa = limparTexto($linha);
            if (empty($linhaLimpa)) continue;

            $dadosA = explode(";", $linhaLimpa);
            $idPerguntaAlt = intval(limparTexto($dadosA[0] ?? 0));

            // Se o ID da alternativa existe no mapa (ou seja, a pergunta não foi excluída)
            if (array_key_exists($idPerguntaAlt, $mapaIds)) {
                $novoIdPergunta = $mapaIds[$idPerguntaAlt]; // Pega o novo ID reindexado
                $textoAlt  = limparTexto($dadosA[1] ?? '');
                $letraAlt  = limparTexto($dadosA[2] ?? '');
                $corretaAlt = limparTexto($dadosA[3] ?? '0');

                $novoConteudoAlternativas .= $novoIdPergunta . ";" . $textoAlt . ";" . $letraAlt . ";" . $corretaAlt . "\n";
            }
        }

        // Sobrescreve o alternativas.txt com os IDs corrigidos
        file_put_contents($arquivoAlternativas, $novoConteudoAlternativas);
    }
}

// Redireciona de volta para o visualizador
$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
header("Location: responder.php?p=" . $p . "&excluido=1");
exit;