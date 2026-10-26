<?php
// api_estoque.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST");

require_once "conexao.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') { 
  try {
    // 1. Força o PDO a disparar exceções caso a tabela não exista ou esteja errada
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM produtos");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Se a consulta funcionou, exibe a lista (mesmo que vazia)
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
  } catch (Exception $e) {
    // Se der erro no SQL, exibe a mensagem real do MySQL para descobrirmos o que houve
    echo json_encode(["erro" => "Erro na consulta SQL: " . $e->getMessage()]);
  }
  exit;
}

if ($method === 'POST') { 
  $data = json_decode(file_get_contents("php://input"), true);

  if (isset($data['produto_id'], $data['nova_quantidade'])) {
    $id = (int)$data['produto_id'];
    $quantity = (int)$data['nova_quantidade'];

    $stmt = $pdo->prepare("UPDATE produtos SET estoque = :estoque WHERE id = :id");
    $success = $stmt->execute([':estoque' => $quantity, ':id' => $id]);

    echo json_encode(["sucesso" => $success]);
    exit;
  }

  echo json_encode(["sucesso" => false, "mensagem" => "Dados incompletos."]);
  exit;
}