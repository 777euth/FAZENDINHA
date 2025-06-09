let empresas = [];
let pessoas = [];
let emails = [];
let opcoes = [];
let perfis = [];
let site_logins = [];
let empresasDisponiveis = 0;
let pessoasDisponiveis = 0;
let emailsDisponiveis = 0;

function updateTime() {
    const currentTimeEl = document.getElementById('current-time');
    if (currentTimeEl) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        currentTimeEl.textContent = timeString;
    }
}

setInterval(updateTime, 1000);

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        if (modalId === 'modal-criar-perfil') {
            populatePastaGoLoginSelect();
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function showMessageBox(message, type = 'success') {
    const messageBox = document.getElementById('message-box');
    const messageText = document.getElementById('message-text');
    if (messageBox && messageText) {
        messageText.textContent = message;
        messageBox.className = `message-box ${type}`;
        messageBox.style.display = 'flex';
    }
}

function closeMessageBox() {
    const messageBox = document.getElementById('message-box');
    if (messageBox) {
        messageBox.style.display = 'none';
    }
}

function submitForm(event, type) {
    event.preventDefault();
    event.stopPropagation();

    const form = event.target;
    const formData = new FormData(form);


    let url;
    switch (type) {
        case 'empresa':
            url = '../backend/save_empresa.php';
            break;
        case 'pessoa':
            url = '../backend/save_pessoa.php';
            break;
        case 'email':
            url = '../backend/save_email.php';
            break;
        case 'opcoes':
            url = '../backend/save_opcoes.php';
            break;
        case 'criar_perfil':
            url = '../backend/save_perfil.php';
            break;
        default:
            showMessageBox('Tipo de formulário inválido', 'error');
            return;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Erro na resposta do servidor: ' + response.statusText + ' - ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal(form.closest('.modal').id);
            loadData();
        }
    })
    .catch(error => {
        console.error('Erro ao enviar formulário:', error);
        showMessageBox('Erro ao cadastrar: ' + error.message, 'error');
    });
}

function loadData() {
    fetch('../backend/backend.php?action=get_all')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            empresas = data.empresas || [];
            pessoas = data.pessoas || [];
            emails = data.emails || [];
            opcoes = data.opcoes || [];
            perfis = data.perfis || [];
            site_logins = data.site_logins || [];
            empresasDisponiveis = data.empresas_disponiveis || 0;
            pessoasDisponiveis = data.pessoas_disponiveis || 0;
            emailsDisponiveis = data.emails_disponiveis || 0;

            updateCriarPerfilButton();
            updateListaPerfis();
            updateListaLogins();
            updateDashboard();
            updateCharts();
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            showMessageBox('Erro ao carregar dados: ' + error.message, 'error');
        });
}

function updateCriarPerfilButton() {
    const btn = document.getElementById('criar-perfil');
    if (btn) {
        btn.disabled = !(empresasDisponiveis > 0 && pessoasDisponiveis > 0 && emailsDisponiveis > 0);
    }
}

function populatePastaGoLoginSelect() {
    const select = document.getElementById('select-pasta-gologin');
    if (select) {
        select.innerHTML = '<option value="">Selecione uma pasta</option>';
        opcoes.forEach(opcao => {
            if (opcao.id && opcao.pasta_gologin) {
                const option = document.createElement('option');
                option.value = opcao.id;
                option.textContent = opcao.pasta_gologin;
                select.appendChild(option);
            }
        });
    }
}

