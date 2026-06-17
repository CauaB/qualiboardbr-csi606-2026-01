<?php
// ATENÇÃO: ESTE SCRIPT APAGA TODOS OS DADOS ATUAIS DO BANCO!
require_once 'config/conexao.php';

echo "<h3>Iniciando repovoamento do banco de dados...</h3>";

try {
    // 1. DESATIVAR CHECAGEM DE CHAVES ESTRANGEIRAS PARA PODER LIMPAR AS TABELAS
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 2. LIMPAR TODAS AS TABELAS
    $pdo->exec("TRUNCATE TABLE bugs");
    $pdo->exec("TRUNCATE TABLE usuario_projeto");
    $pdo->exec("TRUNCATE TABLE projetos");
    $pdo->exec("TRUNCATE TABLE usuarios");
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Tabelas limpas com sucesso.<br>";

    // 3. GERAR A SENHA PADRÃO "123456" CRIPTOGRAFADA
    $senha_padrao = password_hash('123456', PASSWORD_DEFAULT);

    // 4. CRIAR 30 USUÁRIOS (O primeiro será Admin, os outros 29 Comuns)
    $nomes_usuarios = [
        'Cauã Bandeira', 'Mille Nobre', 'Sofia Nobre', 'Larinha', 'Carlos Silva', 
        'Ana Souza', 'Roberto Costa', 'Fernanda Lima', 'Lucas Alves', 'Amanda Pereira', 
        'Rafael Gomes', 'Juliana Castro', 'Bruno Martins', 'Camila Rocha', 'Diego Ribeiro', 
        'Letícia Carvalho', 'Rodrigo Mendes', 'Mariana Santos', 'Felipe Oliveira', 'Beatriz Araujo', 
        'Thiago Melo', 'Patrícia Cardoso', 'Marcelo Barbosa', 'Natália Dias', 'Gustavo Pinto', 
        'Carolina Teixeira', 'Ricardo Moraes', 'Vanessa Freitas', 'Eduardo Ramos', 'Larissa Moura'
    ];

    $ids_usuarios = [];
    $stmt_user = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
    
    foreach ($nomes_usuarios as $index => $nome) {
        // Gera um email baseado no primeiro nome (ex: caua@empresa.com.br)
        $email = strtolower(explode(' ', $nome)[0]) . "@empresa.com.br";
        // O primeiro usuário da lista é o Admin
        $perfil = ($index === 0) ? 'admin' : 'comum'; 
        
        $stmt_user->execute([$nome, $email, $senha_padrao, $perfil]);
        $ids_usuarios[] = $pdo->lastInsertId();
    }
    echo "30 Usuários criados (Senha de todos: 123456).<br>";

    // 5. CRIAR 20 PROJETOS
    $nomes_projetos = [
        'QualiBoardBR', 'Adote Fácil', 'Dashboard BI', 'TerraLAB Framework', 'Portal JM', 
        'API de Logística', 'App Delivery', 'ERP Financeiro', 'Sistema de RH', 'Portal Institucional', 
        'App Mobile iOS', 'E-commerce B2B', 'CRM de Vendas', 'Sistema de Biblioteca', 'Automação de Marketing', 
        'App de Saúde', 'Plataforma EAD', 'Gestão de Frota', 'Gateway de Pagamento', 'Analytics Data'
    ];

    $ids_projetos = [];
    $stmt_proj = $pdo->prepare("INSERT INTO projetos (nome, descricao, imagem) VALUES (?, ?, ?)");
    
    foreach ($nomes_projetos as $nome_proj) {
        $descricao = "Sistema desenvolvido para atender as demandas do projeto " . $nome_proj . " aplicando os melhores padrões.";
        $imagem = "https://ui-avatars.com/api/?name=" . urlencode($nome_proj) . "&background=random&color=fff&size=400";
        
        $stmt_proj->execute([$nome_proj, $descricao, $imagem]);
        $ids_projetos[] = $pdo->lastInsertId();
    }
    echo "20 Projetos criados.<br>";

    // 6. POPULAR BUGS (5 PARA CADA PROJETO) E EQUIPES (5 USUÁRIOS POR PROJETO)
    $prioridades = ['Baixa', 'Média', 'Alta'];
    $status_list = ['Aberto', 'Em Correção', 'Corrigido'];
    
    $stmt_bug = $pdo->prepare("INSERT INTO bugs (projeto_id, titulo, descricao, passos_reproducao, prioridade, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_equipe = $pdo->prepare("INSERT INTO usuario_projeto (usuario_id, projeto_id, status) VALUES (?, ?, 'aprovado')");

    foreach ($ids_projetos as $proj_id) {
        
        // --- Inserir 5 Bugs ---
        for ($i = 1; $i <= 5; $i++) {
            $titulo = "Falha técnica no módulo " . rand(1, 99);
            $desc = "Durante a execução dos testes, o sistema apresentou um comportamento inesperado na tela principal.";
            $passos = "1. Abrir o sistema\n2. Clicar no botão X\n3. Observar o erro";
            $prio = $prioridades[array_rand($prioridades)];
            $stat = $status_list[array_rand($status_list)];
            
            $stmt_bug->execute([$proj_id, $titulo, $desc, $passos, $prio, $stat]);
        }

        // --- Inserir 5 Usuários Aleatórios na Equipe deste projeto ---
        // Embaralha a lista de IDs de usuários e pega os 5 primeiros
        shuffle($ids_usuarios);
        $equipe_selecionada = array_slice($ids_usuarios, 0, 5);
        
        foreach ($equipe_selecionada as $user_id) {
            $stmt_equipe->execute([$user_id, $proj_id]);
        }
    }
    echo "100 Bugs inseridos (5 por projeto).<br>";
    echo "Equipes distribuídas (5 usuários por projeto).<br>";

    echo "<hr><h2 style='color:green;'>Sucesso! O banco foi repovoado.</h2>";
    echo "<p><strong>Seu login de Administrador:</strong> caua@empresa.com.br <br><strong>Senha:</strong> 123456</p>";
    echo "<a href='login.php'>Ir para a tela de Login</a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Erro no banco de dados: " . $e->getMessage() . "</h3>";
}
?>