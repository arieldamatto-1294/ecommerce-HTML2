<?php
// api_login.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

// Inicia a sessão para podermos lembrar que o usuário está logado
session_start();

require_once "conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['email']) || !isset($data['senha'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Por favor, preencha todos os campos."]);
    exit;
  }

  $email = trim($data['email']);
  $senha = $data['senha'];

  try {
    // Busca o usuário pelo e-mail informado
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Se o usuário existir e a senha criptografada bater com a digitada
    if ($usuario && md5($senha) === $usuario['senha']) {
        
      // Salva as informações do usuário logado na Sessão do PHP
      $_SESSION['usuario_id'] = $usuario['id'];
      $_SESSION['usuario_nome'] = $usuario['nome'];

      echo json_encode(["sucesso" => true]);
      exit;
    } else {
      echo json_encode(["sucesso" => false, "mensagem" => "E-mail ou senha incorretos."]);
      exit;
    }

  } catch (Exception $e) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
    exit;
  }
}