function updateListaPerfis() {
    const tbody = document.querySelector('#tabela-perfis tbody');
    if (tbody) {
        tbody.innerHTML = '';

        const nomeFiltro = (document.getElementById('filtro-nome')?.value || '').toLowerCase();
        const googleFiltro = document.getElementById('filtro-google')?.value || '';
        const statusFiltro = document.getElementById('filtro-status')?.value || '';
        const contaFiltro = document.getElementById('filtro-conta')?.value || '';
        const estadoFiltro = document.getElementById('filtro-estado')?.value || '';

        const perfisCriados = perfis.filter(perfil => perfil.perfil_criado).filter(perfil => {
            const nome = (perfil.nome_perfil || '').toLowerCase();
            if (nomeFiltro && !nome.includes(nomeFiltro)) return false;
            if (googleFiltro && perfil.google_aprovado !== googleFiltro) return false;
            if (statusFiltro && perfil.status !== statusFiltro) return false;
            if (contaFiltro && perfil.conta_suspensa !== contaFiltro) return false;
            if (estadoFiltro && perfil.estado !== estadoFiltro) return false;
            return true;
        });

        const labels = [
            'Nome do Perfil',
            'Empresa',
            'Email',
            'Google Aprovado',
            'Campanhas',
            'Conta Suspensa',
            'Estado',
            'Status',
            'Objetivo',
            'Último Evento'
        ];

        perfisCriados.forEach(perfil => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td data-label="${labels[0]}">${perfil.nome_perfil || '-'}</td>
                <td data-label="${labels[1]}">${perfil.empresa || '-'}</td>
                <td data-label="${labels[2]}">${perfil.email || '-'}</td>
                <td data-label="${labels[3]}">${perfil.google_aprovado || '-'}</td>
                <td data-label="${labels[4]}">${perfil.campanhas || '-'}</td>
                <td data-label="${labels[5]}">${perfil.conta_suspensa || '-'}</td>
                <td data-label="${labels[6]}">${perfil.estado || '-'}</td>
                <td data-label="${labels[7]}">${perfil.status || '-'}</td>
                <td data-label="${labels[8]}">${perfil.objetivo || '-'}</td>
                <td data-label="${labels[9]}">${perfil.ultimo_evento || '-'}</td>
            `;
            row.style.cursor = 'pointer';
            row.addEventListener('click', () => openEditPerfilModal(perfil));
            row.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                window.location.href = `eventos.php?perfil_id=${perfil.id}`;
            });
            tbody.appendChild(row);
        });
    }
}

function updateListaLogins() {
    const lista = document.getElementById('lista-logins');
    if (lista) {
        lista.innerHTML = '';
        if (site_logins.length === 0) {
            lista.innerHTML = '<li>Nenhum login cadastrado.</li>';
            return;
        }
        site_logins.forEach(login => {
            const li = document.createElement('li');
            li.className = 'editable';
            li.innerHTML = `${login.nome_site} - <a href="${login.url}" target="_blank">${login.url}</a>`;
            li.onclick = () => openEditLoginModal(login.id, login.nome_site, login.url, login.login, login.senha);
            lista.appendChild(li);
        });
    }
}

let pagamentos = [];
function loadPagamentos() {
    fetch('../backend/get_pagamentos.php')
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            pagamentos = data.pagamentos || [];
            updateTabelaPagamentos();
        })
        .catch(error => {
            console.error('Erro ao carregar pagamentos:', error);
            showMessageBox('Erro ao carregar pagamentos: ' + error.message, 'error');
        });
}

let proximosPagamentos = [];
function loadProximosPagamentos() {
    fetch('../backend/get_pagamentos.php?proximos=1')
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            proximosPagamentos = data.pagamentos || [];
            updateTabelaProximos();
        })
        .catch(error => {
            console.error('Erro ao carregar próximos pagamentos:', error);
            showMessageBox('Erro ao carregar próximos pagamentos: ' + error.message, 'error');
        });
}

function updateTabelaPagamentos() {
    const tbody = document.querySelector('#tabela-pagamentos tbody');
    if (tbody) {
        tbody.innerHTML = '';
        pagamentos.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.descricao || '-'}</td>
                <td>${p.valor ? parseFloat(p.valor).toFixed(2) : '-'}</td>
                <td>${p.tipo || '-'}</td>
                <td>${p.data_vencimento || '-'}</td>
                <td>${p.data_pagamento || '-'}</td>
                <td>${p.status || '-'}</td>
            `;
            tbody.appendChild(row);
        });
    }
}

