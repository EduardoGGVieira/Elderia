// 1. Verificação de Sessão: Redireciona se já estiver logado
fetch('get_session.php')
    .then(response => response.json())
    .then(data => {
        if (data.logged_in) {
            // Se já estiver logado, vai direto para o perfil
            window.location.href = '../../perfil/index.html';
        }
    })
    .catch(error => console.error('Erro ao verificar sessão:', error));

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const messageDiv = document.getElementById('message');

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            messageDiv.style.display = 'block';
            messageDiv.className = 'error';
            messageDiv.innerText = data.message;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar o login.');
    });
});