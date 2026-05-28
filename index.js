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

      if (navBotoes) {
        let linkConsultas = 'consulta/index.html';
        let textoLink = 'Consultas';

        if (data.tipo === 'profissional') {
          linkConsultas = 'consulta/confirmar.php';
          textoLink = 'Confirmar Consultas';
        } else if (data.tipo === 'admin') {
          linkConsultas = 'admin/admin.php';
          textoLink = 'Painel Admin';
        }

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

        if (secaoProfissionais) secaoProfissionais.style.display = 'none';
      }
    }

    if (data.tipo !== 'admin') {
      carregarProfissionais();
    }
  })
  .catch((error) => console.error("Erro ao verificar sessão:", error));

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

          mostrarMensagem("success", "Avaliação apagada com sucesso!");

          setTimeout(() => {
            window.location.reload();
          }, 1200);

        } else {

          mostrarMensagem("error", "Erro ao apagar: " + resultado.error);

        }
      })
      .catch(err => {

        console.error("Erro na requisição:", err);

        mostrarMensagem("error", "Erro de comunicação com o servidor.");

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

  const fotoPlaceholder = "https://img.freepik.com/fotos-premium/medicos-enfermeiros-e-retrato-de-equipe-em-clinica-hospitalar-ou-consultorio-medico-diversidade-de-profissionais-de-saude-e-de-saude-juntos-bracos-cruzados-em-colaboracao-ou-suporte-de-trabalho-em-equipe-de-confianca_590464-89340.jpg?w=1380";

  card.innerHTML = `
    <div class="info-profissional">
        <img src="${prof.foto || fotoPlaceholder}" alt="Foto de ${prof.nome}" class="foto-perfil">
        
        <div class="dados-texto">
            <h3>${prof.nome || "Especialista"}</h3>
            <p class="especialidade">${prof.especialidade || "Saúde"}</p>
            <p class="biografia-preview">${prof.biografia || "Sem descrição disponível."}</p>
        </div>
    </div>

    <div class="agenda-profissional">
        <a href="perfil.php?id=${prof.id_profissional}" class="btn-ver-mais">Ver Perfil Completo</a>
    </div>
  `;

  const containerDepoimentos = document.getElementById("container-depoimentos");

  if (containerDepoimentos) {
    fetch("get_depoimentos.php")
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
          containerDepoimentos.innerHTML = `<p style="text-align: center; width: 100%; color: #777;">Nenhum depoimento disponível no momento.</p>`;
          return;
        }

        containerDepoimentos.innerHTML = "";

        data.forEach(depoimento => {

          let estrelas = "⭐".repeat(depoimento.nota);

          const cardDepoimento = document.createElement("div");
          cardDepoimento.style.cssText = `
                    background: white; 
                    padding: 25px; 
                    border-radius: 12px; 
                    box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
                    flex: 1; 
                    min-width: 280px;
                    border-left: 5px solid var(--cor-secundaria);
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                `;

          cardDepoimento.innerHTML = `
                    <div>
                        <p style="color: #f1c40f; margin-bottom: 10px; font-size: 1.1rem;">${estrelas}</p>
                        <p style="font-style: italic; color: #555; line-height: 1.5;">"${depoimento.comentario || 'Sem comentários.'}"</p>
                    </div>
                    <br>
                    <strong style="color: var(--cor-primaria);">- ${depoimento.nome_idoso}</strong>
                `;

          containerDepoimentos.appendChild(cardDepoimento);
        });
      })
      .catch(error => {
        console.error("Erro ao buscar depoimentos:", error);
        containerDepoimentos.innerHTML = `<p style="text-align: center; width: 100%; color: #e74c3c;">Erro ao carregar depoimentos.</p>`;
      });
  }

  return card;
}