<?php
include '../includes/header.php';
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Admin') {
    header("Location: login.php");
    exit;
}
?>

<!-- Message Box -->
<div id="message-box" class="message-box" style="display: none;">
    <span id="message-text"></span>
    <button onclick="closeMessageBox()">Fechar</button>
</div>

<!-- Botões Principais -->
<div class="button-group">
    <button onclick="openModal('modal-add-login')" class="btn">Adicionar Login de Site</button>
    <button onclick="window.location.href='dashboard_admin.php'" class="btn">Voltar ao Dashboard</button>
</div>

<!-- Modal Adicionar Login -->
<div id="modal-add-login" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-add-login')">×</span>
        <h2>Adicionar Login de Site</h2>
        <form id="form-add-login" onsubmit="submitLoginForm(event, 'add')">
            <input type="text" name="nome_site" placeholder="Nome do Site" required>
            <input type="url" name="url" placeholder="URL do Site" required>
            <input type="text" name="login" placeholder="Login" required>
            <input type="text" name="senha" placeholder="Senha" required>
            <button type="submit">Salvar</button>
        </form>
    </div>
</div>

<!-- Modal Editar Login -->
<div id="modal-edit-login" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-edit-login')">×</span>
        <h2>Editar Login de Site</h2>
        <form id="form-edit-login" onsubmit="submitLoginForm(event, 'edit')">
            <input type="hidden" name="id" id="login-id">
            <input type="text" name="nome_site" id="edit-nome-site" placeholder="Nome do Site" required>
            <input type="url" name="url" id="edit-url" placeholder="URL do Site" required>
            <input type="text" name="login" id="edit-login" placeholder="Login" required>
            <input type="text" name="senha" id="edit-senha" placeholder="Senha" required>
            <div class="form-actions">
                <button type="submit">Salvar</button>
                <button type="button" class="btn-apagar" onclick="deleteLogin()">Apagar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Logins -->
<div class="lista-logins">
    <h2>Lista de Logins de Sites</h2>
    <ul id="lista-logins" class="scrollable-list"></ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadData();
});

function submitLoginForm(event, action) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const url = action === 'add' ? '../backend/save_login.php' : '../backend/edit_login.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal(form.closest('.modal').id);
            loadData();
        }
    })
    .catch(error => {
        showMessageBox('Erro ao processar: ' + error.message, 'error');
    });
}

function openEditLoginModal(id, nome_site, url, login, senha) {
    const form = document.getElementById('form-edit-login');
    form.querySelector('#login-id').value = id;
    form.querySelector('#edit-nome-site').value = nome_site;
    form.querySelector('#edit-url').value = url;
    form.querySelector('#edit-login').value = login;
    form.querySelector('#edit-senha').value = senha;
    openModal('modal-edit-login');
}

function deleteLogin() {
    const form = document.getElementById('form-edit-login');
    const formData = new FormData(form);

    fetch('../backend/delete_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal('modal-edit-login');
            loadData();
        }
    })
    .catch(error => {
        showMessageBox('Erro ao excluir: ' + error.message, 'error');
    });
}
</script>

<?php include '../includes/footer.php'; ?>