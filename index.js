const feed = document.querySelector(".grid-profissionais"); // Use a classe do seu container de cards

fetch("get_profissionais.php")
  .then((response) => response.json())
  .then((data) => {
    feed.innerHTML = ""; // Limpa o feed antes de carregar
    data.forEach((prof) => {
      const card = criarCardProfissional(prof);
      feed.appendChild(card);
    });
  })
  .catch((error) => {
    console.error("Erro ao buscar profissionais:", error);
  });

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