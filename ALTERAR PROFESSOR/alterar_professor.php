<?php
$matriculaBusca = "";
$nomeAtual = "";
$cursoAtual = "";
$msg = "";
$encontrado = false;

$nomeArquivo = "professor.txt";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['buscar'])) {
        $matriculaBusca = trim($_POST["matricula_busca"]);
        
        if (!file_exists($nomeArquivo)) {
            $msg = "Erro: O arquivo '{$nomeArquivo}' não foi encontrado.";
        } else {
            $arq = fopen($nomeArquivo, "r") or die("Erro ao abrir arquivo");
            
            // Pula o cabeçalho (nome;matricula;curso)
            $cabecalho = fgets($arq);
            
            while (!feof($arq)) {
                $linha = fgets($arq);
                if (trim($linha) == "") continue;
                
                
                $colunaDados = explode(";", trim($linha));
                
                
                $nome = isset($colunaDados[0]) ? $colunaDados[0] : "";
                $matricula = isset($colunaDados[1]) ? $colunaDados[1] : "";
                $curso = isset($colunaDados[2]) ? $colunaDados[2] : "";
                
                
                if ($matricula == $matriculaBusca) {
                    $encontrado = true;
                    $nomeAtual = $nome;
                    $cursoAtual = $curso;
                    break;
                }
            }
            fclose($arq);
            
            if (!$encontrado) {
                $msg = "Registro com a matrícula '$matriculaBusca' não foi encontrado!";
                $matriculaBusca = ""; 
            }
        }
    }
    
    
    if (isset($_POST['salvar_alteracao'])) {
        $matriculaAntiga = trim($_POST["matricula_antiga"]);
        $novoNome = trim($_POST["novo_nome"]);
        $novoCurso = trim($_POST["novo_curso"]);
        
        if (!file_exists($nomeArquivo)) {
            $msg = "Erro: O arquivo '{$nomeArquivo}' não foi encontrado.";
        } else {
            $arq = fopen($nomeArquivo, "r") or die("Erro ao abrir arquivo");
            $arqNovo = fopen("professor_temp.txt", "w") or die("Erro ao abrir arquivo temporário");
            
            
            if (!feof($arq)) {
                fwrite($arqNovo, fgets($arq));
            }
            
            while (!feof($arq)) {
                $linha = fgets($arq);
                if (trim($linha) == "") continue;
                
                $colunaDados = explode(";", trim($linha));
                $matricula = isset($colunaDados[1]) ? trim($colunaDados[1]) : "";
                
               
                if ($matricula == $matriculaAntiga) {
                    
                    $novaLinha = $novoNome . ";" . $matriculaAntiga . ";" . $novoCurso . "\n";
                    fwrite($arqNovo, $novaLinha);
                } else {
                    
                    fwrite($arqNovo, $linha);
                }
            }
            
            fclose($arq);
            fclose($arqNovo);
            
            
            unlink($nomeArquivo); 
            rename("professor_temp.txt", $nomeArquivo); 
            
            $msg = "Dados alterados com sucesso!";
            $matriculaBusca = ""; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Professor</title>
</head>
<body>
<h1>Alterar Dados do Professor</h1>
<br>


<?php if (empty($matriculaBusca) || $msg == "Dados alterados com sucesso!"): ?>
    <form method="POST" action="">
        <label>Digite a <strong>Matrícula</strong> do professor que deseja alterar:</label><br>
        <input type="text" name="matricula_busca" required>
        <input type="submit" name="buscar" value="Buscar Professor">
    </form>
<?php endif; ?>


<?php if (!empty($matriculaBusca) && $encontrado): ?>
    <hr>
    <h3>Editando Professor de Matrícula: <?php echo htmlspecialchars($matriculaBusca); ?></h3>
    <form method="POST" action="">
       
        <input type="hidden" name="matricula_antiga" value="<?php echo htmlspecialchars($matriculaBusca); ?>">
        
        <label>Novo Nome:</label><br>
        <input type="text" name="novo_nome" value="<?php echo htmlspecialchars($nomeAtual); ?>" required><br><br>
        
        <label>Novo Curso:</label><br>
        <input type="text" name="novo_curso" value="<?php echo htmlspecialchars($cursoAtual); ?>" required><br><br>
        
        <input type="submit" name="salvar_alteracao" value="Salvar Alterações">
    </form>
<?php endif; ?>

<br>
<p><strong><?php echo $msg; ?></strong></p>

<br>
<ul>
    <li><a href="alterar_professor.php">Voltar / Recomeçar</a></li>
</ul>

</body>
</html>
