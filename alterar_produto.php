<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] !=2 && $_SESSION['perfil'] !=3){

    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$produto = null;

// Processa alteração de dados se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto']) && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
    $id_produto = $_POST['id_produto'];
    $nome_prod = trim($_POST['nome_prod']);
    $descricao = trim($_POST['descricao']);
    $qtde = trim($_POST['qtde']);
    $valor_unit = trim($_POST['valor_unit']);

    $sql = "UPDATE produto SET nome_prod = :nome_prod, descricao = :descricao, qtde = :qtde, valor_unit = :valor_unit" .
           " WHERE id_produto = :id_produto";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome_prod', $nome_prod);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':qtde', $qtde);
    $stmt->bindParam(':valor_unit', $valor_unit);
    $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Produto alterado com sucesso!'); window.location.href='alterar_produto.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao alterar produto!'); window.location.href='alterar_produto.php';</script>";
        exit();
    }
}

// Processa busca de usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca_produto']) && (!isset($_POST['acao']) || $_POST['acao'] !== 'alterar')) {
    $busca = trim($_POST['busca_produto']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM produto WHERE id_produto = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM produto WHERE nome_prod LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo "<script>alert('Produto não encontrado!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Produto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Alterar Produto</h2>

    <!-- Formulário para buscar usuário pelo ID ou Nome -->
    <form action="alterar_produto.php" method="POST">
        <label for="busca_produto">Digite o ID ou Nome do produto:</label>
        <input type="text" id="busca_produto" name="busca_produto" required>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($produto): ?>
        <!-- Formulário para alterar usuário -->
        <form action="alterar_produto.php" method="POST">
            <input type="hidden" name="id_produto" value="<?= htmlspecialchars($produto['id_produto']) ?>">
            <input type="hidden" name="acao" value="alterar">

            <label for="nome_prod">Nome do Produto:</label>
            <input type="text" id="nome_prod" name="nome_prod" value="<?= htmlspecialchars($produto['nome_prod']) ?>" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($produto['descricao']) ?>" required>

            <label for="qtde">Quantidade:</label>
            <input type="number" id="qtde" name="qtde" value="<?= htmlspecialchars($produto['qtde']) ?>" required>

            <label for="valor_unit">Valor Unitário:</label>
            <input type="number" id="valor_unit" name="valor_unit" value="<?= htmlspecialchars($produto['valor_unit']) ?>" required>


            <button type="submit">Alterar</button>
            <button type="reset">Cancelar</button>
        </form>
    <?php endif; ?>

    <a href="principal.php">Voltar</a>
</body>
</html>
