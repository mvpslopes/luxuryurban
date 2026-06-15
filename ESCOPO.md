# Luxury Urban — Escopo do Sistema de Gestão

> **Versão:** 1.0  
> **Data:** 15/06/2026  
> **Status:** Escopo fechado — pronto para implementação (Fase 1)  
> **Design:** Seguir rigorosamente `design.json` (dark mode, dashboard administrativo)

---

## 1. Visão geral

Sistema web interno em **PHP** para a **Luxury Urban** (loja de roupas e acessórios), hospedado em `luxuryurban.com.br`. O sistema cobre gestão de usuários, produtos, estoque, clientes, vendas, descontos controlados, estornos e emissão de recibos em PDF.

**Objetivo de negócio:** centralizar operação da loja em um painel único, com controle de permissões por perfil e rastreabilidade de movimentações de estoque e vendas.

**Usuários do sistema:** equipe interna (Root, Admin, Vendedor). Não é e-commerce público nesta fase.

---

## 2. Stack tecnológica proposta

| Camada | Tecnologia | Justificativa |
|--------|-----------|---------------|
| Backend | PHP 8.2+ | Compatível com hospedagem compartilhada/VPS comum no Brasil |
| Banco | MySQL / MariaDB | Banco já provisionado (`u179630068_luxuryurban`) |
| Acesso a dados | PDO (prepared statements) | Segurança contra SQL injection |
| Dependências | Composer | Dompdf (PDF), autoload PSR-4 |
| Frontend | HTML + CSS + JavaScript vanilla | Leve, sem build step; alinhado ao `design.json` |
| Ícones | Lucide Icons ou Heroicons (SVG inline) | Consistente com design outline |
| Fonte | Inter (Google Fonts) | Definida no design system |
| Sessões | PHP native sessions + regeneração pós-login | Autenticação stateful |
| Uploads | `storage/uploads/produtos/` | Fotos de produtos com validação MIME |
| Config | `.env` (nunca versionado) | Credenciais fora do código |

### Dependências Composer previstas

```
dompdf/dompdf          → Geração de recibos PDF
vlucas/phpdotenv       → Variáveis de ambiente (opcional, recomendado)
```

---

## 3. Ambiente e configuração

### 3.1 Banco de dados (produção)

| Parâmetro | Valor |
|-----------|-------|
| Host | `localhost` (hospedagem compartilhada — confirmado via painel) |
| Database | `u179630068_luxuryurban` |
| Usuário | `u179630068_luxuryurban_us` |
| Domínio | `luxuryurban.com.br` |
| Criado em | 2026-06-15 |

> **Segurança:** credenciais devem ficar exclusivamente em `.env`. O arquivo `.env` entra no `.gitignore`. Nunca commitar senhas no repositório.

### 3.2 Usuário Root seed

| Campo | Valor |
|-------|-------|
| Usuário | `marcus.lopes` |
| Senha | `*.Admin14!` |
| Hash bcrypt | `$2a$12$3JlP15IEn6rdsteMoEnxN.sJ1ZMdGqq7XgQ/1EMjbKc.hQ5uuHOUK` |
| Perfil | `root` |

Este usuário é inserido via migration/seed inicial e é o único que pode criar novos usuários.

---

## 4. Perfis e matriz de permissões

### 4.1 Definição dos perfis

| Perfil | Descrição |
|--------|-----------|
| **Root** | Superusuário. Gestão de usuários do sistema. Acesso total. |
| **Admin** | Gestão de produtos, categorias, formas de pagamento, estoque, vendas no PDV, estornos e aprovação de descontos acima de 10% (de vendedores). |
| **Vendedor** | Cadastro de clientes, vendas, estornos e descontos até 10%. |

### 4.2 Matriz de permissões

