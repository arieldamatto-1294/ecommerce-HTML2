document.getElementById('form-login').addEventListener('submit', async (e) => {
  e.preventDefault(); // Impede a página de recarregar

  const email = document.getElementById('email').value;
  const password = document.getElementById('senha').value;
  const message_div = document.getElementById('mensagem-resposta');

  message_div.textContent = 'Autenticando...';
  message_div.className = 'mensagem';

  try {
      const resposta = await fetch('api_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email, password: password
        })
      });

      const result = await resposta.json();

      if (result.sucesso) {
          message_div.textContent = 'Login feito com sucesso! Redirecionando...';
          message_div.className = 'mensagem sucesso';
          
          // Redireciona o usuário para a tela de estoque após 1.5 segundos
          setTimeout(() => {
            window.location.href = 'estoque.html';
          }, 1500);
      } else {
        message_div.textContent = result.mensagem;
        message_div.className = 'mensagem erro';
      }
  } catch (error) {
      message_div.textContent = 'Erro ao conectar ao servidor.';
      message_div.className = 'mensagem erro';
  }
});