function updateTabelaProximos() {
    const tbody = document.querySelector('#tabela-proximos tbody');
    if (tbody) {
        tbody.innerHTML = '';
        proximosPagamentos.forEach(p => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${p.descricao || '-'}</td>
                <td>${p.valor ? parseFloat(p.valor).toFixed(2) : '-'}</td>
                <td>${p.tipo || '-'}</td>
                <td>${p.data_vencimento || '-'}</td>
                <td>${p.status || '-'}</td>
            `;
            tbody.appendChild(row);
        });
    }
}

function cadastrarPagamento(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    fetch('../backend/save_pagamento.php', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) throw new Error('Erro na resposta do servidor: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            showMessageBox(data.message, data.status);
            if (data.status === 'success') {
                form.reset();
                loadPagamentos();
            }
        })
        .catch(error => {
            console.error('Erro ao cadastrar pagamento:', error);
            showMessageBox('Erro ao cadastrar pagamento: ' + error.message, 'error');
        });
}

function updateDashboard() {
    const perfilCriadoEl = document.getElementById('perfil-criado');
    const googleAprovadoEl = document.getElementById('google-aprovado');
    const campanhasEl = document.getElementById('campanhas');
    const contaSuspensaEl = document.getElementById('conta-suspensa');
    const empresasDisponiveisEl = document.getElementById('empresas-disponiveis');
    const pessoasDisponiveisEl = document.getElementById('pessoas-disponiveis');
    const emailsDisponiveisEl = document.getElementById('emails-disponiveis');

    if (perfilCriadoEl) perfilCriadoEl.textContent = perfis.filter(p => p.perfil_criado).length;
    if (googleAprovadoEl) googleAprovadoEl.textContent = perfis.filter(p => p.google_aprovado === 'Aprovado').length;
    if (campanhasEl) campanhasEl.textContent = perfis.filter(p => p.campanhas === 'White' || p.campanhas === 'Black').length;
    if (contaSuspensaEl) contaSuspensaEl.textContent = perfis.filter(p => p.conta_suspensa === 'Sim').length;
    if (empresasDisponiveisEl) empresasDisponiveisEl.textContent = empresasDisponiveis;
    if (pessoasDisponiveisEl) pessoasDisponiveisEl.textContent = pessoasDisponiveis;
    if (emailsDisponiveisEl) emailsDisponiveisEl.textContent = emailsDisponiveis;
}

let graficoRecursos;
function updateCharts() {
    const totalEmpresas = empresas.length;
    const totalPessoas = pessoas.length;
    const totalEmails = emails.length;

    const usadosEmpresas = totalEmpresas - empresasDisponiveis;
    const usadosPessoas = totalPessoas - pessoasDisponiveis;
    const usadosEmails = totalEmails - emailsDisponiveis;

    const dados = {
        labels: ['Empresas', 'Pessoas', 'Emails'],
        datasets: [
            {
                label: 'Disponíveis',
                backgroundColor: '#8bc34a',
                data: [empresasDisponiveis, pessoasDisponiveis, emailsDisponiveis]
            },
            {
                label: 'Usados',
                backgroundColor: '#ff9800',
                data: [usadosEmpresas, usadosPessoas, usadosEmails]
            }
        ]
    };

    if (graficoRecursos) {
        graficoRecursos.data = dados;
        graficoRecursos.update();
    } else if (document.getElementById('grafico-recursos')) {
        const ctx = document.getElementById('grafico-recursos').getContext('2d');
        graficoRecursos = new Chart(ctx, {
            type: 'bar',
            data: dados,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function toggleCnpj() {
    const checkbox = document.getElementById('exibir-cnpj');
    const empresaNomes = document.querySelectorAll('.empresa-nome');
    const empresaCnpjs = document.querySelectorAll('.empresa-cnpj');

    if (checkbox.checked) {
        empresaNomes.forEach(nome => nome.style.display = 'none');
        empresaCnpjs.forEach(cnpj => cnpj.style.display = 'inline');
    } else {
        empresaNomes.forEach(nome => nome.style.display = 'inline');
        empresaCnpjs.forEach(cnpj => cnpj.style.display = 'none');
    }
}

function openEditModal(type, id, ...args) {
    const modalId = `modal-edit-${type}`;
    const form = document.getElementById(`form-edit-${type}`);

    if (!form) return;

    if (type === 'empresa') {
        fetch(`../backend/get_empresa.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.empresa) {
                    const empresa = data.empresa;
                    form.querySelector('#empresa-id').value = empresa.id || '';
                    form.querySelector('#empresa').value = empresa.empresa || '';
                    form.querySelector('#cnpj').value = empresa.cnpj || '';
                    form.querySelector('#site_original').value = empresa.site_original || '';
                    form.querySelector('#telefone').value = empresa.telefone || '';
                    form.querySelector('#endereco').value = empresa.endereco || '';
                    form.querySelector('#cidade').value = empresa.cidade || '';
                    form.querySelector('#estado').value = empresa.estado || '';
                    form.querySelector('#cep').value = empresa.cep || '';

                    const pdfCnpjSpan = document.getElementById('pdf-cnpj-atual');
                    const pdfSusepSpan = document.getElementById('pdf-susep-atual');
                    pdfCnpjSpan.innerHTML = empresa.pdf_cnpj ? `<a href="../${empresa.pdf_cnpj}" target="_blank">Ver PDF CNPJ Atual</a>` : 'Nenhum PDF';
                    pdfSusepSpan.innerHTML = empresa.pdf_susep ? `<a href="../${empresa.pdf_susep}" target="_blank">Ver PDF SUSEP Atual</a>` : 'Nenhum PDF';

                    openModal(modalId);
                } else {
                    showMessageBox('Erro: Empresa não encontrada', 'error');
                }
            })
            .catch(error => {
                console.error('Erro ao buscar empresa:', error);
                showMessageBox('Erro ao carregar dados da empresa: ' + error.message, 'error');
            });
    } else if (type === 'pessoa') {
        form.querySelector('input[name="id"]').value = id;
        form.querySelector('input[name="nome"]').value = args[0] || '';
        openModal(modalId);
    } else if (type === 'email') {
        form.querySelector('input[name="id"]').value = id;
        form.querySelector('input[name="email"]').value = args[0] || '';
        form.querySelector('input[name="senha"]').value = args[1] || '';
        form.querySelector('input[name="email_rec"]').value = args[2] || '';
        openModal(modalId);
    }
}

