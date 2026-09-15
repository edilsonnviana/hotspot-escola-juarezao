<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($senha, $admin['senha_hash'])) {
            $_SESSION['admin_logado'] = true;
            $_SESSION['usuario'] = $admin['usuario'];
            header("Location: admin_painel.php");
            exit;
        } else {
            $erro = "Usuário ou senha inválidos.";
        }
    } catch (\PDOException $e) {
        $erro = "Erro no sistema. Tente novamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login da Secretaria - Escola Técnica Juarezão</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container { border-top-color: var(--cor-primaria); }
        .erro { background: #fde8e8; color: #c53030; padding: 12px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid #f8b4b4; text-align: left; }
    </style>
</head>
<body>
    <div class="login-container">
 <img src="img/logo-juarezao.png" alt="Logo Escola" class="logo-img" onerror="this.style.display='none'">
        <h2>Área Restrita - Secretaria</h2>
        <?php if (!empty($erro)): ?>
            <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Digite o usuário">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Digite a senha">
            </div>
            <button type="submit" class="login-btn">Entrar no Sistema</button>
        </form>

        <div class="footer-links">
            <a href="login.php">← Voltar para o login dos alunos</a>
        </div>
    </div>
</body>
</html>