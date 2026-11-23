<?php
require_once 'conexao.php';

$totalPerguntas = (int) $pdo->query("SELECT COUNT(*) FROM perguntas")->fetchColumn();
$indiceAtual = isset($_GET['p']) ? intval($_GET['p']) : 0;

if ($indiceAtual < 0) {
    $indiceAtual = 0;
} elseif ($indiceAtual >= $totalPerguntas && $totalPerguntas > 0) {
    $indiceAtual = $totalPerguntas - 1;
}

$perguntaAtual = null;
$alternativasAtuais = [];

if ($totalPerguntas > 0) {
    $stmt = $pdo->prepare("SELECT * FROM perguntas ORDER BY id ASC LIMIT 1 OFFSET :offset");
    $stmt->bindValue(':offset', $indiceAtual, PDO::PARAM_INT);
    $stmt->execute();
    $perguntaAtual = $stmt->fetch();

    if ($perguntaAtual && $perguntaAtual['tipo'] === 'multipla') {
        $stmtAlt = $pdo->prepare("SELECT texto, letra, correta FROM alternativas WHERE id_pergunta = :id ORDER BY letra ASC");
        $stmtAlt->execute([':id' => $perguntaAtual['id']]);
        $alternativasAtuais = $stmtAlt->fetchAll();
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
</head>
<body>

    <div class="container">
        <a href="index.html" class="btn-voltar">← Voltar para o Menu Principal</a>

        <h1>Visualizador de Perguntas</h1>

        <?php if (isset($_GET['excluido'])) { ?>
            <div class="alerta-sucesso">
                ✅ Pergunta excluída com sucesso!
            </div>
        <?php } ?>

        <?php if ($totalPerguntas === 0) { ?>
            <p>Nenhuma pergunta encontrada no sistema. <a href="criar_pergunta.php">Cadastre uma agora</a>.</p>
        <?php } else { ?>

            <div class="cartao-pergunta">
                <a href="excluir_pergunta.php?id=<?php echo $perguntaAtual['id']; ?>&p=<?php echo $indiceAtual; ?>" 
                   class="btn-excluir" 
                   onclick="return confirm('Tem certeza que deseja excluir esta pergunta?');">
                    🗑 Excluir Pergunta
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
                    <textarea rows="3" class="textarea-resposta" placeholder="Espaço para resposta dissertativa..."></textarea>
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