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

        .container {
            max-width: 1100px;
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

        button,
        .btn-link {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin: 5px 5px 5px 0;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .delete {
            background: #e74c3c;
            color: white;
        }

        .view {
            background: var(--cor-primaria);
            color: white;
        }

        .approve {
            background: #27ae60;
            color: white;
        }

        .reject {
            background: #f39c12;
            color: white;
        }

        button:hover,
        .btn-link:hover {
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

        .docs-area {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
            background: white;
            font-size: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: var(--cor-primaria);
            color: white;
        }

        .status {
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pendente {
            color: #f39c12;
        }

        .status-aprovado {
            color: #27ae60;
        }

        .status-reprovado {
            color: #e74c3c;
        }

        @media (max-width: 800px) {
            .list-container {
                flex-direction: column;
            }

            table {
                font-size: 13px;
            }
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;

            background: #333;
            color: white;

            padding: 14px 20px;
            border-radius: 10px;

            opacity: 0;
            pointer-events: none;

            transform: translateY(-20px);

            transition: 0.3s ease;

            z-index: 9999;
            font-weight: bold;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background: #27ae60;
        }

        .toast.error {
            background: #e74c3c;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;

            background: rgba(0, 0, 0, 0.5);

            display: none;
            justify-content: center;
            align-items: center;

            z-index: 9998;
        }

        .modal-box {
            background: white;

            padding: 25px;
            border-radius: 14px;

            width: 90%;
            max-width: 400px;

            animation: aparecer 0.2s ease;
        }

        .modal-box h3 {
            margin-bottom: 10px;
            color: #e74c3c;
        }

        .modal-box p {
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .cancel-btn {
            background: #bdc3c7;
            color: black;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
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

        function caminhoParaLink(caminho) {
            if (!caminho) return '#';

            caminho = caminho.replaceAll('\\', '/');

            const posUploads = caminho.indexOf('uploads/');

            if (posUploads !== -1) {
                return '../' + caminho.substring(posUploads);
            }

            return caminho;
        }

        function textoStatus(status) {
            status = status || 'pendente';

            return `<span class="status status-${status}">${status}</span>`;
        }

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

                const ehProfissional = user.tipo && user.tipo.toLowerCase() === 'profissional';

                div.innerHTML = `
                <h3>${user.nome} <small>(${user.tipo})</small></h3>
                <p>Email: ${user.email}</p>
                <br>

                <button class="delete" onclick="deletar(${user.id})">Deletar</button>
                <button class="view" onclick="abrirPerfil(${user.id}, '${user.tipo}')">Ver Perfil</button>

                ${ehProfissional ? `<button class="view" onclick="toggleDocumentos(${user.id})">Ver documentos</button>` : ''}

                <div class="docs-area" id="docs-${user.id}"></div>
            `;

                if (user.tipo && user.tipo.toLowerCase() === 'idoso') {
                    colIdosos.appendChild(div);
                } else {
                    colProfissionais.appendChild(div);
                }
            });
        }

        let usuarioParaDeletar = null;

        function deletar(id) {
            usuarioParaDeletar = id;

            const modal = document.getElementById('modalConfirmacao');

            modal.style.display = 'flex';
        }

        function fecharModal() {
            document.getElementById('modalConfirmacao').style.display = 'none';

            usuarioParaDeletar = null;
        }

        async function confirmarDelete() {

            if (!usuarioParaDeletar) return;

            try {

                const res = await fetch(`admin_api.php?action=delete_user&id=${usuarioParaDeletar}`);

                if (res.ok) {

                    mostrarToast('Usuário deletado com sucesso!', 'success');

                    fecharModal();

                    carregarUsuarios();

                } else {

                    mostrarToast('Erro ao deletar usuário.', 'error');
                }

            } catch (err) {

                mostrarToast('Erro de conexão com o servidor.', 'error');
            }
        }

        function abrirPerfil(id, tipo) {
            if (tipo && tipo.toLowerCase() === 'idoso') {
                window.location.href = `../perfil/ver_ficha.php?id=${id}`;
            } else {
                window.location.href = `../perfil.php?id=${id}`;
            }
        }

        async function toggleDocumentos(id) {
            const area = document.getElementById(`docs-${id}`);

            if (area.style.display === 'block') {
                area.style.display = 'none';
                return;
            }

            area.style.display = 'block';
            area.innerHTML = '<p>Carregando documentos...</p>';

            const res = await fetch(`admin_api.php?action=get_documents&id=${id}`);
            const data = await res.json();

            if (!data.success) {
                area.innerHTML = '<p>Erro ao carregar documentos.</p>';
                return;
            }

            let html = `
            <h3>Certificados</h3>
            ${montarTabelaCertificados(data.certificados, id)}

            <h3>Documentação profissional</h3>
            ${montarTabelaDocumento(data.documento, id)}
        `;

            area.innerHTML = html;
        }

        function montarTabelaCertificados(certificados, idProfissional) {
            if (!certificados || certificados.length === 0) {
                return '<p>Nenhum certificado enviado.</p>';
            }

            let html = `
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Data de emissão</th>
                        <th>Arquivo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
        `;

            certificados.forEach(c => {
                const link = caminhoParaLink(c.url_documento);

                html += `
                <tr>
                    <td>${c.titulo || 'Sem título'}</td>
                    <td>${c.data_emissao || '-'}</td>
                    <td>
                        ${c.url_documento ? `<a href="${link}" target="_blank">Abrir arquivo</a>` : 'Sem arquivo'}
                    </td>
                    <td>${textoStatus(c.status)}</td>
                    <td>
                        ${c.status !== 'aprovado' ? `<button class="approve" onclick="acaoCertificado('validar_certificado', ${c.id_certificado}, ${idProfissional})">Validar</button>` : ''}
                        ${c.status !== 'reprovado' ? `<button class="reject" onclick="acaoCertificado('reprovar_certificado', ${c.id_certificado}, ${idProfissional})">Reprovar</button>` : ''}
                        ${c.status === 'aprovado' ? `<button class="delete" onclick="acaoCertificado('excluir_certificado', ${c.id_certificado}, ${idProfissional})">Excluir</button>` : ''}
                    </td>
                </tr>
            `;
            });

            html += `
                </tbody>
            </table>
        `;

            return html;
        }

        function montarTabelaDocumento(documento, idProfissional) {
            if (!documento || !documento.documentacao_url) {
                return '<p>Nenhuma documentação profissional enviada.</p>';
            }

            const link = caminhoParaLink(documento.documentacao_url);

            return `
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Arquivo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${documento.documentacao_numero || '-'}</td>
                        <td><a href="${link}" target="_blank">Abrir documento</a></td>
                        <td>${textoStatus(documento.documentacao_status)}</td>
                        <td>
                            ${documento.documentacao_status !== 'aprovado' ? `<button class="approve" onclick="acaoDocumento('validar_documento', ${idProfissional})">Validar</button>` : ''}
                            ${documento.documentacao_status !== 'reprovado' ? `<button class="reject" onclick="acaoDocumento('reprovar_documento', ${idProfissional})">Reprovar</button>` : ''}
                            ${documento.documentacao_status === 'aprovado' ? `<button class="delete" onclick="acaoDocumento('excluir_documento', ${idProfissional})">Excluir</button>` : ''}
                        </td>
                    </tr>
                </tbody>
            </table>
        `;
        }

        async function acaoCertificado(action, idCertificado, idProfissional) {
            if (action.includes('excluir') && !confirm('Tem certeza que deseja excluir este certificado?')) return;

            await fetch(`admin_api.php?action=${action}&id=${idCertificado}`);
            await toggleDocumentos(idProfissional);
            await toggleDocumentos(idProfissional);
        }

        async function acaoDocumento(action, idProfissional) {
            if (action.includes('excluir') && !confirm('Tem certeza que deseja excluir este documento?')) return;

            await fetch(`admin_api.php?action=${action}&id=${idProfissional}`);
            await toggleDocumentos(idProfissional);
            await toggleDocumentos(idProfissional);
        }

        carregarUsuarios();

        function mostrarToast(mensagem, tipo = 'success') {

            const toast = document.getElementById('toast');

            toast.textContent = mensagem;

            toast.className = `toast show ${tipo}`;

            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

    </script>

    <div id="toast" class="toast"></div>

    <div id="modalConfirmacao" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirmar exclusão</h3>

            <p id="textoModal">
                Tem certeza que deseja deletar este usuário?
            </p>

            <div class="modal-buttons">
                <button class="cancel-btn" onclick="fecharModal()">
                    Cancelar
                </button>

                <button class="delete-btn" onclick="confirmarDelete()">
                    Deletar
                </button>
            </div>
        </div>
    </div>
</body>

</html>