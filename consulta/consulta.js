document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("container-consultas");

    fetch("consultas.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            if (data.length === 0) {
                container.innerHTML = "<p>Nenhuma consulta encontrada.</p>";
                return;
            }

            data.forEach(consulta => {
                const card = document.createElement("div");
                card.className = `card-mini-consulta status-${consulta.status.toLowerCase()}`;
                
                // Formatação da data para facilitar a utilização [cite: 7]
                const dataFormatada = new Date(consulta.data_hora).toLocaleString('pt-BR', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });

                card.innerHTML = `
                    <div class="info-principal">
                        <strong>${consulta.profissional_nome}</strong>
                        <span>${consulta.especialidade}</span>
                    </div>
                    <div class="info-data">
                        <p>📅 ${dataFormatada}</p>
                        <span class="badge-status">${consulta.status.toUpperCase()}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        })
        .catch(err => console.error("Erro ao carregar consultas:", err));
});