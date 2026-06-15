# Deploy em hospedagem PHP (Hostinger)

O sistema **Luxury Urban** é 100% **PHP nativo** — não usa Node.js, React build, Laravel nem Docker.

## Requisitos da hospedagem

| Item | Mínimo |
|------|--------|
| PHP | **8.1** ou superior (selecione no hPanel → PHP Configuration) |
| MySQL | 5.7+ / MariaDB |
| Extensões PHP | `pdo_mysql`, `mbstring`, `json`, `session` |
| Apache | `mod_rewrite` ativo (padrão na Hostinger) |

---

## Passo 1 — Criar tabelas (phpMyAdmin)

1. hPanel → **Bancos de dados** → phpMyAdmin
2. Banco: `u179630068_luxuryurban`
3. Aba **SQL** → cole o arquivo `database/luxuryurban_install.sql` → **Executar**

---

## Passo 2 — Enviar arquivos

### Opção A — Document root na pasta `public/` (recomendado)

1. Envie **toda** a pasta do projeto para o servidor (FTP ou Gerenciador de arquivos)
2. hPanel → **Websites** → **Gerenciar** → **Domínios** / **Document root**
3. Aponte `luxuryurban.com.br` para: `.../luxuryurban/public`

Pronto. Acesse `https://luxuryurban.com.br/login`

### Opção B — Document root em `public_html/` (padrão Hostinger)

Estrutura no servidor:

```
/home/seu_usuario/
├── luxuryurban/              ← FORA do public_html (seguro)
│   ├── app/
│   ├── config/
│   ├── vendor/               ← envie esta pasta (já gerada)
│   ├── database/
│   └── .env
└── domains/luxuryurban.com.br/public_html/
    ├── index.php             ← use deploy/public_html/index.php
    ├── .htaccess             ← use deploy/public_html/.htaccess
    ├── assets/               ← copie de public/assets/
    └── uploads/              ← copie de public/uploads/
```

> Ajuste o caminho `LUXURYURBAN_BASE` em `deploy/public_html/index.php` se a pasta tiver outro nome ou local.

---

## Passo 3 — Arquivo `.env`

Crie `luxuryurban/.env` (fora do public_html):

```env
APP_NAME=Luxury Urban
APP_URL=https://luxuryurban.com.br
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_NAME=u179630068_luxuryurban
DB_USER=u179630068_luxuryurban_us
DB_PASS=sua_senha_aqui
```

---

## Passo 4 — Composer (dependências PHP)

A pasta `vendor/` **já deve ser enviada** junto com o projeto.

Se precisar gerar de novo no seu PC:

```bash
composer install --no-dev --optimize-autoloader
```

Depois envie a pasta `vendor/` via FTP. **Não é obrigatório** ter SSH na hospedagem.

Dependências PHP usadas:
- `dompdf/dompdf` — recibos PDF
- `vlucas/phpdotenv` — configuração `.env`

---

## Passo 5 — Permissões

Pasta de uploads com escrita:

```
public/uploads/produtos/   → 755 ou 775
```

---

## Passo 6 — PHP no hPanel

1. hPanel → **Configuração PHP**
2. Versão: **PHP 8.1** ou **8.2**
3. Confirme extensões: `pdo_mysql`, `mbstring`

---

## Login

| Campo | Valor |
|-------|-------|
| URL | `https://luxuryurban.com.br/login` |
| Usuário | `marcus.lopes` |
| Senha | `*.Admin14!` |

---

## Problemas comuns

| Erro | Solução |
|------|---------|
| Página em branco | Ative `APP_DEBUG=true` no `.env` temporariamente |
| 404 em todas rotas | Verifique se `.htaccess` está em public/ ou public_html/ |
| Erro de banco | Confirme `DB_HOST=localhost` e credenciais no `.env` |
| Erro vendor/autoload | Envie a pasta `vendor/` completa |
| Upload de fotos falha | Permissão 775 em `uploads/produtos/` |