function openEditLoginModal(id, nome_site, url, login, senha) {
    const form = document.getElementById('form-edit-login');
    if (form) {
        form.querySelector('#login-id').value = id;
        form.querySelector('#edit-nome-site').value = nome_site;
        form.querySelector('#edit-url').value = url;
        form.querySelector('#edit-login').value = login;
        form.querySelector('#edit-senha').value = senha;
        openModal('modal-edit-login');
    }
}

function openEditPerfilModal(perfil) {
    const form = document.getElementById('form-edit-perfil');
    if (form) {
        form.querySelector('#perfil-id').value = perfil.id || '';
        form.querySelector('#perfil-google').value = perfil.google_aprovado || '';
        form.querySelector('#perfil-campanhas').value = perfil.campanhas || '';
        form.querySelector('#perfil-suspensa').value = perfil.conta_suspensa || '';
        form.querySelector('#perfil-estado').value = perfil.estado || '';
        form.querySelector('#perfil-status').value = perfil.status || '';
        form.querySelector('#perfil-objetivo').value = perfil.objetivo || '';
        openModal('modal-edit-perfil');
    }
}

function saveEditedItem(event, type) {
    event.preventDefault();
    event.stopPropagation();

    const form = event.target;
    const formData = new FormData(form);

    let url;
    switch (type) {
        case 'empresa':
            url = '../backend/edit_empresa.php';
            break;
        case 'pessoa':
            url = '../backend/edit_pessoa.php';
            break;
        case 'email':
            url = '../backend/edit_email.php';
            break;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal(form.closest('.modal').id);
            loadData();
        }
    })
    .catch(error => {
        console.error('Erro ao salvar item:', error);
        showMessageBox('Erro ao salvar item: ' + error.message, 'error');
    });
}

function savePerfilEdit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch('../backend/edit_perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal('modal-edit-perfil');
            loadData();
        }
    })
    .catch(error => {
        console.error('Erro ao salvar perfil:', error);
        showMessageBox('Erro ao salvar perfil: ' + error.message, 'error');
    });
}

function deleteItem(type) {
    const form = document.querySelector(`#form-edit-${type}`);
    const formData = new FormData(form);

    let url;
    switch (type) {
        case 'empresa':
            url = '../backend/delete_empresa.php';
            break;
        case 'pessoa':
            url = '../backend/delete_pessoa.php';
            break;
        case 'email':
            url = '../backend/delete_email.php';
            break;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        showMessageBox(data.message, data.status);
        if (data.status === 'success') {
            closeModal(form.closest('.modal').id);
            loadData();
        }
    })
    .catch(error => {
        console.error('Erro ao excluir item:', error);
        showMessageBox('Erro ao excluir item: ' + error.message, 'error');
    });
}

function copiarTexto(button) {
    const valor = button.previousElementSibling.textContent;
    navigator.clipboard.writeText(valor).then(() => {
        showMessageBox('Valor copiado: ' + valor, 'success');
    }).catch(err => {
        console.error('Erro ao copiar texto: ', err);
        showMessageBox('Erro ao copiar texto: ' + err.message, 'error');
    });
}

function openAdminTab(tab) {
    const tabs = ['gerenciamento', 'graficos', 'pagamentos', 'proximos'];
    tabs.forEach(t => {
        const content = document.getElementById(`tab-${t}`);
        const link = document.querySelector(`.tab-link[data-tab="${t}"]`);
        if (content) content.style.display = t === tab ? 'block' : 'none';
        if (link) {
            if (t === tab) link.classList.add('active');
            else link.classList.remove('active');
        }
    });
    if (tab === 'graficos') {
        updateCharts();
    } else if (tab === 'pagamentos') {
        loadPagamentos();
    } else if (tab === 'proximos') {
        loadProximosPagamentos();
    }
}

function toggleLoginDetails(titleElement) {
    const details = titleElement.nextElementSibling;
    if (details.style.display === 'none' || details.style.display === '') {
        details.style.display = 'block';
        titleElement.classList.add('active'); // Adiciona a classe active para girar a seta
    } else {
        details.style.display = 'none';
        titleElement.classList.remove('active'); // Remove a classe active
    }
}

window.onload = function() {
    loadData();
    updateTime();
    if (typeof openAdminTab === 'function') {
        openAdminTab('gerenciamento');
    }

    const formPag = document.getElementById('form-pagamento');
    if (formPag) formPag.addEventListener('submit', cadastrarPagamento);

    const formEditPerfil = document.getElementById('form-edit-perfil');
    if (formEditPerfil) formEditPerfil.addEventListener('submit', savePerfilEdit);

    const nomeInput = document.getElementById('filtro-nome');
    if (nomeInput) nomeInput.addEventListener('input', updateListaPerfis);

    ['filtro-google', 'filtro-status', 'filtro-conta', 'filtro-estado'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', updateListaPerfis);
    });
};
