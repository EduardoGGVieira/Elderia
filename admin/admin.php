<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElderiaMyAdmin</title>
    <style>
        :root {
            --cor-fundo: #F4F7F6;
            --cor-primaria: #1A5F7A;
            --cor-secundaria: #E36414;
            --cor-texto: #333333;
            --cor-borda: #CCCCCC;
            --cor-card: #FFFFFF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--cor-fundo);
            color: var(--cor-texto);
            font-size: 18px;
        }

        .cabecalho-principal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--cor-primaria);
            color: white;
            padding: 15px 30px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-container h1 {
            font-size: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background-color: var(--cor-card);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--cor-borda);
        }

        .card h3 {
            color: var(--cor-primaria);
            margin-bottom: 10px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 5px;
        }

        .delete {
            background: #e74c3c;
            color: white;
        }

        .view {
            background: var(--cor-primaria);
            color: white;
        }

        button:hover {
            opacity: 0.85;
        }

        h2 {
            color: var(--cor-primaria);
            margin-bottom: 20px;
        }

        .list-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .column {
            flex: 1;
            background: #e9ecef;
            padding: 15px;
            border-radius: 10px;
        }

        .column>h3 {
            text-align: center;
            color: var(--cor-primaria);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--cor-primaria);
            padding-bottom: 5px;
        }
    </style>
</head>

<body>

    <header class="cabecalho-principal">
        <div class="logo-container">
            <h1>Elderia Admin</h1>
        </div>
    </header>

    <div class="container" id="user-list"></div>

    <script>
        async function verificarAcessoAdmin() {
            const res = await fetch('../conta/login/get_session.php');
            const data = await res.json();
            if (!data.logged_in || data.tipo !== 'admin') {
                window.location.href = '../index.html';
            }
        }
        verificarAcessoAdmin();

        async function carregarUsuarios() {
            const res = await fetch('admin_api.php?action=list');
            const usuarios = await res.json();

            const container = document.getElementById('user-list');
            container.innerHTML = `
        <h2>Usuários cadastrados</h2>
        <div class="list-container">
            <div class="column" id="col-idosos">
                <h3>Idosos</h3>
            </div>
            <div class="column" id="col-profissionais">
                <h3>Profissionais</h3>
            </div>
        </div>
    `;

            const colIdosos = document.getElementById('col-idosos');
            const colProfissionais = document.getElementById('col-profissionais');

            usuarios.forEach(user => {
                const div = document.createElement('div');
                div.classList.add('card');

                div.innerHTML = `
            <h3>${user.nome} <small>(${user.tipo})</small></h3>
            <p>Email: ${user.email}</p>
            <br>
            <button class="delete" onclick="deletar(${user.id})">Deletar</button>
            <button class="view" onclick="abrirPerfil(${user.id})">Ver Perfil</button>
        `;

                if (user.tipo && user.tipo.toLowerCase() === 'idoso') {
                    colIdosos.appendChild(div);
                } else {
                    colProfissionais.appendChild(div);
                }
            });
        }

        async function deletar(id) {
            if (!confirm('Tem certeza que deseja deletar este usuário?')) return;
            await fetch(`admin_api.php?action=delete_user&id=${id}`);
            carregarUsuarios();
        }
// NÃO FUNCIONA O BOTÃO DE PERFIL
               function abrirPerfil(id) {
    window.location.href = `perfil_profissional.html?id=${id}`;
}



        async function verAvaliacoes(id) {
            const res = await fetch(`admin_api.php?action=list_reviews&id=${id}`);
            const avaliacoes = await res.json();

            let html = '<h2>Avaliações</h2>';

            avaliacoes.forEach(a => {
                html += `
            <div class="card">
                <p>${a.comentario}</p>
                <button class="delete" onclick="deletarAvaliacao(${a.id})">Deletar</button>
            </div>
        `;
            });

            html += '<button class="view" onclick="carregarUsuarios()">Voltar</button>';

            document.getElementById('user-list').innerHTML = html;
        }

        

        carregarUsuarios();
    </script>

</body>

</html>