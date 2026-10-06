<?php
$arquivoUsuarios = "usuarios.txt";

function limparTexto($texto) {
    return trim(str_replace("\xEF\xBB\xBF", "", $texto));
}


function obterProximoId($arquivo) {
    if (!file_exists($arquivo)) return 1;
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $maiorId = 0;
    foreach ($linhas as $linha) {
        $linhaLimpa = limparTexto($linha);
        if (empty($linhaLimpa)) continue;
        $dados = explode(";", $linhaLimpa);
        $idAtual = intval(limparTexto($dados[0] ?? 0));
        if ($idAtual > $maiorId) $maiorId = $idAtual;
    }
    return $maiorId + 1;
}

$msg = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nome      = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['nome'] ?? ''));
    $email     = str_replace([";", "\r", "\n"], ["", " ", " "], trim($_POST['email'] ?? ''));

    if (empty($nome) || empty($email)) {
        $erro = "Preencha o nome e o e-mail.";
    } else {
        if ($idUsuario > 0) {
            // EDITAR
            if (file_exists($arquivoUsuarios)) {
                $linhas = file($arquivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $novasLinhas = [];
                foreach ($linhas as $linha) {
                    $linhaLimpa = limparTexto($linha);
                    if (empty($linhaLimpa)) continue;
                    $dados = explode(";", $linhaLimpa);
                    if (intval(limparTexto($dados[0])) === $idUsuario) {
                        $novasLinhas[] = "{$idUsuario};{$nome};{$email}";
                    } else {
                        $novasLinhas[] = $linhaLimpa;
                    }
                }
                file_put_contents($arquivoUsuarios, implode("\n", $novasLinhas) . "\n");
                $msg = "Usuário #{$idUsuario} atualizado com sucesso!";
            }
        } else {
            
            $novoId = obterProximoId($arquivoUsuarios);
            $linhaNova = "{$novoId};{$nome};{$email}\n";
            file_put_contents($arquivoUsuarios, $linhaNova, FILE_APPEND);
            $msg = "Usuário #{$novoId} cadastrado com sucesso!";
        }
    }
}


if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $idExcluir = intval($_GET['id'] ?? 0);
    if ($idExcluir > 0 && file_exists($arquivoUsuarios)) {
        $linhas = file($arquivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $usuariosManter = [];

        foreach ($linhas as $linha) {
            $linhaLimpa = limparTexto($linha);
            if (empty($linhaLimpa)) continue;
            $dados = explode(";", $linhaLimpa);
            $idAtual = intval(limparTexto($dados[0]));

            if ($idAtual !== $idExcluir) {
                $usuariosManter[] = [
                    'idAntigo' => $idAtual,
                    'nome'     => limparTexto($dados[1] ?? ''),
                    'email'    => limparTexto($dados[2] ?? '')
                ];
            }
        }

        // Ordena e reindexa IDs (1, 2, 3...)
        usort($usuariosManter, fn($a, $b) => $a['idAntigo'] <=> $b['idAntigo']);

        $novoConteudo = "";
        $novoId = 1;
        foreach ($usuariosManter as $u) {
            $novoConteudo .= "{$novoId};{$u['nome']};{$u['email']}\n";
            $novoId++;
        }

        file_put_contents($arquivoUsuarios, $novoConteudo);
        header("Location: usuarios.php?msg=excluido");
        exit;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'excluido') {
    $msg = "Usuário excluído e IDs reordenados com sucesso!";
}

$usuarios = [];
if (file_exists($arquivoUsuarios)) {
    $linhas = file($arquivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linhaLimpa = limparTexto($linha);
        if (empty($linhaLimpa)) continue;
        $dados = explode(";", $linhaLimpa);
        if (count($dados) >= 3) {
            $usuarios[] = [
                'id'    => intval(limparTexto($dados[0])),
                'nome'  => limparTexto($dados[1]),
                'email' => limparTexto($dados[2])
            ];
        }
    }
}

$usuarioEditando = null;
if (isset($_GET['acao']) && $_GET['acao'] === 'editar') {
    $idEdit = intval($_GET['id'] ?? 0);
    foreach ($usuarios as $u) {
        if ($u['id'] === $idEdit) {
            $usuarioEditando = $u;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-box { 
            background: #f8f9fa; 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 30px; 
        }
        .tabela-usuarios { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
    }
        .tabela-usuarios th, .tabela-usuarios td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }

        .tabela-usuarios th { 
            background-color: #f2f2f2; 
        }
        .btn-acao { 
            padding: 5px 10px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 0.85em; 
            color: white; 
            margin-right: 5px; 
        }
        .btn-edit { 
            background-color: #ffc107; 
            color: #000; 
        }
        .btn-del { 
            background-color: #dc3545; 
        }
        .input-full { 
            width: 100%; 
            padding: 8px; 
            margin-top: 5px; 
            margin-bottom: 15px; 
            box-sizing: border-box; 
        }
        .alerta { 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 15px; 
        }
        .sucesso { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .erro { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.html" class="btn-voltar">← Voltar para o Menu Principal</a>

        <h1>Gerenciamento de Usuários</h1>

        <?php if (!empty($msg)) { ?><div class="alerta sucesso">✅ <?php echo $msg; ?></div><?php } ?>
        <?php if (!empty($erro)) { ?><div class="alerta erro">❌ <?php echo $erro; ?></div><?php } ?>

        <div class="form-box">
            <h2><?php echo $usuarioEditando ? "Editar Usuário #".$usuarioEditando['id'] : "Cadastrar Usuário"; ?></h2>
            
            <form action="usuarios.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $usuarioEditando ? $usuarioEditando['id'] : 0; ?>">

                <label>Nome:</label>
                <input type="text" name="nome" class="input-full" required value="<?php echo $usuarioEditando ? htmlspecialchars($usuarioEditando['nome']) : ''; ?>">

                <label>E-mail:</label>
                <input type="email" name="email" class="input-full" required value="<?php echo $usuarioEditando ? htmlspecialchars($usuarioEditando['email']) : ''; ?>">

                <input type="submit" value="<?php echo $usuarioEditando ? 'Salvar Alterações' : 'Cadastrar'; ?>">
                <?php if ($usuarioEditando) { ?>
                    <a href="usuarios.php" style="margin-left: 10px;">Cancelar</a>
                <?php } ?>
            </form>
        </div>

        <h2>Lista de Usuários (Total: <?php echo count($usuarios); ?>)</h2>

        <?php if (!empty($usuarios)) { ?>
            <table class="tabela-usuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u) { ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['nome']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <a href="usuarios.php?acao=editar&id=<?php echo $u['id']; ?>" class="btn-acao btn-edit">✏️ Editar</a>
                                <a href="usuarios.php?acao=excluir&id=<?php echo $u['id']; ?>" class="btn-acao btn-del" onclick="return confirm('Excluir o usuário #<?php echo $u['id']; ?>?');">🗑️ Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Nenhum usuário cadastrado.</p>
        <?php } ?>

    </div>

</body>
</html>