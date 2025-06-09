<?php
include '../includes/header.php';
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Fazendeiro') {
    header("Location: login.php");
    exit;
}

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

$base_url = '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['executar_evento'])) {
    $perfil_id = filter_input(INPUT_POST, 'perfil_id', FILTER_VALIDATE_INT);
    $evento_id = filter_input(INPUT_POST, 'evento_id', FILTER_VALIDATE_INT);

    if (!$perfil_id || !$evento_id) {
        $error_message = "Perfil ou Evento inválido.";
    } else {
        $stmt = $conn->prepare("SELECT nome, passos, campanhas, conta_suspensa, google_aprovado, estado, status, objetivo, campos_perfil_adicionar FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $evento = $result->fetch_assoc();
        $stmt->close();

        if ($evento) {
            $update_fields = [];
            $update_values = [];
            $types = '';

            $update_fields[] = "campanhas = ?";
            $update_values[] = $evento['campanhas'];
            $types .= 's';

            $update_fields[] = "conta_suspensa = ?";
            $update_values[] = $evento['conta_suspensa'];
            $types .= 's';

            $update_fields[] = "google_aprovado = ?";
            $update_values[] = $evento['google_aprovado'];
            $types .= 's';

            $update_fields[] = "estado = ?";
            $update_values[] = $evento['estado'];
            $types .= 's';

            $update_fields[] = "status = ?";
            $update_values[] = $evento['status'];
            $types .= 's';

            $update_fields[] = "objetivo = ?";
            $update_values[] = $evento['objetivo'];
            $types .= 's';

            $campos_adicionar = json_decode($evento['campos_perfil_adicionar'], true);
            if (!empty($campos_adicionar)) {
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
            }

            $update_query = "UPDATE perfis SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $types .= 'i';
            $update_values[] = $perfil_id;
            $stmt->bind_param($types, ...$update_values);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                $descricao = "Evento '" . htmlspecialchars($evento['nome']) . "' executado em " . date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO tarefas (perfil_id, descricao) VALUES (?, ?)");
                $stmt->bind_param("is", $perfil_id, $descricao);
                $stmt->execute();
                $stmt->close();

                $success_message = "Evento executado com sucesso!";
                echo "<script>window.location.href='dashboard_fazendeiro.php';</script>";
            } else {
                $error_message = "Erro ao executar evento.";
            }
        } else {
            $error_message = "Evento não encontrado.";
        }
    }
}
?>

<h2>Eventos (Tutoriais)</h2>
<div class="eventos-container">
    <button class="btn" onclick="window.location.href='dashboard_fazendeiro.php'">Voltar para a Dashboard</button>

    <form method="POST" action="">
        <label for="perfil_id">Selecione o Perfil:</label>
        <select name="perfil_id" id="perfil_id" required>
            <option value="">Selecione um perfil</option>
        </select>

        <label for="evento_id">Selecione o Evento:</label>
        <select name="evento_id" id="evento_id" required>
            <option value="">Selecione um evento</option>
        </select>

        <div id="campos-adicionais" class="campos-adicionais">
            <!-- Campos adicionais serão adicionados aqui dinamicamente -->
        </div>

        <button type="submit" name="executar_evento">Executar Evento</button>
    </form>

    <div class="eventos-layout">
        <div class="dados-selecionados">
            <h3>Dados Selecionados do Perfil</h3>
            <div id="perfil-selecionado">
                <ul id="perfil-dados"></ul>
                <div id="documentos" class="documentos-container"></div>
            </div>
        </div>
        <div id="passos-evento">
            <h3>Passos do Evento</h3>
            <ul id="passos-lista"></ul>
        </div>
    </div>

    <div id="evento-selecionado">
        <h3>Informações do Evento</h3>
        <p><strong>Campanha:</strong> <span id="campanha-info"></span></p>
        <p><strong>Conta Suspensa:</strong> <span id="conta-suspensa-info"></span></p>
        <p><strong>Google Aprovado:</strong> <span id="google-aprovado-info"></span></p>
        <p><strong>Estado:</strong> <span id="estado-info"></span></p>
        <p><strong>Status:</strong> <span id="status-info"></span></p>
        <p><strong>Objetivo:</strong> <span id="objetivo-info"></span></p>
        <p><strong>Dias para o Próximo Evento:</strong> <span id="dias-info"></span></p>
    </div>
</div>

<script>
let eventosData = [];
let perfisData = [];

