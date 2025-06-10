<?php
include '../includes/header.php';
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Fazendeiro') {
    header("Location: login.php");
    exit;
}

// Buscar dados para preencher o formulário de criação de perfil
$empresas = $conn->query("SELECT id, empresa, cnpj FROM empresas WHERE id NOT IN (SELECT empresa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$pessoas = $conn->query("SELECT id, nome FROM pessoas WHERE id NOT IN (SELECT pessoa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$emails = $conn->query("SELECT id, email FROM emails WHERE id NOT IN (SELECT email_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
?>

<!-- Message Box -->
<div id="message-box" class="message-box" style="display: none;">
    <span id="message-text"></span>
    <button onclick="closeMessageBox()">Fechar</button>
</div>

<!-- Botões Principais -->
<div class="button-group">
    <button onclick="openModal('modal-criar-perfil')" class="btn">Criar Perfil</button>
    <button onclick="window.location.href='eventos.php'" class="btn">Eventos</button>
    <button onclick="openModal('modal-view-logins')" class="btn">Ver Logins</button>
</div>

<!-- Modal Criar Perfil -->
<div id="modal-criar-perfil" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-criar-perfil')">×</span>
        <h2>Criar Perfil</h2>
        <form id="form-criar-perfil" onsubmit="submitForm(event, 'criar_perfil')">
            <label>Selecione a Empresa:</label>
            <select name="empresa_id" required>
                <option value="">Selecione uma empresa</option>
                <?php foreach ($empresas as $empresa): ?>
                    <option value="<?php echo htmlspecialchars($empresa['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($empresa['empresa'], ENT_QUOTES, 'UTF-8') . ' - CNPJ: ' . htmlspecialchars($empresa['cnpj'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Selecione a Pessoa:</label>
            <select name="pessoa_id" required>
                <option value="">Selecione uma pessoa</option>
                <?php foreach ($pessoas as $pessoa): ?>
                    <option value="<?php echo htmlspecialchars($pessoa['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($pessoa['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Selecione o Email:</label>
            <select name="email_id" required>
                <option value="">Selecione um email</option>
                <?php foreach ($emails as $email): ?>
                    <option value="<?php echo htmlspecialchars($email['id'], ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Modal Ver Logins -->
<div id="modal-view-logins" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-view-logins')">×</span>
        <h2>Logins de Sites</h2>
        <div id="login-cards" class="login-cards-container"></div>
    </div>
</div>

<!-- Modal Editar Perfil -->
<div id="modal-edit-perfil" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-perfil')">×</span>
        <h2>Editar Perfil</h2>
        <form id="form-edit-perfil">
            <input type="hidden" name="id" id="perfil-id">
            <label>Google Aprovado:</label>
            <select name="google_aprovado" id="perfil-google">
                <option value="Aprovado">Aprovado</option>
                <option value="Pendente">Pendente</option>
            </select>
            <label>Campanhas:</label>
            <input type="text" name="campanhas" id="perfil-campanhas">
            <label>Conta Suspensa:</label>
            <select name="conta_suspensa" id="perfil-suspensa">
                <option value="Sim">Sim</option>
                <option value="Não">Não</option>
            </select>
            <label>Estado:</label>
            <select name="estado" id="perfil-estado">
                <option value="Aguardando">Aguardando</option>
                <option value="Rodando">Rodando</option>
                <option value="Pausado">Pausado</option>
            </select>
            <label>Status:</label>
            <select name="status" id="perfil-status">
                <option value="Ativa">Ativa</option>
                <option value="Inativa">Inativa</option>
            </select>
            <label>Objetivo:</label>
            <input type="text" name="objetivo" id="perfil-objetivo">
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Lista de Perfis -->
<div class="lista-perfis">
    <h2>Lista de Perfis</h2>
    <div class="filter-group">
        <input type="text" id="filtro-nome" placeholder="Buscar nome">
        <select id="filtro-google">
            <option value="">Google Aprovado</option>
            <option value="Aprovado">Aprovado</option>
            <option value="Pendente">Pendente</option>
        </select>
        <select id="filtro-status">
            <option value="">Status</option>
            <option value="Ativa">Ativa</option>
            <option value="Inativa">Inativa</option>
        </select>
        <select id="filtro-conta">
            <option value="">Conta Suspensa</option>
            <option value="Sim">Sim</option>
            <option value="Não">Não</option>
        </select>
        <select id="filtro-estado">
            <option value="">Estado</option>
            <option value="Aguardando">Aguardando</option>
            <option value="Rodando">Rodando</option>
            <option value="Pausado">Pausado</option>
        </select>
    </div>
    <div class="table-wrapper">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updatePerfis() {
        fetch('../backend/backend.php?action=get_all')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                empresas = data.empresas || [];
                pessoas = data.pessoas || [];
                emails = data.emails || [];
                opcoes = data.opcoes || [];
                perfis = data.perfis || [];
                site_logins = data.site_logins || [];
                empresasDisponiveis = data.empresas_disponiveis || 0;
                pessoasDisponiveis = data.pessoas_disponiveis || 0;
                emailsDisponiveis = data.emails_disponiveis || 0;

                updateListaPerfis();
                updateDashboard();
            })
            .catch(error => {
                console.error('Erro ao carregar dados:', error);
                showMessageBox('Erro ao carregar dados: ' + error.message, 'error');
            });
    }

    // Atualizar inicialmente
    updatePerfis();

    // Atualizar a cada 5 segundos
    setInterval(updatePerfis, 5000);

    // Função para carregar os logins
    function loadLogins() {
        fetch('../backend/get_logins.php')
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error('Erro na resposta do servidor: ' + response.statusText + ' - ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados dos logins recebidos:', data);
                const container = document.getElementById('login-cards');
                container.innerHTML = '';
                if (data.status === 'success') {
                    if (data.logins && data.logins.length > 0) {
                        data.logins.forEach(login => {
                            const card = document.createElement('div');
                            card.className = 'login-card';
                            card.innerHTML = `
                                <h3 class="login-card-title">${login.nome_site}</h3>
                                <div class="login-card-details" style="display: none;">
                                    <p><strong>URL:</strong> <a href="${login.url}" target="_blank">${login.url}</a></p>
                                    <p><strong>Login:</strong> <span class="valor-copiavel">${login.login}</span> <button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button></p>
                                    <p><strong>Senha:</strong> <span class="valor-copiavel">${login.senha}</span> <button class="btn-copiar" onclick="copiarTexto(this)">Copiar</button></p>
                                </div>
                            `;
                            container.appendChild(card);

                            // Adiciona o evento de clique no título do card
                            card.querySelector('.login-card-title').addEventListener('click', function(event) {
                                event.stopPropagation(); // Impede a propagação do evento para o elemento pai
                                toggleLoginDetails(this);
                            });
                        });
                    } else {
                        container.innerHTML = '<p>Nenhum login disponível.</p>';
                    }
                } else {
                    container.innerHTML = '<p>' + data.message + '</p>';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar logins:', error);
                showMessageBox('Erro ao carregar logins: ' + error.message, 'error');
            });
    }

    // Carregar os logins apenas ao abrir o modal
    let loginsLoaded = false;
    document.getElementById('modal-view-logins').addEventListener('click', function(e) {
        if (e.target.classList.contains('close')) return;
        if (!loginsLoaded) {
            loadLogins();
            loginsLoaded = true;
        }
    });
});

function toggleLoginDetails(titleElement) {
    const details = titleElement.nextElementSibling;
    if (details.style.display === 'none' || details.style.display === '') {
        details.style.display = 'block';
    } else {
        details.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
