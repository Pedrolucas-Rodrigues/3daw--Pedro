<?php
$siglaBusca = "";
$novoNome = "";
$novaCarga = "";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['buscar'])) {
        $siglaBusca = trim($_POST["sigla_busca"]);
        
        if (!file_exists("disciplinas.txt")) {
            $msg = "Erro: O arquivo de disciplinas não foi encontrado.";
        } else {
            $arqDisc = fopen("disciplinas.txt", "r") or die("Erro ao abrir arquivo");
            
            // Pula o cabeçalho
            $cabecalho = fgets($arqDisc);
            $encontrado = false;
            
            while (!feof($arqDisc)) {
                $linha = fgets($arqDisc);
                if (trim($linha) == "") continue;
                
                $colunaDados = explode(";", trim($linha));
                
                if ($colunaDados[0] == $siglaBusca) {
                    $encontrado = true;
                
                    $nomeAtual = isset($colunaDados[1]) ? $colunaDados[1] : "";
                    $cargaAtual = isset($colunaDados[2]) ? $colunaDados[2] : "";
                    break;
                }
            }
            fclose($arqDisc);
            
            if (!$encontrado) {
                $msg = "Matéria não encontrada!";
                $siglaBusca = ""; 
            }
        }
    }
    
    
    if (isset($_POST['salvar_alteracao'])) {
        $siglaAntiga = trim($_POST["sigla_antiga"]);
        $novoNome = trim($_POST["novo_nome"]);
        $novaCarga = trim($_POST["nova_carga"]);
        
        $arqDisc = fopen("disciplinas.txt", "r") or die("Erro ao abrir arquivo");
        $arqDiscNovo = fopen("disciplinas_temp.txt", "w") or die("Erro ao abrir arquivo novo");
        
        
        if (!feof($arqDisc)) {
            fwrite($arqDiscNovo, fgets($arqDisc));
        }
        
        while (!feof($arqDisc)) {
            $linha = fgets($arqDisc);
            if (trim($linha) == "") continue;
            
            $colunaDados = explode(";", trim($linha));
           
            if ($colunaDados[0] == $siglaAntiga) {
      
                $novaLinha = $siglaAntiga . ";" . $novoNome . ";" . $novaCarga . "\n";
                fwrite($arqDiscNovo, $novaLinha);
            } else {
                
                fwrite($arqDiscNovo, $linha);
            }
        }
        
        fclose($arqDisc);
        fclose($arqDiscNovo);
        
        unlink("disciplinas.txt");
        rename("disciplinas_temp.txt", "disciplinas.txt");
        
        $msg = "Matéria alterada com sucesso!";
        $siglaBusca = ""; // Reseta o fluxo
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Alterar Disciplina</title>
</head>
<body>
<h1>Alterar Disciplina</h1>
<br>

<?php if (empty($siglaBusca) || isset($msg) && $msg == "Matéria alterada com sucesso!"): ?>
    <form method="POST" action="">
        <label>Digite a Sigla da Matéria que deseja ALTERAR:</label><br>
        <input type="text" name="sigla_busca" required>
        <input type="submit" name="buscar" value="Buscar Matéria">
    </form>
<?php endif; ?>

<?php if (!empty($siglaBusca) && !isset($nomeAtual) === false && isset($nomeAtual)): ?>
    <hr>
    <h3>Editando a Matéria: <?php echo htmlspecialchars($siglaBusca); ?></h3>
    <form method="POST" action="">
        <input type="hidden" name="sigla_antiga" value="<?php echo htmlspecialchars($siglaBusca); ?>">
        
        <label>Novo Nome da Matéria:</label><br>
        <input type="text" name="novo_nome" value="<?php echo htmlspecialchars($nomeAtual); ?>" required><br><br>
        
        <label>Nova Carga Horária:</label><br>
        <input type="text" name="nova_carga" value="<?php echo htmlspecialchars($cargaAtual); ?>" required><br><br>
        
        <input type="submit" name="salvar_alteracao" value="Salvar Alterações">
    </form>
<?php endif; ?>

<br>
<p><strong><?php echo $msg; ?></strong></p>

<br>
<ul>
    <li><a href="alterar_disciplina.php">Voltar / Recomeçar</a></li>
</ul>

</body>
</html>
