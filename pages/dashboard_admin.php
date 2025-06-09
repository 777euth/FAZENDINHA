<?php
include '../includes/header.php';
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING));
    $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $categoria = trim(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_STRING));

    if (empty($nome) || empty($usuario) || empty($senha) || empty($categoria)) {
        $error_message = "Todos os campos são obrigatórios.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error_message = "Erro: O usuário '$usuario' já está cadastrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (nome, usuario, senha, categoria) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $usuario, $senha, $categoria);
            $success = $stmt->execute();

            if (!$success) {
                $error_message = "Erro ao criar usuário: " . $stmt->error;
            } else {
                $success_message = "Usuário criado com sucesso!";
                header("Location: dashboard_admin.php");
                exit;
            }
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_evento'])) {
    $nome_evento = trim(filter_input(INPUT_POST, 'nome_evento', FILTER_SANITIZE_STRING));
    $perfil_id = !empty($_POST['perfil_id']) ? (int)$_POST['perfil_id'] : null;
    $passos = json_encode(explode("\n", trim($_POST['passos'] ?? '')));
    $campanhas = trim(filter_input(INPUT_POST, 'campanhas', FILTER_SANITIZE_STRING));
    $conta_suspensa = trim(filter_input(INPUT_POST, 'conta_suspensa', FILTER_SANITIZE_STRING));
    $google_aprovado = trim(filter_input(INPUT_POST, 'google_aprovado', FILTER_SANITIZE_STRING));
    $estado = trim(filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING));
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $objetivo = trim(filter_input(INPUT_POST, 'objetivo', FILTER_SANITIZE_STRING));
    $quantidade_dias = filter_input(INPUT_POST, 'quantidade_dias', FILTER_VALIDATE_INT) ?: 0;
    $campos_exibir = json_encode($_POST['campos_exibir'] ?? []);
    $campos_perfil_adicionar = json_encode($_POST['campos_perfil_adicionar'] ?? []);
    $exibir_arquivos = isset($_POST['exibir_arquivos']) ? 1 : 0;

    $custom_fields = [];
    for ($i = 1; $i <= 7; $i++) {
        $nome_campo = trim(filter_input(INPUT_POST, "custom_field_name_$i", FILTER_SANITIZE_STRING));
        $dados_campo = trim(filter_input(INPUT_POST, "custom_field_data_$i", FILTER_SANITIZE_STRING));
        if (!empty($nome_campo) && !empty($dados_campo)) {
            $custom_fields[] = ["nome" => $nome_campo, "dados" => $dados_campo];
        }
    }
    $campos_personalizados = json_encode($custom_fields);

    if (empty($nome_evento)) {
        $error_message = "O nome do evento é obrigatório.";
    } else {
        $stmt = $conn->prepare("INSERT INTO eventos (nome, passos, perfil_id, usuario_id, campanhas, conta_suspensa, google_aprovado, estado, status, objetivo, quantidade_dias, campos_exibir, campos_perfil_adicionar, campos_personalizados, exibir_arquivos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssssssisssi", $nome_evento, $passos, $perfil_id, $_SESSION['user_id'], $campanhas, $conta_suspensa, $google_aprovado, $estado, $status, $objetivo, $quantidade_dias, $campos_exibir, $campos_perfil_adicionar, $campos_personalizados, $exibir_arquivos);
        
        if ($stmt->execute()) {
            $evento_id = $conn->insert_id;
            
            if ($perfil_id) {
                $stmt = $conn->prepare("UPDATE perfis SET campanhas = ?, conta_suspensa = ?, google_aprovado = ?, estado = ?, status = ?, objetivo = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $campanhas, $conta_suspensa, $google_aprovado, $estado, $status, $objetivo, $perfil_id);
                $stmt->execute();

                $campos_adicionar = json_decode($campos_perfil_adicionar, true);
                if (!empty($campos_adicionar)) {
                    $update_fields = [];
                    $update_values = [];
                    $types = '';
                    if (in_array('email_profissional', $campos_adicionar)) {
                        $email_profissional = trim(filter_input(INPUT_POST, 'email_profissional', FILTER_SANITIZE_STRING));
                        $update_fields[] = "email_profissional = ?";
                        $update_values[] = $email_profissional;
                        $types .= 's';
                    }
                    if (in_array('novo_site', $campos_adicionar)) {
                        $novo_site = trim(filter_input(INPUT_POST, 'novo_site', FILTER_SANITIZE_STRING));
                        $update_fields[] = "novo_site = ?";
                        $update_values[] = $novo_site;
                        $types .= 's';
                    }
                    if (in_array('numero_google_ads', $campos_adicionar)) {
                        $numero_google_ads = trim(filter_input(INPUT_POST, 'numero_google_ads', FILTER_SANITIZE_STRING));
                        $update_fields[] = "numero_google_ads = ?";
                        $update_values[] = $numero_google_ads;
                        $types .= 's';
                    }
                    if (in_array('codigo_ativacao_g2', $campos_adicionar)) {
                        $codigo_ativacao_g2 = trim(filter_input(INPUT_POST, 'codigo_ativacao_g2', FILTER_SANITIZE_STRING));
                        $update_fields[] = "codigo_ativacao_g2 = ?";
                        $update_values[] = $codigo_ativacao_g2;
                        $types .= 's';
                    }
                    if (in_array('campo_adicional_1', $campos_adicionar)) {
                        $campo_adicional_1 = trim(filter_input(INPUT_POST, 'campo_adicional_1', FILTER_SANITIZE_STRING));
                        $update_fields[] = "campo_adicional_1 = ?";
                        $update_values[] = $campo_adicional_1;
                        $types .= 's';
                    }
                    if (in_array('campo_adicional_2', $campos_adicionar)) {
                        $campo_adicional_2 = trim(filter_input(INPUT_POST, 'campo_adicional_2', FILTER_SANITIZE_STRING));
                        $update_fields[] = "campo_adicional_2 = ?";
                        $update_values[] = $campo_adicional_2;
                        $types .= 's';
                    }

                    if (!empty($update_fields)) {
                        $update_query = "UPDATE perfis SET " . implode(', ', $update_fields) . " WHERE id = ?";
                        $stmt = $conn->prepare($update_query);
                        $types .= 'i';
                        $update_values[] = $perfil_id;
                        $stmt->bind_param($types, ...$update_values);
                        $stmt->execute();
                    }
                }

                $descricao = "Evento '" . htmlspecialchars($nome_evento) . "' associado ao perfil em " . date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO tarefas (perfil_id, descricao) VALUES (?, ?)");
                $stmt->bind_param("is", $perfil_id, $descricao);
                $stmt->execute();
            }

            $success_message = "Evento criado com sucesso!";
            header("Location: dashboard_admin.php");
            exit;
        } else {
            $error_message = "Erro ao criar evento: " . $stmt->error;
        }
        $stmt->close();
    }
}

