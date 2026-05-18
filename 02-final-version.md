# CSI606-2026-01 - Remoto - Trabalho Final - Resultados

*Discente: Cauã Bandeira Nobre*

### Resumo

O QualiBoardBR é um Sistema de Controle de Bugs desenvolvido para auxiliar empresas no gerenciamento de múltiplas falhas de software distribuídas em diferentes projetos. A aplicação centraliza o registro e o acompanhamento de ocorrências, permitindo que as equipes visualizem e atualizem o status dos bugs de forma organizada. O sistema conta com controle de acesso (login) e um fluxo completo de cadastro e manutenção de projetos e suas respectivas falhas.

### 1. Tecnologias utilizadas - Backend e Frontend

- **Backend:** PHP puro (utilizando a extensão PDO para comunicação segura com o banco de dados).
- **Frontend:** HTML5 e CSS3 (estruturação das páginas e estilização, conforme o arquivo `style.css`).
- **Banco de Dados:** MySQL.
- **Ambiente de Desenvolvimento:** Servidor local Apache e MariaDB/MySQL providos pelo XAMPP.

### 2. Funcionalidades implementadas

- **Autenticação de Usuários:** Sistema de login e logout (`login.php`, `valida_login.php`, `logout.php`) com controle de sessão para proteger o acesso ao painel.
- **Gestão de Projetos:** Tela para listagem, cadastro e gerenciamento dos projetos da empresa (`projetos.php`).
- **Gerenciamento de Bugs (CRUD):** Formulários para registrar novas falhas vinculadas a um projeto específico (`form_bug.php`) e scripts para processar a inserção, edição e exclusão no banco de dados (`acao_bug.php`).
- **Setup de Banco de Dados:** Script utilitário (`setup.php`) criado para facilitar a criação do banco de dados `qualiboard_br` e das tabelas necessárias de forma automatizada.

### 3. Funcionalidades previstas e não implementadas

- Geração de relatórios em PDF com o resumo de bugs resolvidos na semana.
- Envio automático de e-mail para a equipe quando um bug de prioridade "Alta" for registrado.

### 4. Outras funcionalidades implementadas

- **Proteção de rotas:** Redirecionamento automático de usuários não autenticados que tentam acessar as páginas internas do sistema.
- Tratamento de exceções no banco de dados (visível no bloco `try-catch` da conexão PDO) para evitar a exibição de erros críticos aos usuários.

### 5. Principais desafios e dificuldades

- Estruturar o relacionamento entre as tabelas no banco de dados MySQL (garantindo que a chave estrangeira do projeto no cadastro de bugs funcionasse corretamente).
- Configurar e gerenciar o estado das sessões (`$_SESSION`) no PHP para manter o usuário logado com segurança.
- Organizar a separação de responsabilidades no PHP estruturado, dividindo arquivos de visão (HTML/Formulários) e arquivos de processamento de dados (ações do CRUD).

### 6. Instruções para instalação e execução

1. Certifique-se de ter o **XAMPP** (ou pacote similar como WAMP/MAMP) instalado em sua máquina.
2. Inicie os serviços do **Apache** e do **MySQL** no painel de controle do XAMPP.
3. Clone ou copie a pasta do projeto `qualiboardbr` para dentro do diretório `htdocs` (geralmente localizado em `C:\xampp\htdocs\`).
4. Abra o navegador e acesse `http://localhost/qualiboardbr/setup.php` para que o sistema crie automaticamente o banco de dados e as tabelas necessárias.
5. Após o setup, acesse `http://localhost/qualiboardbr/` para visualizar a tela inicial/login do sistema.

### 7. Referências

IBM. **Teste de software**: o que é teste de software e como ele funciona? IBM Think Topics, [s. d.]. Disponível em: <https://www.ibm.com/br-pt/think/topics/software-testing>. Acesso em: 18 maio 2026.

PRESSMAN, Roger S.; MAXIM, Bruce R. **Engenharia de software**: uma abordagem profissional. 8. ed. Porto Alegre: AMGH, 2016.

THE PHP GROUP. **PHP: Hypertext Preprocessor - Manual**. Disponível em: <https://www.php.net/manual/pt_BR/>. Acesso em: 18 maio 2026.