| Funcionalidade | Root | Admin | Vendedor |
|----------------|:----:|:-----:|:--------:|
| Login / logout | ✅ | ✅ | ✅ |
| Dashboard (métricas) | ✅ | ✅ | ✅ (limitado) |
| Criar usuários | ✅ | ❌ | ❌ |
| Editar/desativar usuários | ✅ | ❌ | ❌ |
| CRUD produtos | ✅ | ✅ | 👁️ leitura |
| CRUD categorias de produto | ✅ | ✅ | 👁️ leitura |
| CRUD formas de pagamento | ✅ | ✅ | 👁️ leitura |
| Upload fotos de produtos | ✅ | ✅ | ❌ |
| Gestão de estoque (entrada/saída/ajuste) | ✅ | ✅ | 👁️ leitura |
| CRUD clientes | ✅ | 👁️ | ✅ (qualquer cliente da loja) |
| Realizar vendas (PDV) | ✅ | ✅ | ✅ |
| Aplicar desconto ≤ 10% | ✅ | ✅ | ✅ |
| Aplicar desconto > 10% | ✅ | ✅ (direto na própria venda) | ⚠️ requer aprovação Admin |
| Estornar vendas | ✅ | ✅ | ✅ |
| Gerar recibo PDF | ✅ | ✅ | ✅ |
| Relatórios completos | ✅ | ✅ | 👁️ próprias vendas |
| Logs de auditoria | ✅ | 👁️ | ❌ |

**Legenda:** ✅ acesso total · 👁️ somente leitura · ❌ sem acesso · ⚠️ fluxo especial

### 4.3 Regras de desconto

```
Desconto solicitado ≤ 10%  → Vendedor ou Admin aplica diretamente na venda

Desconto solicitado > 10%  → Vendedor: venda fica "pendente_aprovacao"
                           → Admin recebe notificação no dashboard
                           → Admin aprova ou rejeita
                           → Se aprovado, vendedor finaliza a venda

                           → Admin (própria venda): aplica qualquer desconto
                             diretamente, sem fluxo de aprovação
                           → Root: aplica qualquer desconto diretamente
```

- Desconto pode ser **percentual** ou **valor fixo** (convertido internamente para % sobre subtotal).
- Toda alteração de desconto acima do limite gera registro em `discount_approvals`.

---

## 5. Módulos funcionais

### 5.1 Autenticação e sessão

- Tela de login (usuário + senha).
- Senhas armazenadas com **bcrypt** (`password_hash` / `password_verify`).
- Sessão regenerada após login bem-sucedido.
- Middleware de autenticação em todas as rotas protegidas.
- Middleware de autorização por perfil (`root`, `admin`, `vendedor`).
- Logout destrói sessão.
- Usuários inativos não conseguem logar.

### 5.2 Gestão de usuários (somente Root)

**Campos do usuário:**
- Nome completo
- Username (único)
- E-mail (opcional)
- Perfil (`admin` | `vendedor` — Root não cria outro Root via UI)
- Senha (definida na criação, mínimo 8 caracteres)
- Status (ativo/inativo)

**Ações:**
- Listar usuários (tabela com filtros)
- Criar usuário
- Editar dados e perfil
- Ativar/desativar
- Resetar senha (Root define nova senha)

### 5.3 Gestão de produtos (Admin)

**Campos do produto:**
- Nome *
- Descrição (textarea)
- SKU / código interno (único, auto-gerável)
- Preço de venda *
- Preço de custo (opcional, Admin only)
- Categoria * (selecionar cadastrada — ver seção 5.3.1)
- Status (ativo/inativo)
- Fotos (múltiplas, 1 principal)
- Quantidade em estoque (vinculada ao módulo de estoque)

**Ações:**
- Listar produtos (busca, filtro por categoria/status)
- Criar / editar / inativar produto
- Upload de até **5 fotos** por produto (JPG, PNG, WebP — máx. 2 MB cada)
- Reordenar fotos e definir capa
- Visualizar histórico de movimentações de estoque do produto

#### 5.3.1 Categorias de produto (cadastro dinâmico)

Admin (e Root) gerenciam categorias em tela dedicada. Novas categorias podem ser cadastradas a qualquer momento.

**Campos da categoria:**
- Nome * (único, ex: Roupas, Acessórios)
- Status (ativo/inativo)
- Ordem de exibição (opcional)

**Ações (Admin / Root):**
- Listar categorias
- Criar nova categoria
- Editar nome e status
- Inativar categoria (não excluir se houver produtos vinculados)

