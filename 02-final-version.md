# CSI606-2026-01 - Remoto - Trabalho Final - Resultados

*Discente: Cauã Bandeira Nobre*

### Resumo

O **QualiBoardBR** é um Sistema de Controle de Bugs desenvolvido para auxiliar empresas no gerenciamento de múltiplas falhas de software distribuídas em diferentes projetos. A aplicação centraliza o registro e o acompanhamento de ocorrências, permitindo que as equipes visualizem e atualizem o status dos bugs de forma organizada. O sistema conta com controle de acesso (login) e um fluxo completo de cadastro e manutenção de projetos e suas respectivas falhas. 

**Visão de Mercado e Arquitetura:** O projeto foi idealizado com uma mentalidade de produto *SaaS (Software as a Service)*. Embora no atual escopo acadêmico o banco de dados seja unificado, a arquitetura de negócios foi pensada para que diferentes empresas possam assinar a plataforma. Cada empresa teria seu próprio ambiente isolado (ou *tenant*), garantindo que as equipes visualizem apenas os projetos e bugs referentes à sua própria organização, sem cruzamento de dados sensíveis entre clientes distintos.

### 1. Tecnologias utilizadas - Backend e Frontend

- **Backend:** PHP puro (utilizando a extensão PDO para comunicação segura).
- **Frontend:** HTML5, CSS3 personalizado (Estilo Soft Light) e **Bootstrap 5**.
- **Dashboard e Gráficos:** Integração com **Chart.js** para análise de dados e geração de gráficos dinâmicos.
- **Banco de Dados:** MySQL.
- **Ambiente de Desenvolvimento:** Servidor local Apache e MySQL providos pelo XAMPP.

### 2. Funcionalidades implementadas

- **Autenticação de Usuários:** Sistema de login e logout (`login.php`, `valida_login.php`, `logout.php`) com controle de sessão e interface moderna em *Split-Screen*.
- **Gestão de Projetos e Equipes:** Tela para listagem e cadastro de projetos (`projetos.php`). Implementação de relação N:M (Muitos-para-Muitos), onde usuários solicitam acesso a projetos específicos e o Administrador aprova, garantindo controle restrito de visão.
- **Gerenciamento de Bugs (CRUD):** Formulários para registrar novas falhas vinculadas a um projeto, incluindo suporte a **upload de evidências** (imagens e vídeos).
- **Dashboard Analítico:** Painel principal (`index.php`) com indicadores de desempenho (KPIs) dinâmicos e gráficos que reagem aos filtros aplicados pelo usuário.
- **Setup e Povoamento Automático:** Scripts utilitários (`setup.php` e `povoar.php`) desenvolvidos para automatizar a criação estrutural do banco de dados e gerar uma massa de dados fictícia para testes (Projetos, Usuários e Bugs).

### 3. Funcionalidades previstas e não implementadas

- Geração de relatórios em PDF com o resumo de bugs resolvidos na semana.
- Envio automático de e-mail corporativo para a equipe técnica quando um bug de prioridade "Alta" for registrado.

### 4. Outras funcionalidades implementadas

- **Proteção de rotas:** Redirecionamento automático de usuários não autenticados que tentam acessar as páginas internas do sistema e travas de segurança para usuários comuns.
- **Criptografia:** Senhas de usuários armazenadas no banco de dados com algoritmos de hash seguros nativos do PHP (`password_hash`).

### 5. Principais desafios e dificuldades

- Estruturar e aplicar na prática o relacionamento N:M (Muitos-para-Muitos) entre a tabela de `usuarios` e `projetos`, utilizando a tabela intermediária `usuario_projeto` para gerenciar as permissões de acesso da equipe.
- Configurar e gerenciar o estado das sessões (`$_SESSION`) no PHP para manter o usuário logado com segurança.
- Organizar a separação de responsabilidades no PHP estruturado, aliando o processamento complexo de dados no backend com uma interface frontend (CSS/UI) extremamente refinada e moderna.

### 6. Instruções para instalação e execução

1. Certifique-se de ter o **XAMPP** (ou pacote similar como WAMP/MAMP) instalado em sua máquina.
2. Inicie os serviços do **Apache** e do **MySQL** no painel de controle do XAMPP.
3. Clone ou copie a pasta do projeto `qualiboardbr` para dentro do diretório `htdocs` (geralmente localizado em `C:\xampp\htdocs\qualiboardbr\`).
4. Abra o navegador e acesse `http://localhost/qualiboardbr/setup.php` para que o sistema crie automaticamente o banco de dados `qualiboard_br` e as 4 tabelas estruturais necessárias.
5. *Opcional:* Em seguida, acesse `http://localhost/qualiboardbr/povoar.php` para que o script insira uma carga de dados iniciais para testes (30 usuários, 20 projetos e 100 bugs).
6. Acesse `http://localhost/qualiboardbr/` para visualizar a tela inicial/login do sistema. Caso tenha usado o script de povoamento, o login de administrador padrão é `caua@empresa.com.br` com a senha `123456`.

### 7. Referências

IBM. **Teste de software**: o que é teste de software e como ele funciona? IBM Think Topics, [s. d.]. Disponível em: <https://www.ibm.com/br-pt/think/topics/software-testing>. Acesso em: 18 maio 2026.

PRESSMAN, Roger S.; MAXIM, Bruce R. **Engenharia de software**: uma abordagem profissional. 8. ed. Porto Alegre: AMGH, 2016.

THE PHP GROUP. **PHP: Hypertext Preprocessor - Manual**. Disponível em: <https://www.php.net/manual/pt_BR/>. Acesso em: 18 maio 2026.
