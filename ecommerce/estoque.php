<?php
// estoque.php (desconsiderar este arquivo)
$pdo = new PDO("mysql:host=localhost;dbname=ecommerce;charset=utf8", "root", "usbw");

// Processa a atualização de estoque se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id'], $_POST['nova_quantidade'])) {
  $stmt = $pdo->prepare("UPDATE produtos SET estoque = :estoque WHERE id = :id");
  $stmt->execute([
    ':estoque' => (int)$_POST['nova_quantidade'],
    ':id' => (int)$_POST['produto_id']
  ]);
  header("Location: estoque.php");
  exit;
}