**Regras:**
- Categorias **inativas** não aparecem ao cadastrar/editar produto nem nos filtros do PDV
- Produtos já vinculados mantêm a categoria mesmo se esta for inativada
- Seed inicial sugere: Roupas, Acessórios, Calçados, Outros

**Permissão Vendedor:** somente leitura (visualiza categorias nos produtos).

### 5.4 Gestão de estoque (Admin)

**Operações de movimentação:**

| Tipo | Descrição | Efeito no saldo |
|------|-----------|-----------------|
| `entrada` | Compra/reposição manual | +quantidade |
| `saida` | Baixa manual (perda, doação) | −quantidade |
| `ajuste` | Correção de inventário | define saldo |
| `venda` | Automático ao confirmar venda | −quantidade |
| `estorno` | Automático ao estornar venda | +quantidade |

**Funcionalidades:**
- Painel de estoque com saldo atual por produto
- Alertas visuais de estoque baixo (configurável por produto, padrão: ≤ 5 un.)
- Histórico completo de movimentações (quem, quando, motivo, referência)
- Impedir venda se estoque insuficiente
- Relatório de movimentações por período

### 5.5 Gestão de clientes (Vendedor e Admin no PDV)

Clientes pertencem à **loja**, não ao vendedor. Qualquer vendedor (ou Admin no PDV) pode listar, criar e **editar qualquer cliente**.

**Campos do cliente:**
- Nome completo *
- CPF/CNPJ (único, validação básica)
- E-mail
- Telefone / WhatsApp
- Endereço (rua, número, bairro, cidade, UF, CEP)
- Observações
- Cadastrado por (auditoria — registra quem criou, sem restrição de edição)

**Ações:**
- Listar / buscar clientes (todos os clientes da loja)
- Criar / editar qualquer cliente
- Visualizar histórico de compras do cliente

### 5.6 Vendas — PDV (Vendedor e Admin)

Admin tem acesso completo ao PDV, com as mesmas telas e fluxo do Vendedor. A diferença está apenas nas regras de desconto (seção 4.3): Admin aplica descontos acima de 10% diretamente em vendas próprias. No PDV, Admin pode selecionar clientes existentes ou fazer cadastro rápido de novo cliente (mesmo fluxo do Vendedor).

**Fluxo da venda:**

```
1. Selecionar ou cadastrar cliente
2. Adicionar produtos ao carrinho (busca por nome/SKU)
3. Definir quantidade por item (validar estoque)
4. Aplicar desconto (Vendedor: se > 10%, acionar fluxo de aprovação; Admin: livre)
5. Selecionar forma de pagamento *
6. Revisar totais (subtotal, desconto, total)
7. Confirmar venda
   → Baixa estoque automaticamente
   → Gera número de recibo sequencial (ex: LU-2026-00001)
   → Disponibiliza download/visualização PDF
```

**Campos da venda:**
- Número do recibo (auto)
- Cliente
- Vendedor
- Forma de pagamento * (obrigatório)
- Itens (produto, qtd, preço unitário, subtotal)
- Subtotal
- Desconto (% ou R$)
- Total final
- Status: `concluida` | `pendente_aprovacao` | `estornada`
- Data/hora

#### 5.6.1 Formas de pagamento (cadastro dinâmico)

Admin (e Root) gerenciam formas de pagamento em tela dedicada. Novas formas podem ser cadastradas a qualquer momento.

**Campos da forma de pagamento:**
- Nome * (único, ex: Dinheiro, Pix, Cartão de Crédito)
- Status (ativo/inativo)
- Ordem de exibição (opcional)

**Ações (Admin / Root):**
- Listar formas de pagamento
- Criar nova forma
- Editar nome e status
- Inativar forma (não excluir se houver vendas vinculadas)

**Regras:**
- Formas **inativas** não aparecem no PDV
- Campo obrigatório no PDV — vendedor seleciona entre formas **ativas**
- Nome da forma é gravado como snapshot na venda (`payment_method_name`) para histórico fiel
- Sem integração com gateways — apenas registro da forma escolhida
- Exibido no recibo PDF e no histórico de vendas
- Seed inicial sugere: Dinheiro, Pix, Cartão de Crédito, Cartão de Débito, Promissória

**Permissão Vendedor:** somente seleção no PDV (lista de ativas).

