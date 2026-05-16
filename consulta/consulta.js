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
            // EVENTO DOS BOTÕES CORRIGIDO E BLINDADO
            document.querySelectorAll(".btn-reagendar")
                .forEach(botao => {

                    botao.addEventListener("click", () => {

                        const idConsulta = botao.dataset.consulta;

                        const mensagemConfirmacao = "tem certeza que dejesa remarcar esta consulta? caso de \"ok\" seu horario vai ser disponibilizado novamente e você poderá realizar outro agendamento";

                        if (confirm(mensagemConfirmacao)) {

                            // Usamos FormData que é mais seguro e limpo para o PHP receber via POST
                            const dadosEnviar = new FormData();
                            dadosEnviar.append('id_consulta', idConsulta);

                            fetch("deletar_consulta.php", {
                                method: "POST",
                                body: dadosEnviar // Envia como FormData padrão
                            })
                                .then(response => {
                                    // Se o PHP der erro 404 ou 500, capturamos aqui antes de quebrar
                                    if (!response.ok) {
                                        throw new Error(`Ficheiro não encontrado ou erro no servidor (Status: ${response.status})`);
                                    }
                                    return response.text(); // Lemos como texto primeiro para inspecionar erros ocultos
                                })
                                .then(texto => {
                                    try {
                                        const resultado = JSON.parse(texto);
                                        if (resultado.success) {
                                            // Redireciona para a página inicial (raiz do projeto)
                                            window.location.href = "../index.html";
                                        } else {
                                            alert("Erro no banco: " + resultado.error);
                                        }
                                    } catch (erroJson) {
                                        // Se o PHP retornar um erro HTML do XAMPP, vai cair aqui e mostrar o erro real
                                        console.error("Resposta do servidor não era um JSON válido:", texto);
                                        alert("Erro interno do PHP. Veja o Console (F12) para detalhes.");
                                    }
                                })
                                .catch(err => {
                                    console.error("Erro na requisição:", err);
                                    alert("Erro de comunicação: " + err.message);
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
