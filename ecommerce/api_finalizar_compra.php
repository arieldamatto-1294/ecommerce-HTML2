<?php
// Impede qualquer eco ou espaço antes do cabeçalho JSON
ob_start();

session_start();
header('Content-Type: application/json; charset=utf-8');

// Usa a mesma conexão do seu sistema (conexao.php)
require_once "conexao.php";

try {
    // 1. Verifica se a sessão do carrinho existe e tem itens
    if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
        ob_clean();
        echo json_encode([
            'sucesso' => false, 
            'mensagem' => 'Seu carrinho está vazio ou a sessão expirou.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Garante que a variável $pdo existe (veio do conexao.php)
    if (!isset($pdo)) {
        throw new Exception("Erro de conexão com o banco de dados.");
    }

    $pdo->beginTransaction();

    foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
        // Atualiza o estoque no banco "ecommerce", tabela "produtos"
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?");
        $stmt->execute([$quantidade, $produto_id, $quantidade]);

        // Se nenhuma linha foi afetada, o estoque acabou ou o produto não existe
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            ob_clean();
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => "Estoque insuficiente para o produto ID {$produto_id}."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Limpa o carrinho da sessão e confirma a transação
    unset($_SESSION['carrinho']);
    $pdo->commit();

    ob_clean();
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Compra finalizada com sucesso! Estoque atualizado.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_clean();
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Erro no servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>