### 5.7 Estorno de vendas (Vendedor e Admin)

**Regras:**
- Apenas vendas com status `concluida` podem ser estornadas
- Estorno **sempre completo** — devolve todos os itens da venda (sem estorno parcial)
- Estorno restaura estoque de **todos** os itens da venda
- Motivo do estorno obrigatório (campo texto)
- Após estorno → status `estornada` (venda não pode ser estornada novamente)
- Recibo PDF original permanece; comprovante de estorno (opcional fase 2)

### 5.8 Recibo PDF

**Conteúdo do recibo:**
- Logo / nome **Luxury Urban**
- Número do recibo e data
- Dados do cliente
- Tabela de itens (produto, qtd, preço unit., subtotal)
- Subtotal, desconto, total
- Forma de pagamento
- Nome do vendedor
- Rodapé: `luxuryurban.com.br`

**Geração:** Dompdf, layout limpo (fundo branco no PDF — exceção ao dark mode da UI).

### 5.9 Dashboard

**Root / Admin — métricas:**
- Total de vendas do dia / mês
- Faturamento do dia / mês
- Produtos com estoque baixo (lista)
- Vendas pendentes de aprovação de desconto
- Gráfico de vendas (últimos 30 dias) — linha spline conforme `design.json`
- Top produtos vendidos

**Vendedor — métricas:**
- Minhas vendas do dia
- Meu faturamento do dia / mês
- Total de clientes da loja (ou clientes que cadastrei — métrica secundária)

---

## 6. Modelo de dados (MySQL)

### 6.1 Diagrama entidade-relacionamento (simplificado)

```
users ──────────────┬── sales (seller_id)
                    ├── stock_movements (user_id)
                    ├── discount_approvals (requested_by / approved_by)
                    └── sale_refunds (user_id)

customers ────────── sales (customer_id)

product_categories ─ products (category_id)

payment_methods ──── sales (payment_method_id)

products ───────────┬── product_images
                    ├── stock (1:1 saldo atual)
                    ├── stock_movements
                    └── sale_items

sales ──────────────┬── sale_items
                    ├── discount_approvals
                    └── sale_refunds
```

### 6.2 Tabelas

#### `users`
```sql
id              INT UNSIGNED PK AI
name            VARCHAR(120) NOT NULL
username        VARCHAR(60) UNIQUE NOT NULL
email           VARCHAR(120) NULL
password_hash   VARCHAR(255) NOT NULL
role            ENUM('root','admin','vendedor') NOT NULL
active          TINYINT(1) DEFAULT 1
created_at      DATETIME
updated_at      DATETIME
```

