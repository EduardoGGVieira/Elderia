<?php
session_start(); // Iniciar a sessão

$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("ID do profissional não fornecido.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Avaliar Profissional</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        referrerpolicy="no-referrer" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="avaliar.css">
</head>

<body>

    <div
        style="max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; font-family: sans-serif;">

        <a href="../perfil.php?id=<?php echo htmlspecialchars($id_prof); ?>"
            style="display: block; text-align: left; margin-bottom: 15px; color: #00a6ce; text-decoration: none;">←
            Voltar ao perfil</a>

        <h2>Deixe sua Avaliação</h2>

        <?php
        // Imprimir a mensagem de erro ou sucesso salvo na sessão
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>

        <form method="POST" action="../processa.php">

            <!-- Campo oculto com o ID do profissional avaliado -->
            <input type="hidden" name="id_profissional" value="<?php echo htmlspecialchars($id_prof); ?>">

            <div class="estrelas">

                <!-- Carrega o formulário definindo nenhuma estrela selecionada -->
                <input type="radio" name="estrela" id="vazio" value="" checked>

                <!-- Opções para selecionar as estrelas -->
                <label for="estrela_um"><i class="opcao fa"></i></label>
                <input type="radio" name="estrela" id="estrela_um" value="1">

                <label for="estrela_dois"><i class="opcao fa"></i></label>
                <input type="radio" name="estrela" id="estrela_dois" value="2">

                <label for="estrela_tres"><i class="opcao fa"></i></label>
                <input type="radio" name="estrela" id="estrela_tres" value="3">

                <label for="estrela_quatro"><i class="opcao fa"></i></label>
                <input type="radio" name="estrela" id="estrela_quatro" value="4">

                <label for="estrela_cinco"><i class="opcao fa"></i></label>
                <input type="radio" name="estrela" id="estrela_cinco" value="5"><br><br>

                <!-- Campo para enviar a mensagem -->
                <textarea name="mensagem" rows="4"
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; box-sizing: border-box;"
                    placeholder="O que achou deste profissional?"></textarea><br><br>

                <!-- Botão para enviar os dados do formulário -->
                <input type="submit" value="Enviar Avaliação"
                    style="background: #00a6ce; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%;">

            </div>

        </form>
    </div>

</body>

</html>