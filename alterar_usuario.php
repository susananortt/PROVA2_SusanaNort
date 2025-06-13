<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$usuario = null;

// Processa alteração de dados se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario']) && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
    $id_usuario = $_POST['id_usuario'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $id_perfil = $_POST['id_perfil'];
    $nova_senha = !empty($_POST['nova_senha']) ? password_hash($_POST['nova_senha'], PASSWORD_DEFAULT) : null;

    $sql = "UPDATE usuario SET nome = :nome, email = :email, id_perfil = :id_perfil" .
           ($nova_senha ? ", senha = :senha" : "") .
           " WHERE id_usuario = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id_perfil', $id_perfil);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    if ($nova_senha) {
        $stmt->bindParam(':senha', $nova_senha);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Usuário alterado com sucesso!'); window.location.href='alterar_usuario.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao alterar usuário!'); window.location.href='alterar_usuario.php';</script>";
        exit();
    }
}

// Processa busca de usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca_usuario']) && (!isset($_POST['acao']) || $_POST['acao'] !== 'alterar')) {
    $busca = trim($_POST['busca_usuario']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Usuário</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Alterar Usuário</h2>

    <!-- Formulário para buscar usuário pelo ID ou Nome -->
    <form action="alterar_usuario.php" method="POST">
        <label for="busca_usuario">Digite o ID ou Nome do usuário:</label>
        <input type="text" id="busca_usuario" name="busca_usuario" required>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($usuario): ?>
        <!-- Formulário para alterar usuário -->
        <form action="alterar_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
            <input type="hidden" name="acao" value="alterar">

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="id_perfil">Perfil:</label>
            <select id="id_perfil" name="id_perfil">
                <option value="1" <?= $usuario['id_perfil'] == 1 ? 'selected' : '' ?>>Administrador</option>
                <option value="2" <?= $usuario['id_perfil'] == 2 ? 'selected' : '' ?>>Secretaria</option>
                <option value="3" <?= $usuario['id_perfil'] == 3 ? 'selected' : '' ?>>Almoxarife</option>
                <option value="4" <?= $usuario['id_perfil'] == 4 ? 'selected' : '' ?>>Cliente</option>
            </select>

            <?php if ($_SESSION['perfil'] == 1): ?>
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Deixe em branco para não alterar">
            <?php endif; ?>

            <button type="submit">Alterar</button>
            <button type="reset">Cancelar</button>
        </form>
    <?php endif; ?>

    <a href="principal.php">Voltar</a>
</body>
</html>
