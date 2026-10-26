<?php

try {
  $pdo = new PDO("mysql:host=localhost;port=3307;dbname=ecommerce;charset=utf8", "root", "usbw", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["erro" => "Falha na conexão: " . $e->getMessage()]);
  exit;
}