<?php
// api_carrinho.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE");

session_start();
require_once "conexao.php";

// Inicializa o carrinho na sessão caso ele ainda não exista
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. ROTA PARA LISTAR OS ITENS DO CARRINHO (GET)
if ($method === 'GET') {
    $itens_carrinho = [];
    $total_geral = 0;

    if (!empty($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $id_produto => $quantidade) {
    // Busca os dados atualizados do produto diretamente do banco
    $stmt = $pdo->prepare("SELECT id, nome, preco, estoque FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id_produto]);
    $produto = $stmt->fetch();

    if ($produto) {
        $subtotal = $produto['preco'] * $quantidade;
        $total_geral += $subtotal;

        $itens_carrinho[] = [
            "id" => $produto['id'],
            "nome" => $produto['nome'],
            "preco" => (float)$produto['preco'], // CORREÇÃO: Trocado de $p['preco'] para $produto['preco']
            "quantidade" => $quantidade,
            "subtotal" => $subtotal,
            "estoque_disponivel" => (int)$produto['estoque']
        ];
    }
}

    }

    // Retorna o JSON correto
    echo json_encode([
        "itens" => $itens_carrinho,
        "total" => $total_geral
    ], JSON_UNESCAPED_UNICODE);
    
    // OBRIGATÓRIO: Mata a execução aqui para o PHP não duplicar dados na tela
    exit; 
}


// 2. ROTA PARA ADICIONAR OU ATUALIZAR ITEM (POST)
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['produto_id']) || !isset($data['quantidade'])) {
        echo json_encode(["sucesso" => false, "mensagem" => "Dados incompletos."]);
        exit;
    }

    $id_produto = (int)$data['produto_id'];
    $quantidade_desejada = (int)$data['quantidade'];

    // Verifica se o produto existe e tem estoque suficiente
    $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :id");
    $stmt->execute([':id' => $id_produto]);
    $produto = $stmt->fetch();

    if (!$produto) {
        echo json_encode(["sucesso" => false, "mensagem" => "Produto não encontrado."]);
        exit;
    }

    // Calcula quanto já tem no carrinho atualmente
    $quantidade_atual_no_carrinho = isset($_SESSION['carrinho'][$id_produto]) ? $_SESSION['carrinho'][$id_produto] : 0;
    $nova_quantidade_total = $quantidade_atual_no_carrinho + $quantidade_desejada;

    if ($nova_quantidade_total > $produto['estoque']) {
        echo json_encode(["sucesso" => false, "mensagem" => "Estoque insuficiente. Disponível: " . $produto['estoque']]);
        exit;
    }

    // Adiciona ou soma a quantidade na sessão
    $_SESSION['carrinho'][$id_produto] = $nova_quantidade_total;

    echo json_encode(["sucesso" => true, "mensagem" => "Produto adicionado ao carrinho!"]);
    exit;
}

// 3. ROTA PARA REMOVER UM ITEM DO CARRINHO (DELETE)
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['produto_id'])) {
        $id_produto = (int)$data['produto_id'];
        unset($_SESSION['carrinho'][$id_produto]);
        echo json_encode(["sucesso" => true, "mensagem" => "Produto removido."]);
        exit;
    }

    echo json_encode(["sucesso" => false, "mensagem" => "ID não informado."]);
    exit;
}