document.addEventListener('DOMContentLoaded', function() {
    fetch('../backend/get_eventos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                eventosData = data.eventos;
                perfisData = data.perfis;

                const perfilSelect = document.getElementById('perfil_id');
                perfisData.forEach(perfil => {
                    const option = document.createElement('option');
                    option.value = perfil.id;
                    option.textContent = perfil.nome_perfil;
                    option.dataset.nomePerfil = perfil.nome_perfil || '';
                    option.dataset.empresaNome = perfil.empresa_nome || '';
                    option.dataset.cnpj = perfil.cnpj || '';
                    option.dataset.siteOriginal = perfil.site_original || '';
                    option.dataset.telefone = perfil.telefone || '';
                    option.dataset.endereco = perfil.endereco || '';
                    option.dataset.cidade = perfil.cidade || '';
                    option.dataset.empresaEstado = perfil.empresa_estado || '';
                    option.dataset.cep = perfil.cep || '';
                    option.dataset.pessoaNome = perfil.pessoa_nome || '';
                    option.dataset.emailNome = perfil.email_nome || '';
                    option.dataset.emailSenha = perfil.email_senha || '';
                    option.dataset.emailRec = perfil.email_rec || '';
                    option.dataset.perfilCriado = perfil.perfil_criado ? 'Sim' : 'Não';
                    option.dataset.googleAprovado = perfil.google_aprovado || '';
                    option.dataset.campanhas = perfil.campanhas || '';
                    option.dataset.contaSuspensa = perfil.conta_suspensa || '';
                    option.dataset.estado = perfil.estado || '';
                    option.dataset.status = perfil.status || '';
                    option.dataset.objetivo = perfil.objetivo || '';
                    option.dataset.pdfCnpj = perfil.pdf_cnpj || '';
                    option.dataset.pdfSusep = perfil.pdf_susep || '';
                    option.dataset.rgFrente = perfil.rg_frente || '';
                    option.dataset.rgTras = perfil.rg_tras || '';
                    option.dataset.emailProfissional = perfil.email_profissional || '';
                    option.dataset.novoSite = perfil.novo_site || '';
                    option.dataset.numeroGoogleAds = perfil.numero_google_ads || '';
                    option.dataset.codigoAtivacaoG2 = perfil.codigo_ativacao_g2 || '';
                    option.dataset.campoAdicional1 = perfil.campo_adicional_1 || '';
                    option.dataset.campoAdicional2 = perfil.campo_adicional_2 || '';
                    perfilSelect.appendChild(option);
                });

                const eventoSelect = document.getElementById('evento_id');
                eventosData.forEach(evento => {
                    const option = document.createElement('option');
                    option.value = evento.id;
                    option.textContent = evento.nome;
                    option.dataset.passos = evento.passos || '[]';
                    option.dataset.campanhas = evento.campanhas || '';
                    option.dataset.contaSuspensa = evento.conta_suspensa || '';
                    option.dataset.googleAprovado = evento.google_aprovado || '';
                    option.dataset.estado = evento.estado || '';
                    option.dataset.status = evento.status || '';
                    option.dataset.objetivo = evento.objetivo || '';
                    option.dataset.dias = evento.quantidade_dias || '';
                    option.dataset.camposExibir = evento.campos_exibir || '[]';
                    option.dataset.camposPersonalizados = evento.campos_personalizados || '[]';
                    option.dataset.exibirArquivos = evento.exibir_arquivos ? 'true' : 'false';
                    option.dataset.camposPerfilAdicionar = evento.campos_perfil_adicionar || '[]';
                    eventoSelect.appendChild(option);
                });

                const urlParams = new URLSearchParams(window.location.search);
                const perfilId = urlParams.get('perfil_id');
                if (perfilId) {
                    perfilSelect.value = perfilId;
                    const event = new Event('change');
                    perfilSelect.dispatchEvent(event);
                }
            } else {
                alert('Erro ao carregar dados: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            alert('Erro ao carregar dados: ' + error.message);
        });
});

