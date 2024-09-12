<?php
// Iniciar a sessão
session_start();
date_default_timezone_set('America/Sao_Paulo');
// Verificar se o usuário está autenticado
if (!isset($_SESSION['cpf'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
$mysqli = new mysqli("localhost", "root", "", "sistema_gcmsp");

// Verificar a conexão
if ($mysqli->connect_error) {
    die("Conexão falhou: " . $mysqli->connect_error);
}

// Obter o nome do usuário, IP de login e último acesso
$cpf = $_SESSION['cpf'];
$query = "SELECT nome, ip_login, ultimo_acesso, re, graduacao, qra FROM usuarios WHERE cpf = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    die("Erro na preparação da declaração: " . $mysqli->error);
}

$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Atualizar IP de login e último acesso
$ip_login = $_SERVER['REMOTE_ADDR'];
$ultimo_acesso = date('Y-m-d H:i:s');

$update_query = "UPDATE usuarios SET ip_login = ?, ultimo_acesso = ? WHERE cpf = ?";
$update_stmt = $mysqli->prepare($update_query);

if (!$update_stmt) {
    die("Erro na preparação da declaração de atualização: " . $mysqli->error);
}

$update_stmt->bind_param("sss", $ip_login, $ultimo_acesso, $cpf);
$update_stmt->execute();

// Obter as funcionalidades permitidas para o usuário
$usuario_id = $mysqli->query("SELECT id FROM usuarios WHERE cpf = '$cpf'")->fetch_assoc()['id'];
$funcionalidades_permitidas = [];
$result = $mysqli->query("SELECT funcionalidade_id FROM usuarios_funcionalidades WHERE usuario_id = $usuario_id");
while ($row = $result->fetch_assoc()) {
    $funcionalidades_permitidas[] = $row['funcionalidade_id'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema GMCSP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('uploads/digital_0.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: #343a40;
            color: whitesmoke;
            position: relative;
            z-index: 1;
            

        }
        .header h1 {
            margin: 0;
        }
        .logout-btn {
            color: white;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            color: #ffc107;
        }
        .dashboard-container {
            display: flex;
            justify-content: space-around;
            align-items: rigt;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 2rem;                 
            
        }
        .dashboard-icon {
            font-size: 3rem;
            width: 100px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 0px auto;
        }
        .dashboard-item {
            text-align: center;
            background: blue;
            background-color: rgba(70, 100, 200, 0.2); /* Fundo branco com transparência */
            margin-bottom: 1rem;
            flex: 2;
            max-width: 150px;
        }
        .dashboard-item a {
            text-decoration: none;
            color: black;
        }
        .dashboard-item:hover .dashboard-icon {
            transform: scale(2);
            transition: transform 0.5s ease;
        }
        .sidebar {
            position: fixed;
            top: 60px; /* Espaço para o cabeçalho */
            right: 0;
            width: 240px;
            background-color: white;
            border-width: 1px solid gray;
            padding: 20px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h4 {
            margin-bottom: 1rem;
        }
        .sidebar p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
<header class="header">
    <h1>Bem-vindo, <?php echo htmlspecialchars($user['nome']); ?></h1>
    <div>
        <a href="editar_cadastro.php" class="btn btn-warning">
            <i class="bi bi-pencil-square"></i> Editar
        </a>
        <a href="logout.php" class="btn btn-danger logout-btn ml-2">
            <i class="bi bi-box-arrow-right"></i> Sair
        </a>
    </div>
</header>

    <div class="container">
        <div class="dashboard-container">
            <!-- Controle de Viaturas -->
            <?php if (in_array(1, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="controle_viaturas.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <p>Controle de Viaturas</p>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Mapa Força -->
            <?php if (in_array(2, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="criar_mapa_forca.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <p>Mapa Força Diário</p>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Escalas -->
            <?php if (in_array(3, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="escala_mensal.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <p>Escalas Mensais de Serviço</p>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Gestão de Funcionalidades -->
            <?php if (in_array(4, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="gestao_funcionalidades.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <p>Gestão de Funcionalidades</p>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Gestão de Modalidades -->
            <?php if (in_array(3, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="gerenciar_modalidades_servico.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-sticky"></i>
                        </div>
                        <p>Modalidades de Serviços</p>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Manutenção de Viaturas -->
            <?php if (in_array(3, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="manutencao_viaturas.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-wrench-adjustable"></i>
                        </div>
                        <p>Manutenção de VTR's</p>
                    </a>
                </div>
            <?php endif; ?>
             <!-- Inclusão de Postos -->
             <?php if (in_array(3, $funcionalidades_permitidas)): ?>
                <div class="dashboard-item">
                    <a href="gerenciar_postos_servico.php" class="btn btn-outline-primary">
                        <div class="dashboard-icon">
                            <i class="bi bi-clipboard2-check"></i>
                        </div>
                        <p>Postos de Serviço</p>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <aside class="sidebar">
    <!-- Imagem do Usuário -->
    <div class="sidebar-header">
        
    </div>
    

    <h4>Usuário</h4>
    <p><strong>RE:</strong> <?php echo htmlspecialchars($user['re']); ?></p>
    <p><strong>Graduação:</strong> <?php echo htmlspecialchars($user['graduacao']); ?></p>
    <p><strong>Nome:</strong> <?php echo htmlspecialchars($user['qra']); ?></p>
    <p><strong>Horário Atual:</strong> <?php echo date('H:i:s'); ?></p>

    <hr>

    <h3>Links</h3>
    <div class="external-links">
        <a href="https://detecta.sp.gov.br" target="_blank" class="btn btn-primary btn-lg mt-2">
            <i class="bi bi-bricks"></i> Muralha SP
        </a>
        <a href="https://infocrim.ssp.sp.gov.br" target="_blank" class="btn btn-primary btn-lg mt-2">
            <i class="bi bi-graph-up"></i> Infocrim SP
        </a>
        <a href="https://detran.sp.gov.br" target="_blank" class="btn btn-primary btn-lg mt-2">
            <i class="bi bi-stoplights"></i> Detran - SP
        </a>
        <a href="https://cortex.mj.gov.br" target="_blank" class="btn btn-primary btn-lg mt-2">
            <i class="bi bi-globe"></i> Cortéx - MJ
        </a>
        
    </div>
    
</aside>


    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
