const form = document.getElementById('formHorario');

const mensagem =
    document.getElementById('mensagem');


form.addEventListener('submit', function(e) {

    e.preventDefault();

    const data_hora =
        document.getElementById('data_hora').value;

    fetch('salvar_horario.php', {

        method: 'POST',

        headers: {
            'Content-Type':
            'application/x-www-form-urlencoded'
        },

        body:
            `data_hora=${data_hora}`

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

    });

});