updated_at      DATETIME
```

#### `product_categories`
```sql
id              INT UNSIGNED PK AI
name            VARCHAR(80) UNIQUE NOT NULL
active          TINYINT(1) DEFAULT 1
sort_order      INT DEFAULT 0
created_at      DATETIME
updated_at      DATETIME
```

#### `payment_methods`
```sql
id              INT UNSIGNED PK AI
name            VARCHAR(80) UNIQUE NOT NULL
active          TINYINT(1) DEFAULT 1
sort_order      INT DEFAULT 0
created_at      DATETIME
updated_at      DATETIME
```

#### `products`
```sql
id              INT UNSIGNED PK AI
sku             VARCHAR(50) UNIQUE NOT NULL
name            VARCHAR(200) NOT NULL
description     TEXT NULL
category_id     INT UNSIGNED FK → product_categories NOT NULL
price           DECIMAL(10,2) NOT NULL
cost_price      DECIMAL(10,2) NULL
min_stock       INT DEFAULT 5
active          TINYINT(1) DEFAULT 1
created_at      DATETIME
updated_at      DATETIME
```

#### `product_images`
```sql
id              INT UNSIGNED PK AI
product_id      INT UNSIGNED FK → products
filename        VARCHAR(255) NOT NULL
is_primary      TINYINT(1) DEFAULT 0
sort_order      INT DEFAULT 0
created_at      DATETIME
```

#### `stock`
```sql
product_id      INT UNSIGNED PK FK → products
quantity        INT NOT NULL DEFAULT 0
updated_at      DATETIME
```

#### `stock_movements`
```sql
id              INT UNSIGNED PK AI
product_id      INT UNSIGNED FK → products
type            ENUM('entrada','saida','ajuste','venda','estorno') NOT NULL
quantity        INT NOT NULL          -- positivo ou negativo conforme tipo
balance_after   INT NOT NULL
reference_type  VARCHAR(40) NULL      -- 'sale', 'refund', 'manual'
reference_id    INT UNSIGNED NULL
notes           TEXT NULL
user_id         INT UNSIGNED FK → users
created_at      DATETIME
```

#### `customers`
```sql
id              INT UNSIGNED PK AI
name            VARCHAR(200) NOT NULL
document        VARCHAR(20) UNIQUE NULL   -- CPF/CNPJ
email           VARCHAR(120) NULL
phone           VARCHAR(20) NULL
address_street  VARCHAR(200) NULL
address_number  VARCHAR(20) NULL
address_neighborhood VARCHAR(100) NULL
address_city    VARCHAR(100) NULL
address_state   CHAR(2) NULL
address_zip     VARCHAR(10) NULL
notes           TEXT NULL
created_by      INT UNSIGNED FK → users
created_at      DATETIME
updated_at      DATETIME
```

#### `sales`
```sql
id              INT UNSIGNED PK AI
receipt_number  VARCHAR(20) UNIQUE NOT NULL   -- LU-2026-00001
customer_id     INT UNSIGNED FK → customers
seller_id       INT UNSIGNED FK → users
subtotal        DECIMAL(10,2) NOT NULL
discount_type   ENUM('percent','fixed') NULL
discount_value  DECIMAL(10,2) DEFAULT 0
discount_amount DECIMAL(10,2) DEFAULT 0
total           DECIMAL(10,2) NOT NULL
payment_method_id   INT UNSIGNED FK → payment_methods NOT NULL
payment_method_name VARCHAR(80) NOT NULL   -- snapshot no momento da venda
status          ENUM('pendente_aprovacao','concluida','estornada') NOT NULL
notes           TEXT NULL
created_at      DATETIME
updated_at      DATETIME
```

#### `sale_items`
```sql
id              INT UNSIGNED PK AI
sale_id         INT UNSIGNED FK → sales
product_id      INT UNSIGNED FK → products
product_name    VARCHAR(200) NOT NULL    -- snapshot no momento da venda
quantity        INT NOT NULL
unit_price      DECIMAL(10,2) NOT NULL
subtotal        DECIMAL(10,2) NOT NULL
```

#### `discount_approvals`
```sql
id              INT UNSIGNED PK AI
sale_id         INT UNSIGNED FK → sales UNIQUE
requested_by    INT UNSIGNED FK → users
approved_by     INT UNSIGNED FK → users NULL
discount_percent DECIMAL(5,2) NOT NULL
status          ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente'
notes           TEXT NULL
created_at      DATETIME
resolved_at     DATETIME NULL
```

#### `sale_refunds`
```sql
id              INT UNSIGNED PK AI
sale_id         INT UNSIGNED FK → sales
user_id         INT UNSIGNED FK → users
reason          TEXT NOT NULL
refund_total    DECIMAL(10,2) NOT NULL
created_at      DATETIME
```

> Estorno sempre completo: `sale_refunds` referencia a venda inteira. Itens restaurados ao estoque são inferidos de `sale_items` da venda estornada — sem tabela `sale_refund_items`.

#### `audit_logs` (recomendado)
```sql
id              INT UNSIGNED PK AI
user_id         INT UNSIGNED FK → users NULL
action          VARCHAR(80) NOT NULL
entity_type     VARCHAR(40) NULL
entity_id       INT UNSIGNED NULL
details         JSON NULL
ip_address      VARCHAR(45) NULL
created_at      DATETIME
```

---

## 7. Mapa de telas

### 7.1 Telas públicas

| Rota | Tela |
|------|------|
| `/login` | Login |

### 7.2 Telas Root

| Rota | Tela |
|------|------|
| `/dashboard` | Dashboard geral |
| `/usuarios` | Lista de usuários |
| `/usuarios/novo` | Criar usuário |
| `/usuarios/{id}/editar` | Editar usuário |

### 7.3 Telas Admin (+ Root)

| Rota | Tela |
|------|------|
| `/produtos` | Lista de produtos |
| `/produtos/novo` | Cadastrar produto |
| `/produtos/{id}/editar` | Editar produto + fotos |
| `/categorias` | Lista de categorias de produto |
| `/categorias/novo` | Cadastrar categoria |
| `/categorias/{id}/editar` | Editar categoria |
| `/formas-pagamento` | Lista de formas de pagamento |
| `/formas-pagamento/novo` | Cadastrar forma de pagamento |
| `/formas-pagamento/{id}/editar` | Editar forma de pagamento |
| `/estoque` | Painel de estoque |
| `/estoque/movimentacao` | Registrar entrada/saída/ajuste |
| `/estoque/historico` | Histórico de movimentações |
| `/aprovacoes` | Descontos pendentes de aprovação |
| `/clientes` | Lista de clientes (leitura + uso no PDV) |
| `/vendas/nova` | PDV — nova venda |
| `/vendas` | Histórico de vendas |
| `/vendas/{id}` | Detalhe da venda + PDF |
| `/vendas/{id}/estornar` | Estorno |

### 7.4 Telas Vendedor (+ Root)

| Rota | Tela |
|------|------|
| `/clientes` | Lista de clientes |
| `/clientes/novo` | Cadastrar cliente |
| `/clientes/{id}/editar` | Editar cliente |
| `/vendas` | Histórico de vendas |
| `/vendas/nova` | PDV — nova venda |
| `/vendas/{id}` | Detalhe da venda + PDF |
| `/vendas/{id}/estornar` | Estorno |

### 7.5 Layout comum (design.json)

Todas as telas autenticadas compartilham:
- **Sidebar** colapsável com navegação filtrada por perfil
- **Header** com breadcrumbs + título + ações contextuais
- **Cards** de métricas no dashboard
- **Tabelas** com paginação, busca e badges de status
- Tema **dark mode** (`#0D0D0D` / `#1A1A1A`)

