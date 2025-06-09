<?php
include '../includes/header.php';
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Cadastrar') {
    header("Location: login.php");
    exit;
}

// Buscar dados do banco, excluindo itens associados a perfis
$empresas = $conn->query("SELECT id, empresa, cnpj, site_original, telefone, endereco, cidade, estado, cep, pdf_cnpj, pdf_susep FROM empresas WHERE id NOT IN (SELECT empresa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$pessoas = $conn->query("SELECT id, nome FROM pessoas WHERE id NOT IN (SELECT pessoa_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
$emails = $conn->query("SELECT id, email, senha, email_rec FROM emails WHERE id NOT IN (SELECT email_id FROM perfis)")->fetch_all(MYSQLI_ASSOC);
?>

<!-- Message Box -->
<div id="message-box" class="message-box" style="display: none;">
    <span id="message-text"></span>
    <button onclick="closeMessageBox()">Fechar</button>
</div>

<!-- Botões Principais -->
<div class="button-group">
    <button onclick="openModal('modal-empresa')" class="btn">Adicionar Empresas</button>
    <button onclick="openModal('modal-pessoa')" class="btn">Adicionar Pessoas</button>
    <button onclick="openModal('modal-email')" class="btn">Adicionar Email</button>
    <button onclick="openModal('modal-view-logins')" class="btn">Ver Logins</button>
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
        <form id="form-pessoa" onsubmit="submitForm(event, 'pessoa')" enctype="multipart/form-data">
            <input type="text" name="nome" placeholder="Nome" required>
            <label>RG Frente (JPG/PNG):</label>
            <input type="file" name="rg_frente" accept="image/jpeg,image/png" required>
            <label>RG Trás (JPG/PNG):</label>
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

<!-- Modal Ver Logins -->
<div id="modal-view-logins" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-view-logins')">×</span>
        <h2>Logins de Sites</h2>
        <div id="login-cards" class="login-cards-container"></div>
    </div>
</div>

<!-- Listas de Empresas, Pessoas e Emails -->
<div class="listas-container">
    <div class="lista">
        <h3>Empresas Cadastradas</h3>
        <label><input type="checkbox" id="exibir-cnpj" onchange="toggleCnpj()"> Exibir CNPJ</label>
        <ul class="scrollable-list">
            <?php foreach ($empresas as $empresa): ?>
                <li class="editable" data-id="<?php echo htmlspecialchars($empresa['id'], ENT_QUOTES, 'UTF-8'); ?>" data-type="empresa" onclick="openEditModal('empresa', <?php echo htmlspecialchars($empresa['id'], ENT_QUOTES, 'UTF-8'); ?>)">
                    <span class="empresa-nome"><?php echo htmlspecialchars($empresa['empresa'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="empresa-cnpj" style="display: none;"><?php echo htmlspecialchars($empresa['cnpj'], ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($empresas)) echo "<li>Nenhuma empresa disponível.</li>"; ?>
        </ul>
    </div>
    <div class="lista">
        <h3>Pessoas Cadastradas</h3>
        <ul class="scrollable-list">
            <?php foreach ($pessoas as $pessoa): ?>
                <li class="editable" data-id="<?php echo htmlspecialchars($pessoa['id'], ENT_QUOTES, 'UTF-8'); ?>" data-type="pessoa" onclick="openEditModal('pessoa', <?php echo htmlspecialchars($pessoa['id'], ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars($pessoa['nome'], ENT_QUOTES, 'UTF-8'); ?>')">
                    <?php echo htmlspecialchars($pessoa['nome'], ENT_QUOTES, 'UTF-8'); ?>
                </li>
            <?php endforeach; ?>
            <?php if (empty($pessoas)) echo "<li>Nenhuma pessoa disponível.</li>"; ?>
        </ul>
    </div>
    <div class="lista">
        <h3>Emails Cadastrados</h3>
        <ul class="scrollable-list">
            <?php foreach ($emails as $email): ?>
                <li class="editable" data-id="<?php echo htmlspecialchars($email['id'], ENT_QUOTES, 'UTF-8'); ?>" data-type="email" onclick="openEditModal('email', <?php echo htmlspecialchars($email['id'], ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars($email['email'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($email['senha'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($email['email_rec'], ENT_QUOTES, 'UTF-8'); ?>')">
                    <?php echo htmlspecialchars($email['email'], ENT_QUOTES, 'UTF-8'); ?>
                </li>
            <?php endforeach; ?>
            <?php if (empty($emails)) echo "<li>Nenhum email disponível.</li>"; ?>
        </ul>
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

<script>// Carregar os logins apenas ao abrir o modal
let loginsLoaded = false;
document.getElementById('modal-view-logins').addEventListener('click', function(e) {
    if (e.target.classList.contains('close')) return;
    if (!loginsLoaded) {
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
        loginsLoaded = true;
    }
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