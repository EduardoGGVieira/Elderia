const feed = document.querySelector(".grid-profissionais"); 
const headerUser = document.querySelector(".usuario-info");

// 1. Verificar Sessão para Atualizar Cabeçalho
fetch("conta/login/get_session.php")
  .then((response) => response.json())
  .then((data) => {
    if (data.logged_in && headerUser) {
      headerUser.innerHTML = `
        <span class="user-name">Olá, <strong>${data.nome}</strong> (ID: ${data.id})</span>
        <a href="conta/login/logout.php" class="btn-login">Sair</a>
      `;
    }
  })
  .catch((error) => console.error("Erro ao verificar sessão:", error));

// 2. Buscar Profissionais
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
  
  card.innerHTML = `
    <h3>${prof.nome || "Especialista"}</h3>
    <span class="especialidade-tag">${prof.especialidade || "Saúde"}</span>
    <p class="biografia-curta">${prof.biografia || "Sem descrição disponível."}</p>
    <a href="perfil.php?id=${prof.id_profissional}" class="btn-perfil">Ver Perfil Completo</a>
  `;

  return card;
}