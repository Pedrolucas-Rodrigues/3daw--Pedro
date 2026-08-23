<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST["nome"];
    $matricula = $_POST["matricula"];
    $cpf = $_POST["cpf"];
    $email = $_POST["email"];

    $msg = "";

    if (!file_exists("alunos.txt")) {

        $arqAluno = fopen("alunos.txt", "w") or die("erro ao criar arquivo");

        $linha = "nome;matricula;cpf;email\n";

        fwrite($arqAluno, $linha);

        fclose($arqAluno);
    }

    $arqAluno = fopen("alunos.txt", "a") or die("erro ao criar arquivo");

    $linha = $nome . ";" . $matricula . ";" . $cpf . ";" . $email . "\n";

    fwrite($arqAluno, $linha);

    fclose($arqAluno);

    $msg = "Aluna(o) cadastrado com sucesso!";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Aluno</title>

    <link rel="stylesheet" href="estilo.css">

</head>

<body>

    <div class="container">

        <h1>Cadastro de Aluno</h1>

        <p class="subtitulo">
            Preencha os dados do aluno
        </p>

        <form action="incluir_aluno.php" method="POST">

            <label for="nome">Nome:</label>

            <input
                type="text"
                name="nome"
                id="nome"
                required
            >

            <label for="matricula">Matrícula:</label>

            <input
                type="text"
                name="matricula"
                id="matricula"
                required
            >

            <label for="cpf">CPF:</label>

            <input
                type="text"
                name="cpf"
                id="cpf"
                required
            >

            <label for="email">E-MAIL:</label>

            <input
                type="text"
                name="email"
                id="email"
                required
            >

            <input
                type="submit"
                value="Cadastrar Aluno"
            >

        </form>

        <?php if (!empty($msg)) { ?>

            <p class="mensagem">
                <?php echo $msg; ?>
            </p>

        <?php } ?>

    </div>

</body>

</html>