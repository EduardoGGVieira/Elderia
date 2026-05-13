document.addEventListener("DOMContentLoaded", () => {
    const containerAtivas = document.getElementById("container-consultas");
    const containerHistorico = document.getElementById("container-historico");

    fetch("consultas.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                containerAtivas.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            if (data.length === 0) {
                container.innerHTML = "<p>Nenhuma consulta encontrada.</p>";
                return;
            }

            let temAtivas = false;
            let temHistorico = false;

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

                // Lógica de separação: Agendada/Confirmada vai para o topo, o resto para o histórico
                const status = consulta.status.toLowerCase();
                if (status === 'agendada' || status === 'confirmada') {
                    containerAtivas.appendChild(card);
                    temAtivas = true;
                } else {
                    containerHistorico.appendChild(card);
                    temHistorico = true;
                }
            });

            if (!temAtivas) containerAtivas.innerHTML = "<p>Nenhuma consulta futura.</p>";
            if (!temHistorico) containerHistorico.innerHTML = "<p>Seu histórico está vazio.</p>";
        })
        .catch(err => console.error("Erro ao carregar consultas:", err));
});