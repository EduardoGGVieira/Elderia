const feed = document.querySelector(".grid-profissionais"); 
const headerUser = document.querySelector(".usuario-info");
const navBotoes = document.querySelector(".navegacao-botoes");

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
          linkConsultas = 'admin/admin.html'; 
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
    }
  })
  .catch((error) => console.error("Erro ao verificar sessão:", error));

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