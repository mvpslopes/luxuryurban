# Luxury Urban — Sistema de Gestão

Sistema **100% PHP** para gestão de loja de roupas e acessórios. Compatível com hospedagem compartilhada (Hostinger).

> Guia completo de deploy: **[HOSPEDAGEM.md](HOSPEDAGEM.md)**

## Requisitos

- PHP 8.1+ (selecionar no hPanel da Hostinger)
- MySQL / MariaDB
- Extensões PHP: `pdo_mysql`, `mbstring`
- Apache com `mod_rewrite`
- **Não precisa** de Node.js, Docker ou SSH (basta enviar a pasta `vendor/`)

## Instalação rápida (Hostinger + PHP)

### 1. Criar tabelas no MySQL

No **phpMyAdmin**:

1. Selecione o banco `u179630068_luxuryurban`
2. Aba **SQL**
3. Cole e execute `database/luxuryurban_install.sql`

### 2. Enviar arquivos via FTP

Envie **todo** o projeto incluindo a pasta `vendor/`.

Configure o domínio para apontar à pasta **`public/`**  
*(ou siga a Opção B em [HOSPEDAGEM.md](HOSPEDAGEM.md) para `public_html/`)*

### 3. Criar `.env` na raiz do projeto

```env
APP_URL=https://luxuryurban.com.br
DB_HOST=localhost
DB_NAME=u179630068_luxuryurban
DB_USER=u179630068_luxuryurban_us
DB_PASS=sua_senha
```

### 4. Permissões

`public/uploads/produtos/` → permissão **755** ou **775**

## Login inicial

| Campo | Valor |
|-------|-------|
| Usuário | `marcus.lopes` |
| Senha | `*.Admin14!` |
| Perfil | Root |

## Perfis

- **Root** — gerencia usuários
- **Admin** — produtos, categorias, pagamentos, estoque, vendas, aprovações
- **Vendedor** — clientes, vendas (desconto até 10%), estornos

## Estrutura

```
public/          → Document root
app/             → Controllers, Views, Services
database/        → Script SQL de instalação
config/          → Configurações
```
