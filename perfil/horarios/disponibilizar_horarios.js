const form = document.getElementById('formHorario');

const mensagem = document.getElementById('mensagem');

form.addEventListener('submit', function(e) {

    e.preventDefault();

    const dia_semana =
        document.getElementById('dia_semana').value;

    const horario =
        document.getElementById('horario').value;

    fetch('salvar_horario.php', {

        method: 'POST',

        headers: {
            'Content-Type':
                'application/x-www-form-urlencoded'
        },

        body:
            `dia_semana=${dia_semana}&horario=${horario}`

    })

    .then(response => response.text())

    .then(resultado => {

        mensagem.innerHTML = `

            <div style="
                background: #d4edda;
                color: #155724;
                padding: 15px;
                border-radius: 8px;
                margin-top: 15px;
            ">

                ${resultado}

            </div>

        `;

        form.reset();

    })

    .catch(error => {

        console.error('Erro:', error);

        mensagem.innerHTML = `

            <div style="
                background: #f8d7da;
                color: #721c24;
                padding: 15px;
                border-radius: 8px;
                margin-top: 15px;
            ">

                Erro ao salvar horário.

            </div>

        `;

    });

});