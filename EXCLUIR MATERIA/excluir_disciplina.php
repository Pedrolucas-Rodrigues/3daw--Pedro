<?php
$sigla = "";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sigla = $_POST["sigla"]; 
    $msg = "";
    
    
    $arqDisc = fopen("disciplinas.txt", "r") or die("erro ao abrir arquivo");
    
    
    $arqDiscNovo = fopen("disciplinas_temp.txt", "w") or die("erro ao abrir arquivo novo");
    
    
    $linha = fgets($arqDisc);
    fwrite($arqDiscNovo, $linha);

    
    while (!feof($arqDisc)) {
        $linha = fgets($arqDisc);
        
        
        if (trim($linha) == "") {
            continue;
        }
        
        $colunaDados = explode(";", $linha);
        
        
        if ($colunaDados[0] != $sigla) {
            fwrite($arqDiscNovo, $linha);
        }
        
    }
    
        fclose($arqDisc);
    fclose($arqDiscNovo);
    
    
    unlink("disciplinas.txt"); // Deleta o arquivo antigo
    rename("disciplinas_temp.txt", "disciplinas.txt"); // Renomeia o novo
    
    $msg = "Matéria excluída com sucesso!!!";
}
?>
<!DOCTYPE html>
<html>
<head>
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
    <li><a href="excluir_disciplina.php">Excluir Disciplina</a></li>
</ul>

<p><?php echo $msg; ?></p>
<br>
</body>
</html>