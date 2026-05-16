document.addEventListener("DOMContentLoaded", () => {

    const containerAtivas = document.getElementById("container-consultas");
    const containerHistorico = document.getElementById("container-historico");

    fetch("consultas.php")
        .then(response => response.json())
        .then(data => {

            console.log(data);

            if (data.error) {
                containerAtivas.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            if (data.length === 0) {
                containerAtivas.innerHTML =
                    "<p>Nenhuma consulta encontrada.</p>";
                return;
            }

            let temAtivas = false;
            let temHistorico = false;

            data.forEach(consulta => {

                const card = document.createElement("div");

                // status-agendada / status-cancelada / status-realizada
                card.className =
                    `card-mini-consulta status-${consulta.status.toLowerCase()}`;

                // formata a data
                const dataFormatada =
                    new Date(consulta.data_hora).toLocaleString("pt-BR", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit"
                    });

                card.innerHTML = `
                    <div class="info-principal">
                        <strong>${consulta.nome_profissional}</strong>
                        <span>${consulta.especialidade}</span>
                    </div>

                    <div class="info-data">
                        <p>${dataFormatada}</p>

                        <span class="badge-status">
                            ${consulta.status.toUpperCase()}
                        </span>
                    </div>
                `;

                // botão aparece só em consultas agendadas
                if (consulta.status.toLowerCase() === "agendada") {

                    card.innerHTML += `
                        <div class="acoes-consulta">
                            <button 
                                class="btn-reagendar"
                                data-consulta="${consulta.id_consulta}"
                                data-profissional="${consulta.id_profissional}"
                            >
                                REAGENDAR
                            </button>
                        </div>
                    `;
                }

                const status = consulta.status.toLowerCase();

                // consultas futuras
                if (status === "agendada") {

                    containerAtivas.appendChild(card);
                    temAtivas = true;

                } else {

                    containerHistorico.appendChild(card);
                    temHistorico = true;
                }

            });

            if (!temAtivas) {
                containerAtivas.innerHTML =
                    "<p>Nenhuma consulta futura.</p>";
            }

            if (!temHistorico) {
                containerHistorico.innerHTML =
                    "<p>Seu histórico está vazio.</p>";
            }

            // EVENTO DOS BOTÕES
            document.querySelectorAll(".btn-reagendar")
                .forEach(botao => {

                    botao.addEventListener("click", () => {

                        const idConsulta =
                            botao.dataset.consulta;

                        const idProfissional =
                            botao.dataset.profissional;

                        // redireciona para tela de reagendamento
                        window.location.href =
                            `reagendar.php?id_consulta=${idConsulta}&id_profissional=${idProfissional}`;
                    });

                });

        })
        .catch(err => {

            console.error("Erro ao carregar consultas:", err);

            containerAtivas.innerHTML =
                "<p>Erro ao carregar consultas.</p>";
        });

});