**Itens da sidebar por perfil:**

| Item | Root | Admin | Vendedor |
|------|:----:|:-----:|:--------:|
| Dashboard | ✅ | ✅ | ✅ |
| Usuários | ✅ | — | — |
| Produtos | ✅ | ✅ | — |
| Categorias | ✅ | ✅ | — |
| Formas de pagamento | ✅ | ✅ | — |
| Estoque | ✅ | ✅ | — |
| Clientes | ✅ | 👁️ | ✅ |
| Nova Venda | ✅ | ✅ | ✅ |
| Vendas | ✅ | ✅ | ✅ |
| Aprovações | ✅ | ✅ | — |

---

## 8. Arquitetura do projeto

### 8.1 Estrutura de diretórios

```
luxuryurban/
├── public/                    # Document root (luxuryurban.com.br aponta aqui)
│   ├── index.php              # Front controller
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css        # Tokens do design.json
│   │   ├── js/
│   │   │   └── app.js         # PDV, sidebar, interações
│   │   └── img/
│   └── uploads/               # Symlink ou redirect → storage
├── app/
│   ├── Controllers/
│   │   AuthController.php
│   │   DashboardController.php
│   │   UserController.php
│   │   ProductController.php
│   │   CategoryController.php
│   │   PaymentMethodController.php
│   │   StockController.php
│   │   CustomerController.php
│   │   SaleController.php
│   │   RefundController.php
│   │   DiscountApprovalController.php
│   ├── Models/
│   │   User.php
│   │   Product.php
│   │   ProductCategory.php
│   │   PaymentMethod.php
│   │   Stock.php
│   │   Customer.php
│   │   Sale.php
│   │   ...
│   ├── Services/
│   │   AuthService.php
│   │   StockService.php       # Lógica transacional de estoque
│   │   SaleService.php        # Fluxo completo de venda
│   │   PdfService.php         # Geração de recibo
│   │   DiscountService.php    # Validação de limites
│   ├── Middleware/
│   │   AuthMiddleware.php
│   │   RoleMiddleware.php
│   ├── Views/
│   │   layouts/
│   │   │   app.php            # Sidebar + header
│   │   │   auth.php           # Layout login
│   │   auth/
│   │   dashboard/
│   │   users/
│   │   products/
│   │   categories/
│   │   payment_methods/
│   │   stock/
│   │   customers/
│   │   sales/
│   │   ...
│   └── Helpers/
│       functions.php
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── migrations/
│   │   001_create_users.sql
│   │   002_create_products.sql
│   │   ...
│   └── seeds/
│       001_root_user.sql
│       002_categories_and_payment_methods.sql
├── storage/
│   └── uploads/
│       └── produtos/
├── .env.example
├── .gitignore
├── composer.json
├── design.json
└── ESCOPO.md
```

