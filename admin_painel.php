<?php
session_start();
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once 'conexao.php';

$mensagem = '';
$erro = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_aluno'])) {
        $matriculaOriginal = trim($_POST['matricula_original'] ?? '');
        $matricula = trim($_POST['matricula']);
        $cpf = trim($_POST['cpf']);
        $nome = mb_strtoupper(trim($_POST['nome']), 'UTF-8');
        $turma = trim($_POST['turma']);
        $status = $_POST['status'] ?? 'ativo';

        if (!empty($matricula) && !empty($cpf) && !empty($nome)) {
            if (!empty($matriculaOriginal)) {
                $stmt = $pdo->prepare("UPDATE alunos_autorizados SET matricula = ?, cpf = ?, nome = ?, turma = ?, status = ? WHERE matricula = ?");
                $stmt->execute([$matricula, $cpf, $nome, $turma, $status, $matriculaOriginal]);
                $mensagem = "Dados do aluno atualizados com sucesso!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO alunos_autorizados (matricula, cpf, nome, turma, status) VALUES (?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE nome = VALUES(nome), turma = VALUES(turma), status = VALUES(status)");
                $stmt->execute([$matricula, $cpf, $nome, $turma, $status]);
                $mensagem = "Aluno cadastrado/atualizado com sucesso!";
            }
        } else {
            $erro = "Preencha todos os campos obrigatórios do aluno.";
        }
    }

    if (isset($_GET['alternar_status'])) {
        $matAlt = $_GET['alternar_status'];
        $stmtStatus = $pdo->prepare("UPDATE alunos_autorizados SET status = IF(status = 'ativo', 'inativo', 'ativo') WHERE matricula = ?");
        $stmtStatus->execute([$matAlt]);
        header("Location: admin_painel.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_admin'])) {
        $novoUsuario = trim($_POST['novo_usuario']);
        $novaSenha = trim($_POST['nova_senha']);

        if (!empty($novoUsuario) && !empty($novaSenha)) {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            $stmtAdmin = $pdo->prepare("INSERT INTO usuarios_admin (usuario, senha_hash) VALUES (?, ?)
                                        ON DUPLICATE KEY UPDATE senha_hash = VALUES(senha_hash)");
            $stmtAdmin->execute([$novoUsuario, $senhaHash]);
            $mensagem = "Novo usuário administrador cadastrado/atualizado com sucesso!";
        } else {
            $erro = "Preencha o usuário e a senha para o administrador.";
        }
    }

    $alunoEdicao = null;
    if (isset($_GET['editar'])) {
        $matEdit = $_GET['editar'];
        $stmtEdit = $pdo->prepare("SELECT * FROM alunos_autorizados WHERE matricula = ? LIMIT 1");
        $stmtEdit->execute([$matEdit]);
        $alunoEdicao = $stmtEdit->fetch(PDO::FETCH_ASSOC);
    }

    $stmtAlunos = $pdo->query("SELECT * FROM alunos_autorizados ORDER BY criado_em DESC LIMIT 100");
    $listaAlunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

    $stmtAdmins = $pdo->query("SELECT id, usuario, criado_em FROM usuarios_admin ORDER BY id ASC");
    $listaAdmins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    $erro = "Erro com o banco de dados: " . $e->getMessage();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Secretaria - Escola Técnica Juarezão</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <header class="admin-header">
            <h1>Painel de Gestão - Secretaria</h1>
            <div>
                <span style="margin-right: 15px; font-size: 0.9rem; color: #4a5568;">Logado como: <b><?php echo htmlspecialchars($_SESSION['usuario']); ?></b></span>
                <a href="admin_painel.php?logout=true" class="btn-sair">Sair</a>
            </div>
        </header>

        <?php if (!empty($mensagem)): ?>
            <div class="alert-sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if (!empty($erro)): ?>
            <div class="alert-erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- Seção 1: Cadastrar ou Editar Aluno -->
        <div class="form-section" style="<?php echo $alunoEdicao ? 'border-color: #3182ce; background-color: #ebf8ff;' : ''; ?>">
            <h3><?php echo $alunoEdicao ? 'Editando Aluno: ' . htmlspecialchars($alunoEdicao['nome']) : 'Cadastrar / Atualizar Aluno Individualmente'; ?></h3>
            <form action="admin_painel.php" method="POST">
                <input type="hidden" name="matricula_original" value="<?php echo htmlspecialchars($alunoEdicao['matricula'] ?? ''); ?>">

                <div class="grid-form">
                    <div class="form-group">
                        <label for="matricula">Matrícula</label>
                        <input type="text" id="matricula" name="matricula" required value="<?php echo htmlspecialchars($alunoEdicao['matricula'] ?? ''); ?>" placeholder="Ex: 2026001">
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" required value="<?php echo htmlspecialchars($alunoEdicao['cpf'] ?? ''); ?>" placeholder="Ex: 123.456.789-00">
                    </div>
                    <div class="form-group full">
                        <label for="nome">Nome Completo do Aluno</label>
                        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($alunoEdicao['nome'] ?? ''); ?>" placeholder="Nome completo">
                    </div>
                    <div class="form-group">
                        <label for="turma">Turma / Curso</label>
                        <input type="text" id="turma" name="turma" value="<?php echo htmlspecialchars($alunoEdicao['turma'] ?? ''); ?>" placeholder="Ex: Técnico em Informática">
                    </div>
                    <div class="form-group">
                        <label for="status">Status de Acesso na Rede</label>
                        <select id="status" name="status">
                            <option value="ativo" <?php echo (isset($alunoEdicao['status']) && $alunoEdicao['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo (Com acesso)</option>
                            <option value="inativo" <?php echo (isset($alunoEdicao['status']) && $alunoEdicao['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo (Bloqueado)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="cadastrar_aluno" class="login-btn" style="margin-top: 15px;"><?php echo $alunoEdicao ? 'Salvar Alterações' : 'Salvar Aluno na Base'; ?></button>
                <?php if ($alunoEdicao): ?>
                    <a href="admin_painel.php" class="btn-cancelar">Cancelar Edição</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Seção 2: Gerenciar Usuários Administradores -->
        <div class="form-section">
            <h3>Gerenciar Usuários Administradores (Secretaria)</h3>
            <form action="admin_painel.php" method="POST">
                <div class="grid-form">
                    <div class="form-group">
                        <label for="novo_usuario">Nome de Usuário</label>
                        <input type="text" id="novo_usuario" name="novo_usuario" required placeholder="Ex: secretaria_joao">
                    </div>
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" required placeholder="Digite a nova senha">
                    </div>
                </div>
                <button type="submit" name="cadastrar_admin" class="login-btn btn-admin" style="margin-top: 15px;">Salvar / Alterar Administrador</button>
            </form>

            <h4 style="margin-top: 20px; margin-bottom: 10px; font-size: 0.95rem; color: #2d3748;">Administradores com Acesso ao Sistema:</h4>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaAdmins as $adm): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($adm['id']); ?></td>
                                <td><b><?php echo htmlspecialchars($adm['usuario']); ?></b></td>
                                <td><?php echo htmlspecialchars($adm['criado_em']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Seção 3: Lista de Alunos -->
        <h3>Alunos Cadastrados no Sistema</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>CPF</th>
                        <th>Nome</th>
                        <th>Turma</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listaAlunos)): ?>
                        <?php foreach ($listaAlunos as $aluno): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($aluno['matricula']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['cpf']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['turma']); ?></td>
                                <td>
                                    <?php if ($aluno['status'] === 'ativo'): ?>
                                        <span class="badge-ativo">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge-inativo">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin_painel.php?editar=<?php echo urlencode($aluno['matricula']); ?>" class="acoes-link acoes-editar">Editar</a>
                                    <a href="admin_painel.php?alternar_status=<?php echo urlencode($aluno['matricula']); ?>" class="acoes-link acoes-status" onclick="return confirm('Deseja alterar o status de acesso deste aluno?');">
                                        <?php echo ($aluno['status'] === 'ativo') ? 'Desativar' : 'Ativar'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #718096;">Nenhum aluno cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>