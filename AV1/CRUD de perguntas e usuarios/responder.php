<?php
$arquivoPergunta     = "perguntas.txt";
$arquivoAlternativas = "alternativas.txt";

function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}

$perguntas = [];

if (file_exists($arquivoPergunta)) {
    $linhasP = file($arquivoPergunta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhasP as $linha) {
        $linhaLimpa = limparTexto($linha);
        if ($linhaLimpa === '') continue;

        $dados = explode(";", $linhaLimpa);
        if (count($dados) >= 3) {
            $id = intval(limparTexto($dados[0]));
            if ($id > 0) {
                $perguntas[] = [
                    'id'       => $id,
                    'tipo'     => limparTexto($dados[1]),
                    'pergunta' => limparTexto($dados[2])
                ];
            }
        }
    }
}

usort($perguntas, function($a, $b) {
    return $a['id'] <=> $b['id'];
});

$totalPerguntas = count($perguntas);
$indiceAtual = isset($_GET['p']) ? intval($_GET['p']) : 0;

if ($indiceAtual < 0) {
    $indiceAtual = 0;
} elseif ($indiceAtual >= $totalPerguntas && $totalPerguntas > 0) {
    $indiceAtual = $totalPerguntas - 1;
}

$perguntaAtual = $totalPerguntas > 0 ? $perguntas[$indiceAtual] : null;

$alternativasAtuais = [];
if ($perguntaAtual && $perguntaAtual['tipo'] === 'multipla' && file_exists($arquivoAlternativas)) {
    $linhasA = file($arquivoAlternativas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhasA as $linha) {
        $linhaLimpa = limparTexto($linha);
        if ($linhaLimpa === '') continue;

        $dadosA = explode(";", $linhaLimpa);
        if (count($dadosA) >= 4 && intval(limparTexto($dadosA[0])) === $perguntaAtual['id']) {
            $alternativasAtuais[] = [
                'texto'     => limparTexto($dadosA[1]),
                'letra'     => limparTexto($dadosA[2]),
                'isCorreta' => limparTexto($dadosA[3]) == '1'
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Perguntas</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cartao-pergunta {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            background-color: #f9f9f9;
            margin-top: 15px;
            position: relative;
        }
        .navegacao {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }
        .btn-seta {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-seta.desabilitado {
            background-color: #cccccc;
            pointer-events: none;
            cursor: default;
        }
        .btn-excluir {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            float: right;
        }
        .btn-excluir:hover {
            background-color: #bd2130;
        }
        .contador-paginas {
            font-weight: bold;
            color: #555;
        }
        .opcoes-lista {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }
        .opcoes-lista li {
            padding: 8px 12px;
            margin-bottom: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
        }
        .alerta-sucesso {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.html" class="btn-voltar">← Voltar para o Menu Principal</a>

        <h1>Visualizador de Perguntas</h1>

        <?php if (isset($_GET['excluido'])) { ?>
            <div class="alerta-sucesso">
                ✅ Pergunta excluída com sucesso e a sequência dos IDs foi reorganizada!
            </div>
        <?php } ?>

        <?php if ($totalPerguntas === 0) { ?>
            <p>Nenhuma pergunta encontrada no sistema. <a href="criar_pergunta.php">Cadastre uma agora</a>.</p>
        <?php } else { ?>

            <div class="cartao-pergunta">
                <!-- Botão de Excluir -->
                <a href="excluir_pergunta.php?id=<?php echo $perguntaAtual['id']; ?>&p=<?php echo $indiceAtual; ?>" 
                   class="btn-excluir" 
                   onclick="return confirm('Tem certeza que deseja excluir esta pergunta? Todas as perguntas posteriores terão seus IDs ajustados automaticamente.');">
                    🗑️️ Excluir Pergunta
                </a>

                <small>Pergunta #<?php echo $perguntaAtual['id']; ?> (<?php echo $perguntaAtual['tipo'] === 'multipla' ? 'Múltipla Escolha' : 'Dissertativa'; ?>)</small>
                <h2><?php echo htmlspecialchars($perguntaAtual['pergunta']); ?></h2>

                <?php if ($perguntaAtual['tipo'] === 'multipla') { ?>
                    <ul class="opcoes-lista">
                        <?php foreach ($alternativasAtuais as $alt) { ?>
                            <li>
                                <strong>[<?php echo $alt['letra']; ?>]</strong> 
                                <?php echo htmlspecialchars($alt['texto']); ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <textarea rows="3" style="width: 100%; margin-top: 10px;" placeholder="Espaço para resposta dissertativa..."></textarea>
                <?php } ?>
            </div>

         
            <div class="navegacao">
                <?php if ($indiceAtual > 0) { ?>
                    <a href="responder.php?p=<?php echo $indiceAtual - 1; ?>" class="btn-seta">⬅ Anterior</a>
                <?php } else { ?>
                    <span class="btn-seta desabilitado">⬅ Anterior</span>
                <?php } ?>

                <span class="contador-paginas">
                    Pergunta <?php echo $indiceAtual + 1; ?> de <?php echo $totalPerguntas; ?>
                </span>

                <?php if ($indiceAtual < $totalPerguntas - 1) { ?>
                    <a href="responder.php?p=<?php echo $indiceAtual + 1; ?>" class="btn-seta">Próxima ➡</a>
                <?php } else { ?>
                    <span class="btn-seta desabilitado">Próxima ➡</span>
                <?php } ?>
            </div>

        <?php } ?>
    </div>

</body>
</html>