### 8.2 Padrão arquitetural

- **MVC simplificado** com front controller (`public/index.php`).
- Rotas definidas em array associativo (sem framework pesado).
- **Services** concentram regras de negócio e transações DB.
- **Controllers** finos: validam input, chamam service, retornam view.
- Transações MySQL (`BEGIN / COMMIT / ROLLBACK`) em operações críticas: venda, estorno, movimentação de estoque.

### 8.3 Fluxo de requisição

```
Request → public/index.php
       → Router (method + path)
       → Middleware Auth
       → Middleware Role
       → Controller
       → Service (regras + DB transaction)
       → View (render)
       → Response
```

---

## 9. Regras de negócio críticas

| # | Regra |
|---|-------|
| R1 | Apenas Root cria/edita usuários |
| R2 | Produto inativo não aparece no PDV |
| R3 | Venda bloqueada se estoque < quantidade solicitada |
| R4 | Desconto vendedor limitado a 10%; acima exige aprovação Admin. Admin aplica qualquer desconto em vendas próprias |
| R5 | Estorno sempre completo — restaura estoque de todos os itens da venda |
| R6 | Toda movimentação de estoque gera registro em `stock_movements` |
| R7 | Preço unitário na venda é snapshot — alterações futuras no produto não afetam vendas passadas |
| R8 | Número de recibo sequencial e imutável por ano |
| R9 | Venda `pendente_aprovacao` não baixa estoque até aprovação |
| R10 | Usuário inativo não autentica |
| R11 | Clientes são da loja — qualquer vendedor edita qualquer cliente |
| R12 | Forma de pagamento obrigatória em toda venda — selecionada entre formas **ativas** cadastradas |
| R13 | Categorias e formas de pagamento são cadastráveis pelo Admin; inativar em vez de excluir quando houver vínculos |
| R14 | Snapshot de `payment_method_name` na venda preserva histórico mesmo se a forma for renomeada/inativada depois |

---

## 10. Requisitos não funcionais

| Requisito | Especificação |
|-----------|---------------|
| Segurança | HTTPS obrigatório, CSRF token em formulários, prepared statements, escape XSS nas views |
| Performance | Índices em FKs e campos de busca (username, sku, receipt_number, document) |
| Responsivo | Desktop-first; sidebar colapsa em tablet/mobile |
| Backup | Recomendado backup diário do MySQL via painel de hospedagem |
| Logs | `audit_logs` para ações sensíveis (login, estorno, aprovação desconto, CRUD usuários) |
| Upload | Validar MIME real, renomear arquivos (UUID), limitar tamanho |
| Sessão | Timeout 8h de inatividade |

---

## 11. Fora de escopo (fase 1)

- E-commerce público / vitrine online
- Integração com gateways de pagamento (PIX, cartão) — apenas registro manual da forma de pagamento
- Nota fiscal eletrônica (NF-e)
- App mobile nativo
- Multi-loja / filiais
- Controle de comissão de vendedores
- Integração WhatsApp / e-mail transacional
- Recuperação de senha por e-mail (fase 2)

---

## 12. Fases de implementação

### Fase 1 — Fundação (≈ 3–4 dias)
- [ ] Estrutura de pastas e Composer
- [ ] Config `.env` + conexão PDO
- [ ] Migrations de todas as tabelas
- [ ] Seed do usuário Root
- [ ] Sistema de rotas + middleware auth/role
- [ ] Layout base (sidebar, header) conforme `design.json`
- [ ] Tela de login