document.getElementById('perfil_id').addEventListener('change', function() {
    const perfilId = this.value;
    const perfilDados = document.getElementById('perfil-dados');
    const documentosContainer = document.getElementById('documentos');
    perfilDados.innerHTML = '';
    documentosContainer.innerHTML = '';

    const eventoId = document.getElementById('evento_id').value;
    let camposExibir = [];
    let camposPersonalizados = [];
    let camposAdicionar = [];
    let exibirArquivos = false;
    if (eventoId) {
        const selectedOption = document.getElementById('evento_id').options[document.getElementById('evento_id').selectedIndex];
        camposExibir = JSON.parse(selectedOption.dataset.camposExibir || '[]');
        camposPersonalizados = JSON.parse(selectedOption.dataset.camposPersonalizados || '[]');
        camposAdicionar = JSON.parse(selectedOption.dataset.camposPerfilAdicionar || '[]');
        exibirArquivos = selectedOption.dataset.exibirArquivos === 'true';
    }

    if (perfilId) {
        const selectedOption = this.options[this.selectedIndex];
        const dados = {
            'nome_perfil': selectedOption.dataset.nomePerfil || '',
            'empresa_nome': selectedOption.dataset.empresaNome || '',
            'cnpj': selectedOption.dataset.cnpj || '',
            'site_original': selectedOption.dataset.siteOriginal || '',
            'telefone': selectedOption.dataset.telefone || '',
            'endereco': selectedOption.dataset.endereco || '',
            'cidade': selectedOption.dataset.cidade || '',
            'empresa_estado': selectedOption.dataset.empresaEstado || '',
            'cep': selectedOption.dataset.cep || '',
            'pessoa_nome': selectedOption.dataset.pessoaNome || '',
            'email_nome': selectedOption.dataset.emailNome || '',
            'email_senha': selectedOption.dataset.emailSenha || '',
            'email_rec': selectedOption.dataset.emailRec || '',
            'perfil_criado': selectedOption.dataset.perfilCriado || '',
            'google_aprovado': selectedOption.dataset.googleAprovado || '',
            'campanhas': selectedOption.dataset.campanhas || '',
            'conta_suspensa': selectedOption.dataset.contaSuspensa || '',
            'estado': selectedOption.dataset.estado || '',
            'status': selectedOption.dataset.status || '',
            'objetivo': selectedOption.dataset.objetivo || '',
            'pdf_cnpj': selectedOption.dataset.pdfCnpj || '',
            'pdf_susep': selectedOption.dataset.pdfSusep || '',
            'rg_frente': selectedOption.dataset.rgFrente || '',
            'rg_tras': selectedOption.dataset.rgTras || '',
            'email_profissional': selectedOption.dataset.emailProfissional || '',
            'novo_site': selectedOption.dataset.novoSite || '',
            'numero_google_ads': selectedOption.dataset.numeroGoogleAds || '',
            'codigo_ativacao_g2': selectedOption.dataset.codigoAtivacaoG2 || '',
            'campo_adicional_1': selectedOption.dataset.campoAdicional1 || '',
            'campo_adicional_2': selectedOption.dataset.campoAdicional2 || ''
        };

        const colunasPerfil = <?php echo json_encode($colunas_perfil); ?>;
        if (camposExibir.length > 0 || camposPersonalizados.length > 0 || exibirArquivos) {
            camposExibir.forEach(campo => {
                if (dados[campo] || camposAdicionar.includes(campo)) {
                    const li = document.createElement('li');
                    const valor = dados[campo] ? dados[campo] : '<campo vazio>';
                    li.innerHTML = `<strong>${colunasPerfil[campo]}:</strong> <span class="valor-copiavel">${valor}</span> ${dados[campo] ? '<button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button>' : ''}`;
                    perfilDados.appendChild(li);
                }
            });

            camposPersonalizados.forEach(field => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${field.nome}:</strong> <span class="valor-copiavel">${field.dados}</span> <button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button>`;
                perfilDados.appendChild(li);
            });

            if (exibirArquivos) {
                const documentos = [
                    { key: 'pdf_cnpj', label: 'CNPJ', type: 'pdf' },
                    { key: 'pdf_susep', label: 'SUSEP', type: 'pdf' },
                    { key: 'rg_frente', label: 'RG Frente', type: 'image' },
                    { key: 'rg_tras', label: 'RG Trás', type: 'image' }
                ];

                documentos.forEach(doc => {
                    if (dados[doc.key]) {
                        let correctedPath = dados[doc.key].startsWith('backend/') ? dados[doc.key] : 'backend/' + dados[doc.key];
                        correctedPath = '<?php echo $base_url; ?>' + correctedPath;

                        const docElement = document.createElement('div');
                        docElement.className = 'documento-item';
                        if (doc.type === 'pdf') {
                            docElement.innerHTML = `
                                <a href="${correctedPath}" download class="documento-link">
                                    <div class="documento-icon ${doc.type === 'pdf' ? 'pdf-icon' : 'image-icon'}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M16 13h-4v4m0-4v4m-2-2h4"></path></svg>
                                    </div>
                                    <span>${doc.label}</span>
                                </a>
                            `;
                        } else {
                            docElement.innerHTML = `
                                <a href="${correctedPath}" target="_blank" class="documento-link">
                                    <div class="documento-icon ${doc.type === 'pdf' ? 'pdf-icon' : 'image-icon'}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    </div>
                                    <span>${doc.label}</span>
                                </a>
                            `;
                        }
                        documentosContainer.appendChild(docElement);
                    }
                });
            }
        } else {
            perfilDados.innerHTML = '<li>Nenhum dado selecionado para exibição.</li>';
        }
    }
});

document.getElementById('evento_id').addEventListener('change', function() {
    const eventoId = this.value;
    const passosLista = document.getElementById('passos-lista');
    const campanhaInfo = document.getElementById('campanha-info');
    const contaSuspensaInfo = document.getElementById('conta-suspensa-info');
    const googleAprovadoInfo = document.getElementById('google-aprovado-info');
    const estadoInfo = document.getElementById('estado-info');
    const statusInfo = document.getElementById('status-info');
    const objetivoInfo = document.getElementById('objetivo-info');
    const diasInfo = document.getElementById('dias-info');
    const perfilDados = document.getElementById('perfil-dados');
    const documentosContainer = document.getElementById('documentos');
    const camposAdicionaisContainer = document.getElementById('campos-adicionais');
    passosLista.innerHTML = '';
    documentosContainer.innerHTML = '';
    camposAdicionaisContainer.innerHTML = '';

    if (eventoId) {
        const selectedOption = this.options[this.selectedIndex];
        let passos;
        try {
            passos = JSON.parse(selectedOption.dataset.passos);
        } catch (error) {
            console.error("Erro ao parsear passos:", error, selectedOption.dataset.passos);
            passos = [];
        }
        const campanhas = selectedOption.dataset.campanhas || '';
        const contaSuspensa = selectedOption.dataset.contaSuspensa || '';
        const googleAprovado = selectedOption.dataset.googleAprovado || '';
        const estado = selectedOption.dataset.estado || '';
        const status = selectedOption.dataset.status || '';
        const objetivo = selectedOption.dataset.objetivo || '';
        const dias = selectedOption.dataset.dias || '';
        let camposExibir = [];
        let camposPersonalizados = [];
        let camposAdicionar = [];
        try {
            camposExibir = JSON.parse(selectedOption.dataset.camposExibir || '[]');
            camposPersonalizados = JSON.parse(selectedOption.dataset.camposPersonalizados || '[]');
            camposAdicionar = JSON.parse(selectedOption.dataset.camposPerfilAdicionar || '[]');
        } catch (error) {
            console.error("Erro ao parsear campos:", error);
            camposExibir = [];
            camposPersonalizados = [];
            camposAdicionar = [];
        }
        const exibirArquivos = selectedOption.dataset.exibirArquivos === 'true';

        passos.forEach(passo => {
            const li = document.createElement('li');
            li.textContent = passo;
            passosLista.appendChild(li);
        });

        campanhaInfo.textContent = campanhas;
        contaSuspensaInfo.textContent = contaSuspensa;
        googleAprovadoInfo.textContent = googleAprovado;
        estadoInfo.textContent = estado;
        statusInfo.textContent = status;
        objetivoInfo.textContent = objetivo;
        diasInfo.textContent = dias;

        if (camposAdicionar.length > 0) {
            camposAdicionar.forEach(campo => {
                const div = document.createElement('div');
                div.className = 'form-row';
                div.innerHTML = `
                    <label for="${campo}">${campo.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}:</label>
                    <input type="text" name="${campo}" id="${campo}" placeholder="${campo.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}">
                `;
                camposAdicionaisContainer.appendChild(div);
            });
        }

        const perfilId = document.getElementById('perfil_id').value;
        perfilDados.innerHTML = '';
        documentosContainer.innerHTML = '';
        if (perfilId) {
            const perfilOption = document.getElementById('perfil_id').options[document.getElementById('perfil_id').selectedIndex];
            const dados = {
                'nome_perfil': perfilOption.dataset.nomePerfil || '',
                'empresa_nome': perfilOption.dataset.empresaNome || '',
                'cnpj': perfilOption.dataset.cnpj || '',
                'site_original': perfilOption.dataset.siteOriginal || '',
                'telefone': perfilOption.dataset.telefone || '',
                'endereco': perfilOption.dataset.endereco || '',
                'cidade': perfilOption.dataset.cidade || '',
                'empresa_estado': perfilOption.dataset.empresaEstado || '',
                'cep': perfilOption.dataset.cep || '',
                'pessoa_nome': perfilOption.dataset.pessoaNome || '',
                'email_nome': perfilOption.dataset.emailNome || '',
                'email_senha': perfilOption.dataset.emailSenha || '',
                'email_rec': perfilOption.dataset.emailRec || '',
                'perfil_criado': perfilOption.dataset.perfilCriado || '',
                'google_aprovado': perfilOption.dataset.googleAprovado || '',
                'campanhas': perfilOption.dataset.campanhas || '',
                'conta_suspensa': perfilOption.dataset.contaSuspensa || '',
                'estado': perfilOption.dataset.estado || '',
                'status': perfilOption.dataset.status || '',
                'objetivo': perfilOption.dataset.objetivo || '',
                'pdf_cnpj': perfilOption.dataset.pdfCnpj || '',
                'pdf_susep': perfilOption.dataset.pdfSusep || '',
                'rg_frente': perfilOption.dataset.rgFrente || '',
                'rg_tras': perfilOption.dataset.rgTras || '',
                'email_profissional': perfilOption.dataset.emailProfissional || '',
                'novo_site': perfilOption.dataset.novoSite || '',
                'numero_google_ads': perfilOption.dataset.numeroGoogleAds || '',
                'codigo_ativacao_g2': perfilOption.dataset.codigoAtivacaoG2 || '',
                'campo_adicional_1': perfilOption.dataset.campoAdicional1 || '',
                'campo_adicional_2': perfilOption.dataset.campoAdicional2 || ''
            };

            const colunasPerfil = <?php echo json_encode($colunas_perfil); ?>;
            if (camposExibir.length > 0 || camposPersonalizados.length > 0 || exibirArquivos) {
                camposExibir.forEach(campo => {
                    if (dados[campo] || camposAdicionar.includes(campo)) {
                        const li = document.createElement('li');
                        const valor = dados[campo] ? dados[campo] : '<campo vazio>';
                        li.innerHTML = `<strong>${colunasPerfil[campo]}:</strong> <span class="valor-copiavel">${valor}</span> ${dados[campo] ? '<button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button>' : ''}`;
                        perfilDados.appendChild(li);
                    }
                });

                camposPersonalizados.forEach(field => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${field.nome}:</strong> <span class="valor-copiavel">${field.dados}</span> <button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button>`;
                    perfilDados.appendChild(li);
                });

                if (exibirArquivos) {
                    const documentos = [
                        { key: 'pdf_cnpj', label: 'CNPJ', type: 'pdf' },
                        { key: 'pdf_susep', label: 'SUSEP', type: 'pdf' },
                        { key: 'rg_frente', label: 'RG Frente', type: 'image' },
                        { key: 'rg_tras', label: 'RG Trás', type: 'image' }
                    ];

                    documentos.forEach(doc => {
                        if (dados[doc.key]) {
                            let correctedPath = dados[doc.key].startsWith('backend/') ? dados[doc.key] : 'backend/' + dados[doc.key];
                            correctedPath = '<?php echo $base_url; ?>' + correctedPath;

                            const docElement = document.createElement('div');
                            docElement.className = 'documento-item';
                            if (doc.type === 'pdf') {
                                docElement.innerHTML = `
                                    <a href="${correctedPath}" download class="documento-link">
                                        <div class="documento-icon ${doc.type === 'pdf' ? 'pdf-icon' : 'image-icon'}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M16 13h-4v4m0-4v4m-2-2h4"></path></svg>
                                        </div>
                                        <span>${doc.label}</span>
                                    </a>
                                `;
                            } else {
                                docElement.innerHTML = `
                                    <a href="${correctedPath}" target="_blank" class="documento-link">
                                        <div class="documento-icon ${doc.type === 'pdf' ? 'pdf-icon' : 'image-icon'}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8bc34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                        </div>
                                        <span>${doc.label}</span>
                                    </a>
                                `;
                            }
                            documentosContainer.appendChild(docElement);
                        }
                    });
                }
            } else {
                perfilDados.innerHTML = '<li>Nenhum dado selecionado para exibição.</li>';
            }
        }
    }
});

<?php if (isset($error_message)): ?>
    showMessageBox('<?php echo htmlspecialchars($error_message); ?>', 'error');
<?php elseif (isset($success_message)): ?>
    showMessageBox('<?php echo htmlspecialchars($success_message); ?>', 'success');
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>