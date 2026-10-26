<?php
// teste.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Tenta conectar diretamente
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=ecommerce;charset=utf8", "root", "usbw", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Tenta buscar os produtos
    $stmt = $pdo->query("SELECT * FROM produtos");
    $produtos = $stmt->fetchAll();

    // Se chegou aqui, deu certo! Mostra o resultado
    echo json_encode([
        "status" => "sucesso",
        "mensagem" => "Conectado e listado com sucesso!",
        "dados" => $produtos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Captura erros específicos do Banco de Dados
    echo json_encode([
        "status" => "erro_banco",
        "mensagem" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // Captura qualquer outro tipo de erro no PHP
    echo json_encode([
        "status" => "erro_geral",
        "mensagem" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}