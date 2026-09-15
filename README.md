# Portal Captive (Hotspot) - Escola Técnica Deputado Juarezão

Sistema de controle e liberação de acesso à internet para alunos integrado ao roteador MikroTik, validando matrículas e CPFs contra uma base de dados oficial e oferecendo um painel administrativo para a secretaria.

## 🚀 Funcionalidades
* **Portal de Acesso do Aluno:** Tela de login customizada com a identidade visual da escola. O sistema valida se o CPF/Matrícula consta na base de dados ativa antes de liberar a navegação.
* **Painel da Secretaria:** Área administrativa protegida por senha criptografada onde a equipe pode gerenciar cadastros de alunos de forma individual.
* **Segurança e Auditoria:** Registro automático de logs de conexão vinculados à matrícula do aluno.

## 📂 Estrutura do Projeto
- `login.php`: Interface e lógica de validação do aluno.
- `admin_login.php`: Tela de autenticação da secretaria.
- `admin_painel.php`: Painel gerencial de alunos.
- `css/style.css`: Folha de estilos responsiva.
- `database.sql`: Script de estruturação do banco de dados MySQL/MariaDB.