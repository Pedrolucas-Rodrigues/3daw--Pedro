<?php
require_once 'conexao.php';

$msg = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if (empty($nome) || empty($email)) {
        $erro = "Preencha o nome e o e-mail.";
    } else {
        if ($idUsuario > 0) {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
            $stmt->execute([
                ':nome'  => $nome,
                ':email' => $email,
                ':id'    => $idUsuario
            ]);
            $msg = "Usuário #{$idUsuario} atualizado com sucesso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (:nome, :email)");
            $stmt->execute([
                ':nome'  => $nome,
                ':email' => $email
            ]);
            $novoId = $pdo->lastInsertId();
            $msg = "Usuário #{$novoId} cadastrado com sucesso!";
        }
    }
}

if (isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $idExcluir = intval($_GET['id'] ?? 0);
    if ($idExcluir > 0) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $idExcluir]);
        header("Location: usuarios.php?msg=excluido");
        exit;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'excluido') {
    $msg = "Usuário excluído com sucesso!";
}

$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC")->fetchAll();

$usuarioEditando = null;
if (isset($_GET['acao']) && $_GET['acao'] === 'editar') {
    $idEdit = intval($_GET['id'] ?? 0);
    $stmtEdit = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmtEdit->execute([':id' => $idEdit]);
    $usuarioEditando = $stmtEdit->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="style.css">
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
                    <a href="usuarios.php" class="btn-cancelar">Cancelar</a>
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