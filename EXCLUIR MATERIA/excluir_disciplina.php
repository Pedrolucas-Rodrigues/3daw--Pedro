<?php
$sigla = "";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sigla = trim($_POST["sigla"]); 
    $msg = "";
    
  
    if (!file_exists("disciplinas.txt")) {
        $msg = "Erro: O arquivo de disciplinas não foi encontrado.";
    } else {
        $arqDisc = fopen("disciplinas.txt", "r") or die("Erro ao abrir arquivo");
        $arqDiscNovo = fopen("disciplinas_temp.txt", "w") or die("Erro ao abrir arquivo novo");
        
   
        if (!feof($arqDisc)) {
            $linhaCabecalho = fgets($arqDisc);
            fwrite($arqDiscNovo, $linhaCabecalho);
        }
        
        
        while (!feof($arqDisc)) {
            $linha = fgets($arqDisc);
            
            
            if (trim($linha) == "") {
                continue;
            }
            
            $colunaDados = explode(";", $linha);
            
           
            $siglaArquivo = trim($colunaDados[0]);
            

            if ($siglaArquivo != $sigla) {
                fwrite($arqDiscNovo, $linha);
            }
        }
        
        fclose($arqDisc);
        fclose($arqDiscNovo);
        
        unlink("disciplinas.txt"); 
        rename("disciplinas_temp.txt", "disciplinas.txt"); 
        
        $msg = "Matéria excluída com sucesso!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Excluir Disciplina</title>
</head>
<body>
<h1>Excluir Disciplina</h1>
<br>

<form method="POST" action="">
    <label>Digite a Sigla da Matéria que deseja EXCLUIR:</label><br>
    <input type="text" name="sigla" required>
    <input type="submit" value="Excluir Matéria">
</form>

<br>
<ul>
    <li><a href="excluir_disciplina.php">Atualizar Página</a></li>
</ul>

<p><strong><?php echo $msg; ?></strong></p>
<br>
</body>
</html>