$empresas = $conn->query("SELECT id, empresa, cnpj FROM empresas WHERE id NOT IN (SELECT empresa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$pessoas = $conn->query("SELECT id, nome FROM pessoas WHERE id NOT IN (SELECT pessoa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$emails = $conn->query("SELECT id, email FROM emails WHERE id NOT IN (SELECT email_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$perfis = $conn->query("SELECT id, nome_perfil FROM perfis WHERE perfil_criado = 1")->fetch_all(MYSQLI_ASSOC);

$colunas_perfil = [
    'nome_perfil' => 'Nome do Perfil',
    'empresa_nome' => 'Nome da Empresa',
    'cnpj' => 'CNPJ',
    'site_original' => 'Site Original',
    'telefone' => 'Telefone',
    'endereco' => 'Endereço',
    'cidade' => 'Cidade',
    'empresa_estado' => 'Estado (Empresa)',
    'cep' => 'CEP',
    'pessoa_nome' => 'Nome da Pessoa',
    'email_nome' => 'Email',
    'email_senha' => 'Senha do Email',
    'email_rec' => 'Email de Recuperação',
    'perfil_criado' => 'Perfil Criado',
    'google_aprovado' => 'Google Aprovado',
    'campanhas' => 'Campanhas',
    'conta_suspensa' => 'Conta Suspensa',
    'estado' => 'Estado',
    'status' => 'Status',
    'objetivo' => 'Objetivo',
    'email_profissional' => 'Email Profissional',
    'novo_site' => 'Novo Site',
    'numero_google_ads' => 'Número do Google Ads',
    'codigo_ativacao_g2' => 'Código de Ativação G2',
    'campo_adicional_1' => 'Campo Adicional 1',
    'campo_adicional_2' => 'Campo Adicional 2'
];
?>

<!-- Message Box -->
<div id="message-box" class="message-box" style="display: none;">
    <span id="message-text"><?php echo isset($error_message) ? htmlspecialchars($error_message) : (isset($success_message) ? htmlspecialchars($success_message) : ''); ?></span>
    <button onclick="closeMessageBox()">Fechar</button>
</div>

<!-- Abas do Dashboard -->
<div class="tabs">
    <button class="tab-link active" data-tab="gerenciamento" onclick="openAdminTab('gerenciamento')">Gerenciamento</button>
    <button class="tab-link" data-tab="graficos" onclick="openAdminTab('graficos')">Gráficos</button>
    <button class="tab-link" data-tab="pagamentos" onclick="openAdminTab('pagamentos')">Pagamentos</button>
</div>

<div id="tab-gerenciamento" class="tab-content" style="display: block;">

<!-- Botões Principais -->
<div class="button-group">
    <button onclick="openModal('modal-add-info')" class="btn destaque">+ Add Informações</button>
    <button id="criar-perfil" class="btn destaque" disabled onclick="openModal('modal-criar-perfil')">Criar Perfil</button>
    <button onclick="openModal('modal-criar-usuario')" class="btn criar-usuario">Criar Usuário</button>
    <button onclick="openModal('modal-criar-evento')" class="btn criar-evento">Criar Evento</button>
    <button onclick="window.location.href='dashboard_logins.php'" class="btn">Gerenciar Logins</button>
</div>

<!-- Modal Add Informações -->
<div id="modal-add-info" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-add-info')">×</span>
        <h2>Adicionar Informações</h2>
        <div class="button-group">
            <button onclick="openModal('modal-empresa')" class="btn">Adicionar Empresas</button>
            <button onclick="openModal('modal-pessoa')" class="btn">Adicionar Pessoas</button>
            <button onclick="openModal('modal-email')" class="btn">Adicionar Email</button>
            <button onclick="openModal('modal-opcoes')" class="btn">Adicionar Opções</button>
        </div>
    </div>
</div>

<!-- Modal Criar Usuário -->
<div id="modal-criar-usuario" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-criar-usuario')">×</span>
        <h2>Criar Usuário</h2>
        <form method="POST" action="">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <select name="categoria" required>
                <option value="">Selecione a Categoria</option>
                <option value="Admin">Admin</option>
                <option value="Cadastrar">Cadastrar</option>
                <option value="Fazendeiro">Fazendeiro</option>
            </select>
            <button type="submit" name="add_user">Criar Usuário</button>
        </form>
    </div>
</div>

<!-- Modal Criar Evento -->
<div id="modal-criar-evento" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-criar-evento')">×</span>
        <h2>Criar Evento</h2>
        <form method="POST" action="">
            <div class="eventos-layout-grid">
                <!-- Linha 1 -->
                <div class="eventos-col configuracoes-evento">
                    <h3>Configurações do Evento</h3>
                    <div class="form-row">
                        <label for="nome_evento">Nome do Evento:</label>
                        <input type="text" name="nome_evento" placeholder="Nome do Evento" required>
                    </div>
                    <div class="form-row">
                        <label for="perfil_id">Perfil Associado:</label>
                        <select name="perfil_id">
                            <option value="">Nenhum perfil</option>
                            <?php foreach ($perfis as $perfil): ?>
                                <option value="<?php echo $perfil['id']; ?>">
                                    <?php echo htmlspecialchars($perfil['nome_perfil'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="campanhas">Campanha:</label>
                        <select name="campanhas" required>
                            <option value="Pendente">Pendente</option>
                            <option value="Criado">Criado</option>
                            <option value="White">White</option>
                            <option value="Black">Black</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="conta_suspensa">Conta Suspensa:</label>
                        <select name="conta_suspensa" required>
                            <option value="Analisando">Analisando</option>
                            <option value="Não">Não</option>
                            <option value="Sim">Sim</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="google_aprovado">Google Aprovado:</label>
                        <select name="google_aprovado" required>
                            <option value="Pendente">Pendente</option>
                            <option value="Solicitado">Solicitado</option>
                            <option value="Aprovado">Aprovado</option>
                            <option value="Negado">Negado</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="estado">Estado:</label>
                        <select name="estado" required>
                            <option value="Aguardando">Aguardando</option>
                            <option value="Em Andamento">Em Andamento</option>
                            <option value="Concluído">Concluído</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="status">Status:</label>
                        <select name="status" required>
                            <option value="Ativa">Ativa</option>
                            <option value="Inativa">Inativa</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="objetivo">Objetivo:</label>
                        <select name="objetivo" required>
                            <option value="White">White</option>
                            <option value="Black">Black</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="quantidade_dias">Quantidade de Dias:</label>
                        <input type="number" name="quantidade_dias" placeholder="Dias" min="0" required>
                    </div>
                </div>
                <div class="eventos-col campos-exibir">
                    <h3>Campos a Exibir para o Fazendeiro</h3>
                    <div class="checkbox-list">
                        <?php foreach ($colunas_perfil as $coluna => $nome): ?>
                            <label>
                                <input type="checkbox" name="campos_exibir[]" value="<?php echo htmlspecialchars($coluna); ?>">
                                <?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="eventos-col passos-evento">
                    <h3>Passos do Evento</h3>
                    <textarea name="passos" placeholder="Passos do evento (um por linha)" required rows="10" class="passos-textarea"></textarea>
                </div>

                <!-- Linha 2 -->
                <div class="eventos-col campos-personalizados">
                    <h3>Campos Personalizados</h3>
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <div class="custom-field-row" id="custom-field-row-<?php echo $i; ?>" <?php echo $i > 1 ? 'style="display: none;"' : ''; ?>>
                            <div class="form-row">
                                <label for="custom_field_name_<?php echo $i; ?>">Nome do Campo:</label>
                                <input type="text" name="custom_field_name_<?php echo $i; ?>" id="custom_field_name_<?php echo $i; ?>" placeholder="Nome do Campo" oninput="showNextField(<?php echo $i; ?>)">
                            </div>
                            <div class="form-row">
                                <label for="custom_field_data_<?php echo $i; ?>">Dados do Campo:</label>
                                <input type="text" name="custom_field_data_<?php echo $i; ?>" id="custom_field_data_<?php echo $i; ?>" placeholder="Dados do Campo">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="eventos-col campos-perfil-adicionar">
                    <h3>Campos a Adicionar ao Perfil</h3>
                    <div class="checkbox-list">
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="email_profissional">
                            Email Profissional
                        </label><br>
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="novo_site">
                            Novo Site
                        </label><br>
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="numero_google_ads">
                            Número do Google Ads
                        </label><br>
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="codigo_ativacao_g2">
                            Código de Ativação G2
                        </label><br>
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="campo_adicional_1">
                            Campo Adicional 1
                        </label><br>
                        <label>
                            <input type="checkbox" name="campos_perfil_adicionar[]" value="campo_adicional_2">
                            Campo Adicional 2
                        </label><br>
                    </div>
                </div>
                <div class="eventos-col exibir-arquivos">
                    <div class="form-row">
                        <label for="exibir_arquivos">Exibir PDFs e Imagens:</label>
                        <input type="checkbox" name="exibir_arquivos" id="exibir_arquivos">
                    </div>
                </div>
            </div>
            <button type="submit" name="add_evento">Criar Evento</button>
        </form>
    </div>
</div>

<!-- Modal Empresa -->
<div id="modal-empresa" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-empresa')">×</span>
        <h2>Adicionar Empresa</h2>
        <form id="form-empresa" onsubmit="submitForm(event, 'empresa')" enctype="multipart/form-data">
            <input type="text" name="empresa" placeholder="Empresa" required>
            <input type="text" name="cnpj" placeholder="CNPJ" required>
            <input type="text" name="site_original" placeholder="Site Original">
            <input type="text" name="telefone" placeholder="Telefone">
            <input type="text" name="endereco" placeholder="Endereço">
            <input type="text" name="cidade" placeholder="Cidade">
            <input type="text" name="estado" placeholder="Estado">
            <input type="text" name="cep" placeholder="CEP">
            <label>PDF CNPJ:</label>
            <input type="file" name="pdf_cnpj" accept=".pdf">
            <label>PDF SUSEP:</label>
            <input type="file" name="pdf_susep" accept=".pdf">
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal Pessoa -->
<div id="modal-pessoa" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-pessoa')">×</span>
        <h2>Adicionar Pessoa</h2>
        <form id="form-pessoa" onsubmit="return validateForm(event, 'pessoa')" enctype="multipart/form-data">
            <input type="text" name="nome" placeholder="Nome" required>
            <label>RG Frente (JPG/PNG, máx. 2MB):</label>
            <input type="file" name="rg_frente" accept="image/jpeg,image/png" required>
            <label>RG Trás (JPG/PNG, máx. 2MB):</label>
            <input type="file" name="rg_tras" accept="image/jpeg,image/png" required>
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal Email -->
<div id="modal-email" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-email')">×</span>
        <h2>Adicionar Email</h2>
        <form id="form-email" onsubmit="submitForm(event, 'email')">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="senha" placeholder="Senha" required>
            <input type="email" name="email_rec" placeholder="Email Recuperação" required>
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal Opções -->
<div id="modal-opcoes" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-opcoes')">×</span>
        <h2>Adicionar Opções</h2>
        <form id="form-opcoes" onsubmit="submitForm(event, 'opcoes')">
            <input type="text" name="estado" placeholder="Estado">
            <input type="text" name="status" placeholder="Status">
            <input type="text" name="objetivo" placeholder="Objetivo">
            <input type="text" name="pasta_gologin" placeholder="Pasta GoLogin">
            <input type="text" name="evento" placeholder="Evento">
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal Criar Perfil -->
<div id="modal-criar-perfil" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-criar-perfil')">×</span>
        <h2>Criar Perfil</h2>
        <form id="form-criar-perfil" method="POST" action="../backend/save_perfil.php">
            <label>Selecione a Empresa:</label>
            <select name="empresa_id" required>
                <option value="">Selecione uma empresa</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?php echo $empresa['id']; ?>">
                        <?php echo htmlspecialchars($empresa['empresa'], ENT_QUOTES, 'UTF-8') . ' - CNPJ: ' . htmlspecialchars($empresa['cnpj'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Selecione a Pessoa:</label>
            <select name="pessoa_id" required>
                <option value="">Selecione uma pessoa</option>
                <?php foreach ($pessoas as $pessoa): ?>
                    <option value="<?php echo $pessoa['id']; ?>">
                        <?php echo htmlspecialchars($pessoa['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Selecione o Email:</label>
            <select name="email_id" required>
                <option value="">Selecione um email</option>
                <?php foreach ($emails as $email): ?>
                    <option value="<?php echo $email['id']; ?>">
                        <?php echo htmlspecialchars($email['email'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Selecione a Pasta GoLogin:</label>
            <select name="opcao_id" id="select-pasta-gologin" required>
                <option value="">Selecione uma pasta</option>
            </select>
            <button type="submit">Criar Perfil</button>
        </form>
    </div>
</div>

<!-- Modal Editar Empresa -->
<div id="modal-edit-empresa" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-empresa')">×</span>
        <h2>Editar Empresa</h2>
        <form id="form-edit-empresa" onsubmit="saveEditedItem(event, 'empresa')" enctype="multipart/form-data">
            <input type="hidden" name="id" id="empresa-id">
            <div class="form-row">
                <label for="empresa">Nome da Empresa:</label>
                <input type="text" name="empresa" id="empresa" placeholder="Empresa" required>
            </div>
            <div class="form-row">
                <label for="cnpj">CNPJ:</label>
                <input type="text" name="cnpj" id="cnpj" placeholder="CNPJ" required>
            </div>
            <div class="form-row">
                <label for="site_original">Site Original:</label>
                <input type="text" name="site_original" id="site_original" placeholder="Site Original">
            </div>
            <div class="form-row">
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" id="telefone" placeholder="Telefone">
            </div>
            <div class="form-row">
                <label for="endereco">Endereço:</label>
                <input type="text" name="endereco" id="endereco" placeholder="Endereço">
            </div>
            <div class="form-row">
                <label for="cidade">Cidade:</label>
                <input type="text" name="cidade" id="cidade" placeholder="Cidade">
            </div>
            <div class="form-row">
                <label for="estado">Estado:</label>
                <input type="text" name="estado" id="estado" placeholder="Estado">
            </div>
            <div class="form-row">
                <label for="cep">CEP:</label>
                <input type="text" name="cep" id="cep" placeholder="CEP">
            </div>
            <div class="form-row">
                <label for="pdf_cnpj">PDF CNPJ Atual:</label>
                <span id="pdf-cnpj-atual"></span>
            </div>
            <div class="form-row">
                <label for="pdf_cnpj_new">Substituir PDF CNPJ:</label>
                <input type="file" name="pdf_cnpj" id="pdf_cnpj_new" accept=".pdf">
            </div>
            <div class="form-row">
                <label for="pdf_susep">PDF SUSEP Atual:</label>
                <span id="pdf-susep-atual"></span>
            </div>
            <div class="form-row">
                <label for="pdf_susep_new">Substituir PDF SUSEP:</label>
                <input type="file" name="pdf_susep" id="pdf_susep_new" accept=".pdf">
            </div>
            <div class="form-actions">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-apagar" onclick="deleteItem('empresa')">Apagar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Pessoa -->
<div id="modal-edit-pessoa" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-pessoa')">×</span>
        <h2>Editar Pessoa</h2>
        <form id="form-edit-pessoa" onsubmit="saveEditedItem(event, 'pessoa')">
            <input type="hidden" name="id">
            <input type="text" name="nome" placeholder="Nome" required>
            <div class="form-actions">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-apagar" onclick="deleteItem('pessoa')">Apagar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Email -->
<div id="modal-edit-email" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-email')">×</span>
        <h2>Editar Email</h2>
        <form id="form-edit-email" onsubmit="saveEditedItem(event, 'email')">
            <input type="hidden" name="id">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="senha" placeholder="Senha" required>
            <input type="email" name="email_rec" placeholder="Email Recuperação" required>
            <div class="form-actions">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-apagar" onclick="deleteItem('email')">Apagar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Perfis -->
<div class="lista-perfis">
    <h2>Lista de Perfis</h2>
    <table id="tabela-perfis">
        <thead>
            <tr>
                <th>Nome do Perfil</th>
                <th>Empresa</th>
                <th>Email</th>
                <th>Google Aprovado</th>
                <th>Campanhas</th>
                <th>Conta Suspensa</th>
                <th>Estado</th>
                <th>Status</th>
                <th>Objetivo</th>
                <th>Último Evento</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

</div><!-- fim tab-gerenciamento -->

<div id="tab-graficos" class="tab-content">
    <h2>Gráficos de Utilização</h2>
    <canvas id="grafico-recursos" width="600" height="300"></canvas>
</div>

<div id="tab-pagamentos" class="tab-content">
    <h2>Cadastro de Pagamentos</h2>
    <form id="form-pagamento" onsubmit="cadastrarPagamento(event)">
        <div class="form-row">
            <label for="descricao_pag">Descrição:</label>
            <input type="text" id="descricao_pag" name="descricao" required>
        </div>
        <div class="form-row">
            <label for="valor_pag">Valor:</label>
            <input type="number" step="0.01" id="valor_pag" name="valor" required>
        </div>
        <div class="form-row">
            <label for="venc_pag">Data de Vencimento:</label>
            <input type="date" id="venc_pag" name="data_vencimento" required>
        </div>
        <div class="form-row">
            <label for="pag_pag">Data de Pagamento:</label>
            <input type="date" id="pag_pag" name="data_pagamento">
        </div>
        <div class="form-row">
            <label for="status_pag">Status:</label>
            <select id="status_pag" name="status" required>
                <option value="Pendente">Pendente</option>
                <option value="Pago">Pago</option>
            </select>
        </div>
        <button type="submit">Salvar</button>
    </form>
    <table id="tabela-pagamentos">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
function showNextField(currentIndex) {
    const currentField = document.getElementById(`custom_field_name_${currentIndex}`);
    const nextFieldRow = document.getElementById(`custom-field-row-${currentIndex + 1}`);

    if (currentField.value.trim() !== "" && nextFieldRow && currentIndex < 7) {
        nextFieldRow.style.display = "block";
    } else if (nextFieldRow && currentIndex < 7) {
        nextFieldRow.style.display = "none";
        for (let i = currentIndex + 1; i <= 7; i++) {
            const fieldRow = document.getElementById(`custom-field-row-${i}`);
            if (fieldRow) {
                fieldRow.style.display = "none";
                document.getElementById(`custom_field_name_${i}`).value = "";
                document.getElementById(`custom_field_data_${i}`).value = "";
            }
        }
    }
}

function validateForm(event, type) {
    if (type === 'pessoa') {
        const rgFrente = document.querySelector('input[name="rg_frente"]').files[0];
        const rgTras = document.querySelector('input[name="rg_tras"]').files[0];
        const maxSize = 2 * 1024 * 1024;

        if (rgFrente && rgFrente.size > maxSize) {
            showMessageBox('O arquivo RG Frente excede o tamanho máximo de 2MB', 'error');
            event.preventDefault();
            return false;
        }
        if (rgTras && rgTras.size > maxSize) {
            showMessageBox('O arquivo RG Trás excede o tamanho máximo de 2MB', 'error');
            event.preventDefault();
            return false;
        }
    }
    return submitForm(event, type);
}

function closeMessageBox() {
    document.getElementById('message-box').style.display = 'none';
}

window.onload = function() {
    <?php if (isset($error_message)): ?>
        showMessageBox(<?php echo json_encode(htmlspecialchars($error_message)); ?>, 'error');
    <?php elseif (isset($success_message)): ?>
        showMessageBox(<?php echo json_encode(htmlspecialchars($success_message)); ?>, 'success');
    <?php endif; ?>
};
</script>

<?php include '../includes/footer.php'; ?>