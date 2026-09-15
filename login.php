<?php
require_once 'conexao.php';

$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrada = trim($_POST['documento']);
    $ip = $_POST['ip'] ?? $_SERVER['REMOTE_ADDR'];
    $mac = $_POST['mac'] ?? 'N/D';

    $entradaLimpa = preg_replace('/\D/', '', $entrada);

    $cpfFormatado = $entrada;
    if (strlen($entradaLimpa) === 11) {
        $cpfFormatado = substr($entradaLimpa, 0, 3) . '.' . 
                        substr($entradaLimpa, 3, 3) . '.' . 
                        substr($entradaLimpa, 6, 3) . '-' . 
                        substr($entradaLimpa, 9, 2);
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM alunos_autorizados 
                               WHERE (matricula = ? OR cpf = ? OR cpf = ?) 
                               AND status = 'ativo' LIMIT 1");
        $stmt->execute([$entrada, $cpfFormatado, $entradaLimpa]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($aluno) {
            $logStmt = $pdo->prepare("INSERT INTO hotspot_conexoes (matricula, ip_cliente, mac_cliente) VALUES (?, ?, ?)");
            $logStmt->execute([$aluno['matricula'], $ip, $mac]);

            $linkLogin = $_POST['link-login'] ?? '';
            $linkOrig = $_POST['link-orig'] ?? 'http://www.google.com';

            if (!empty($linkLogin)) {
                header("Location: {$linkLogin}?username=" . urlencode($aluno['matricula']) . "&dst=" . urlencode($linkOrig));
                exit;
            } else {
                echo "<script>alert('Acesso liberado com sucesso, " . $aluno['nome'] . "!'); window.location.href='http://www.google.com';</script>";
                exit;
            }
        } else {
            $mensagemErro = "Matrícula ou CPF não encontrados na base de dados ou inativos. Por favor, procure a secretaria da escola.";
        }
    } catch (\PDOException $e) {
        $mensagemErro = "Erro ao processar a autenticação. Tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso à Internet - Escola Técnica Deputado Juarezão</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="login-container">
        <div class="logo-wrapper">
            <img src="img/logo-juarezao.png" alt="Logo Escola" class="logo-img" onerror="this.style.display='none'">
            <div class="school-title">CEP Escola Técnica</div>
            <div class="school-subtitle">Deputado Juarezão - Brazlândia-DF</div>
        </div>

        <h2>Acesso à Internet</h2>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($mensagemErro); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="link-login" value="<?php echo htmlspecialchars($_GET['link-login'] ?? ''); ?>">
            <input type="hidden" name="link-orig" value="<?php echo htmlspecialchars($_GET['link-orig'] ?? ''); ?>">
            <input type="hidden" name="mac" value="<?php echo htmlspecialchars($_GET['mac'] ?? ''); ?>">
            <input type="hidden" name="ip" value="<?php echo htmlspecialchars($_GET['ip'] ?? ''); ?>">

            <div class="form-group">
                <label for="documento">Matrícula ou CPF</label>
                <input type="text" id="documento" name="documento" required placeholder="Digite sua Matrícula ou CPF">
            </div>

            <button type="submit" class="login-btn">Conectar à Internet</button>
        </form>

        <div class="footer-links">
            <a href="admin_login.php">Área da Secretaria</a>
        </div>

        <div class="footer-info">
            Sistema de Controle de Rede Escolar<br>
            Secretaria de Educação - GDF
        </div>
    </div>

</body>
</html>