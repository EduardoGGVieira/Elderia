const feed = document.querySelector(".grid-profissionais"); 
const headerUser = document.querySelector(".usuario-info");
const navBotoes = document.querySelector(".navegacao-botoes");
const secaoBoasVindas = document.querySelector(".boas-vindas");
const secaoProfissionais = document.querySelector(".aba-profissionais");

fetch("conta/login/get_session.php")
  .then((response) => response.json())
  .then((data) => {
    if (data.logged_in && headerUser) {
      headerUser.innerHTML = `
        <span class="user-name" style="margin-right: 15px;">Olá, <strong>${data.nome}</strong></span>
        <a href="conta/login/logout.php" class="btn-sair">Sair</a>
      `;

      // Pierre > Altera a navegação dinamicamente conforme o tipo de usuário (Incluindo Admin)
      if (navBotoes) {
        let linkConsultas = 'consulta/index.html';
        let textoLink = 'Consultas';

        // Validação dos tipos de perfis do banco
        if (data.tipo === 'profissional') {
          linkConsultas = 'consulta/confirmar.php';
          textoLink = 'Confirmar Consultas';
        } else if (data.tipo === 'admin') {
          linkConsultas = 'admin/admin.php'; 
          textoLink = 'Painel Admin';
        
        }
        
        // Renderiza os botões correspondentes ao nível de acesso
        if (data.tipo === 'admin') {
          navBotoes.innerHTML = `
            <a href="${linkConsultas}" class="btn-nav">${textoLink}</a>
          `;
        } else {
          navBotoes.innerHTML = `
            <a href="perfil/" class="btn-nav">Meu Perfil</a>
            <a href="${linkConsultas}" class="btn-nav">${textoLink}</a>
          `;
        }
      }

      // Lógica específica para Admin: Adiciona área de suporte entre a busca e os profissionais
      if (data.tipo === 'admin' && secaoBoasVindas) {
        secaoBoasVindas.insertAdjacentHTML('afterend', `
          <div class="suporte-admin-container" style="background-color: #f9f9f9; padding: 40px 20px; border-bottom: 2px solid #eee;">
              <div class="secao-titulo" style="text-align: center;">
                  <h2>Área de Suporte Admin</h2>
                  <div class="linha-decorativa"></div>
                  <p style="margin: 20px 0; color: #555; font-size: 1.1rem;">
                    Olá, <strong>${data.nome}</strong>. Como administrador, você tem acesso direto aos desenvolvedores.
                  </p>
                  <p style="color: #777; margin-bottom: 30px;">Use o botão abaixo para abrir um ticket ou reportar falhas no sistema.</p>
                  <a href="mailto:devs@elderia.com.br?subject=Suporte Administrativo - Elderia (Admin: ${data.nome})&body=Olá Equipe de Desenvolvimento,%0D%0A%0D%0AComo administrador do sistema Elderia, gostaria de reportar o seguinte:%0D%0A%0D%0A[Escreva sua mensagem aqui]" class="btn-pesquisar" style="display: inline-block; text-decoration: none;">Enviar Ticket para Desenvolvedores</a>
              </div>
          </div>
        `);

        // Oculta a seção inteira de profissionais (título e cards) para o administrador
        if (secaoProfissionais) secaoProfissionais.style.display = 'none';
      }
    }

    // Carrega a lista de profissionais apenas se o usuário NÃO for administrador
    if (data.tipo !== 'admin') {
      carregarProfissionais();
    }
  })
  .catch((error) => console.error("Erro ao verificar sessão:", error));


  
// função de apagar comentário como admin

function apagarAvaliacao(idAvaliacao) {
    const mensagemConfirmacao = "Tem certeza que deseja apagar permanentemente esta avaliação? Esta ação não poderá ser desfeita.";

    if (confirm(mensagemConfirmacao)) {
        const dadosEnviar = new FormData();
        dadosEnviar.append('id_avaliacao', idAvaliacao);

        fetch("admin/deletar_avaliacao.php", {
            method: "POST",
            body: dadosEnviar
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro no servidor (Status: ${response.status})`);
            }
            return response.json();
        })
        .then(resultado => {
            if (resultado.success) {
                alert("Avaliação apagada com sucesso!");
                window.location.reload(); // Recarrega a página atual para atualizar a lista
            } else {
                alert("Erro ao apagar: " + resultado.error);
            }
        })
        .catch(err => {
            console.error("Erro na requisição:", err);
            alert("Erro de comunicação: " + err.message);
        });
    }
}

function carregarProfissionais() {
  if (feed) {
    fetch("get_profissionais.php")
      .then((response) => response.json())
      .then((data) => {
        feed.innerHTML = ""; 
        data.forEach((prof) => {
          const card = criarCardProfissional(prof);
          feed.appendChild(card);
        });
      })
      .catch((error) => console.error("Erro ao buscar profissionais:", error));
  }
}

function criarCardProfissional(prof) {
  const card = document.createElement("div");
  card.classList.add("card-profissional"); 

  const fotoPlaceholder = "https://media.tenor.com/wGufiBV_pI0AAAAe/hide-the-pain-harold-pain.png";

  card.innerHTML = `
    <div class="info-profissional">
        <img src="${prof.foto || fotoPlaceholder}" alt="Foto de ${prof.nome}" class="foto-perfil">
        
        <div class="dados-texto">
            <h3>${prof.nome || "Especialista"}</h3>
            <p class="especialidade">${prof.especialidade || "Saúde"}</p>
            <p class="avaliacao" style="margin-top: 10px; color: #555;">${prof.biografia || "Sem descrição disponível."}</p>
        </div>
    </div>

    <div class="agenda-profissional" style="justify-content: center;">
        <a href="perfil.php?id=${prof.id_profissional}" class="btn-ver-mais" style="text-decoration: none; text-align: center;">Ver Perfil Completo</a>
    </div>
  `;

  return card;
}