### Fase 2 — Usuários (≈ 1–2 dias)
- [ ] CRUD usuários (Root only)
- [ ] Listagem com tabela estilizada

### Fase 3 — Produtos, categorias e estoque (≈ 4–5 dias)
- [ ] CRUD categorias de produto (Admin)
- [ ] CRUD produtos + upload de fotos
- [ ] CRUD formas de pagamento (Admin)
- [ ] Painel de estoque
- [ ] Movimentações manuais (entrada/saída/ajuste)
- [ ] Alertas de estoque baixo

### Fase 4 — Clientes (≈ 1–2 dias)
- [ ] CRUD clientes (Vendedor — acesso a todos os clientes da loja)
- [ ] Busca e histórico de compras

### Fase 5 — Vendas e PDV (≈ 4–5 dias)
- [ ] Tela PDV (carrinho dinâmico com JS)
- [ ] Seleção obrigatória de forma de pagamento (lista dinâmica de ativas)
- [ ] Validação de estoque em tempo real
- [ ] Fluxo de desconto + aprovação Admin
- [ ] Geração de recibo PDF (com forma de pagamento)
- [ ] Histórico de vendas

### Fase 6 — Estornos (≈ 1–2 dias)
- [ ] Estorno completo de venda
- [ ] Restauração de estoque de todos os itens
- [ ] Atualização de status da venda

### Fase 7 — Dashboard e polish (≈ 2–3 dias)
- [ ] Métricas e gráfico de vendas
- [ ] Fila de aprovações no dashboard Admin
- [ ] Audit logs
- [ ] Testes manuais end-to-end
- [ ] Deploy em luxuryurban.com.br

**Estimativa total:** 16–22 dias úteis

---

## 13. Critérios de aceite

1. Root consegue logar com `marcus.lopes` e criar usuários Admin e Vendedor.
2. Admin cadastra produto com fotos e gerencia estoque (entrada, saída, ajuste).
3. Admin realiza venda no PDV, incluindo desconto acima de 10% sem aprovação.
4. Vendedor cadastra cliente e realiza venda com desconto de 10% sem bloqueio.
5. Vendedor tenta desconto de 15% → venda fica pendente → Admin aprova → venda concluída.
6. Estoque é baixado automaticamente na venda e restaurado no estorno completo.
7. Recibo PDF inclui forma de pagamento e todos os dados da venda.
8. Vendedor edita cliente cadastrado por outro vendedor sem restrição.
9. Interface segue o dark mode e componentes definidos em `design.json`.
10. Admin cadastra nova categoria e nova forma de pagamento; ambas aparecem nos selects do sistema.
11. Credenciais do banco não estão expostas no código versionado.

---

## 14. Decisões do projeto

### Confirmadas

| # | Questão | Decisão |
|---|---------|---------|
| D1 | Vendedor pode editar cliente cadastrado por outro vendedor? | **Sim** — clientes são da loja; qualquer vendedor edita qualquer cliente. Campo `created_by` apenas para auditoria. |
| D2 | Admin pode realizar vendas no PDV? | **Sim** — Admin acessa PDV, histórico, estornos e PDF. Descontos acima de 10% aplicados diretamente em vendas próprias, sem aprovação. |
| D3 | Estorno parcial por quantidade? | **Não** — estorno sempre **completo** (todos os itens da venda). Status final: `estornada`. |
| D4 | Categorias fixas ou livres? | **Cadastro dinâmico** — Admin cria/edita/inativa categorias. Seed inicial: Roupas, Acessórios, Calçados, Outros. |
| D5 | Forma de pagamento no PDV? | **Cadastro dinâmico + obrigatório no PDV** — Admin cria/edita/inativa formas. Seed inicial: Dinheiro, Pix, Cartão de Crédito, Cartão de Débito, Promissória. Sem integração com gateway. |
| D6 | Múltiplas filiais? | **Não** — loja única. |
| D7 | Host MySQL é `localhost` ou remoto? | **`localhost`** — padrão da hospedagem compartilhada (painel Hostinger, banco `u179630068_luxuryurban`). |

---

## 15. Próximo passo

Escopo **aprovado**. Implementação inicia pela **Fase 1 — Fundação** (estrutura PHP, migrations, auth, layout).
