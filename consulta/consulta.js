document.addEventListener("DOMContentLoaded", () => {

    const containerAtivas = document.getElementById("container-consultas");
    const containerHistorico = document.getElementById("container-historico");

    const messageBox = document.getElementById("messageBox");

    function mostrarMensagem(tipo, mensagem) {

        messageBox.style.display = "block";
        messageBox.className = "message-box " + tipo;
        messageBox.textContent = mensagem;

        setTimeout(() => {
            messageBox.style.display = "none";
        }, 5000);
    }

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
                const status = consulta.status.toLowerCase();
                card.className = `card-mini-consulta status-${status}`;
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

                if (status === "agendada" || status === "confirmada") {

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

                if (status === "agendada" || status === "confirmada") {

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
            document.querySelectorAll(".btn-reagendar")
                .forEach(botao => {

                    botao.addEventListener("click", () => {

                        const idConsulta = botao.dataset.consulta;

                        const mensagemConfirmacao = "tem certeza que dejesa remarcar esta consulta? caso de \"ok\" seu horario vai ser disponibilizado novamente e você poderá realizar outro agendamento";

                        if (confirm(mensagemConfirmacao)) {

                            const dadosEnviar = new FormData();
                            dadosEnviar.append('id_consulta', idConsulta);

                            fetch("deletar_consulta.php", {
                                method: "POST",
                                body: dadosEnviar
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`Ficheiro não encontrado ou erro no servidor (Status: ${response.status})`);
                                    }
                                    return response.text();
                                })
                                .then(texto => {
                                    try {
                                        const resultado = JSON.parse(texto);
                                        if (resultado.success) {
                                            mostrarMensagem("success", "Consulta cancelada. Agora você pode reagendar.");

                                            setTimeout(() => {
                                                window.location.href = "../index.html";
                                            }, 1500);
                                        } else {
                                            mostrarMensagem("error", "Erro no banco: " + resultado.error);
                                        }
                                    } catch (erroJson) {
                                        console.error("Resposta do servidor não era um JSON válido:", texto);
                                        mostrarMensagem("error", "Erro interno do servidor.");
                                    }
                                })
                                .catch(err => {
                                    console.error("Erro na requisição:", err);
                                    mostrarMensagem("error", "Erro de comunicação com o servidor.");
                                });
                        }
                    });
                });
        })
        .catch(err => {
            console.error("Erro ao buscar consultas:", err);
            containerAtivas.innerHTML = "<p>Erro ao carregar consultas. Tente novamente mais tarde.</p>";
        });
});
