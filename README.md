# Projeto Fazendinha

Este projeto é uma aplicação web simples em PHP para gerenciamento de perfis, empresas e logins.

## Requisitos
- PHP 7.4 ou superior
- Servidor web (Apache, Nginx ou similar)
- SQLite 3

## Configuração
1. Copie o arquivo `.env.example` para `.env` e defina o caminho do arquivo SQLite em `DB_PATH`.
   ```bash
   cp .env.example .env
   # Edite o arquivo .env para informar o caminho do seu banco .db
   ```
2. Importe o esquema de banco de dados correspondente (não incluído neste repositório) para o arquivo `.db` indicado.
3. Certifique-se de que a pasta `uploads/` tenha permissões de escrita caso utilize upload de arquivos.
4. Inicie o servidor web apontando para o diretório do projeto.

## Uso
O arquivo `index.php` redireciona para `pages/login.php`, onde é possível realizar o login. Após a autenticação, o usuário é encaminhado para o painel correspondente à sua categoria (Admin, Cadastrar ou Fazendeiro).

## Segurança
- As credenciais do banco de dados são carregadas do arquivo `.env` ou de variáveis de ambiente.
- Recomenda-se manter o arquivo `.env` fora do controle de versão (já incluído no `.gitignore`).

