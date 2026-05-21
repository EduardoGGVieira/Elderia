// ─── Verificação de sessão ──────────────────────────────────────────
// CODIGO CORRIGIDO PELO PIERRE
// Bloqueia a página inteira enquanto verifica, evitando flash de conteúdo
// e race condition com o formulário de login.
(function checkSession() {
    fetch('get_session.php', {
        credentials: 'include' // garante envio do cookie de sessão
    })
    .then(response => {
        if (!response.ok) throw new Error('Resposta inválida do servidor');
        return response.json();
    })
    .then(data => {
        if (data.logged_in) {
            // Usuário já autenticado: redireciona e interrompe execução
            window.location.replace('/Elderia/perfil/index.html');
            // replace() ao invés de href: não adiciona ao histórico,
            // impedindo que o botão "voltar" retorne ao login
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));
})();


// ─── Formulário de login ────────────────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form        = this;
    const submitBtn   = form.querySelector('button[type="submit"]');
    const messageDiv  = document.getElementById('message');

    // Evita múltiplos submits enquanto a requisição está em andamento
    submitBtn.disabled = true;
    submitBtn.textContent = 'Entrando...';
    messageDiv.style.display = 'none';

    fetch('login.php', {
        method: 'POST',
        credentials: 'include', // essencial para persistir a sessão PHP
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Usa replace() pelo mesmo motivo: evita loop via botão "voltar"
            window.location.replace(data.redirect);
        } else {
            messageDiv.style.display = 'block';
            messageDiv.className    = 'error';
            messageDiv.textContent  = data.message; // textContent > innerText (segurança XSS)

            // Reabilita o botão apenas em caso de falha
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Entrar';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        messageDiv.style.display = 'block';
        messageDiv.className    = 'error';
        messageDiv.textContent  = 'Ocorreu um erro ao processar o login. Tente novamente.';

        submitBtn.disabled    = false;
        submitBtn.textContent = 'Entrar';
    });
});