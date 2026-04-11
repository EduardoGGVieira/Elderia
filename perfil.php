<?php
// Inicia a sessão pra saber quem tá logado
session_start();

// Puxa a conexão que a gente já deixou pronta
require_once 'conexao.php';

// Pega o ID do profissional que veio pela URL (ex: perfil.php?id=5)
$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("Ué, cadê o ID do profissional? Deu erro aqui.");
}

// SQL básico pra pegar os dados do cara no banco
// A gente junta a tabela 'usuario' com a 'profissional' pelo ID
$sql = "SELECT u.nome, u.email, p.especialidade, p.biografia, p.localizacao 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 
        WHERE u.id_usuario = ?";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_prof);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$prof = mysqli_fetch_assoc($resultado);

// Se não achou o profissional, avisa
if (!$prof) {
    die("Profissional não encontrado no banco!");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo $prof['nome']; ?></title>
    <link rel="stylesheet" href="index.css">
    <style>
        .perfil-box { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-agendar { background: #00a6ce; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; }
        .formulario-agendar { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body style="background: #f4f7f6;">

    <div class="perfil-box">
        <a href="index.html" style="text-decoration: none; color: #00a6ce;">← Voltar para a Home</a>
        
        <h1 style="margin-top: 20px;"><?php echo $prof['nome']; ?></h1>
        <p style="color: #00a6ce; font-weight: bold;"><?php echo $prof['especialidade']; ?></p>
        
        <div style="margin-top: 20px;">
            <strong>Sobre:</strong>
            <p><?php echo $prof['biografia'] ?: "Nenhuma biografia cadastrada."; ?></p>
        </div>

        <div style="margin-top: 10px;">
            <strong>Localização:</strong>
            <p><?php echo $prof['localizacao'] ?: "Não informado."; ?></p>
        </div>

        <!-- Parte de agendar a consulta -->
        <div class="formulario-agendar">
            <h3>Agendar uma Consulta</h3>
            
            <?php if (isset($_SESSION['id'])): ?>
                <!-- Se tiver logado, mostra o form de agendamento -->
                <form action="agendar.php" method="POST">
                    <input type="hidden" name="id_profissional" value="<?php echo $id_prof; ?>">
                    
                    <label>Escolha a Data e Hora:</label><br>
                    <input type="datetime-local" name="data_hora" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc;">
                    
                    <button type="submit" class="btn-agendar">Confirmar Agendamento</button>
                    <p style="font-size: 0.8rem; color: #777; margin-top: 10px;">* Seus dados de Idoso serão enviados para o profissional.</p>
                </form>
            <?php else: ?>
                <!-- Se não tiver logado, manda pro login -->
                <p style="color: red;"><strong>Atenção:</strong> Você precisa estar logado para agendar.</p>
                <a href="conta/login/login.html" style="color: #00a6ce;">Clique aqui para fazer login</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
