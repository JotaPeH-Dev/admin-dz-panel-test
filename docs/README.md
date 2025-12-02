# 🔥 Admin D&Z - Painel Administrativo Completo

**Sistema de dashboard profissional desenvolvido em PHP com funcionalidades CRUD completas, design responsivo e tema dark/light.**

## ✨ Funcionalidades Principais

### 🔐 Sistema de Autenticação

- ✅ Login/logout seguro com hash de senhas
- ✅ Sessões protegidas e validação de acesso
- ✅ Redirecionamento automático para não logados

### 📊 Dashboard Interativo

- ✅ Painel responsivo com sidebar dinâmica
- ✅ Tema dark/light com persistência no localStorage
- ✅ Navegação intuitiva entre módulos

### 👥 Gerenciamento de Usuários (CRUD)

- ✅ Criar novos usuários admin via modal
- ✅ Editar informações (nome, email, senha, data nascimento)
- ✅ Excluir usuários (proteção contra auto-exclusão)
- ✅ Validação completa e prepared statements
- ✅ Feedback visual com mensagens de sucesso/erro
- ✅ Padrão PRG para evitar resubmissão de formulários

### 📱 Design Responsivo

- ✅ Interface otimizada para desktop, tablet e mobile
- ✅ Botões touch-friendly em dispositivos móveis
- ✅ Tabelas responsivas com scroll horizontal
- ✅ Sidebar colapsível para telas pequenas

### 🎨 Interface Moderna

- ✅ Material Symbols Icons
- ✅ CSS customizado com variáveis
- ✅ Animações suaves e transições
- ✅ Cards com sombras e efeitos hover

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Icons**: Google Material Symbols
- **Ambiente**: XAMPP (Apache + MySQL)

## ⚙️ Configuração Local

### 1. Pré-requisitos

- XAMPP instalado
- PHP 8.0 ou superior
- MySQL/MariaDB

### 2. Banco de Dados

Execute no MySQL:

```sql
CREATE DATABASE teste_dz;

CREATE TABLE teste_dz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_nascimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão
INSERT INTO teste_dz (nome, email, senha, data_nascimento)
VALUES ('Admin', 'admin@admin.com', '$2y$10$exemplo_hash_senha', '1990-01-01');
```

### 3. Instalação

1. Clone o repositório no htdocs do XAMPP
2. Configure `PHP/conexao.php` com suas credenciais do banco
3. Inicie Apache e MySQL no XAMPP
4. Acesse `http://localhost/admin-teste`

## 📋 Credenciais Padrão

- **Email**: admin@admin.com
- **Senha**: admin123

## 🗂️ Estrutura de Páginas

### 📊 Principais

- **Dashboard** (`index.php`) - Painel principal com estatísticas
- **Configurações** (`settings.php`) - Gerenciamento completo de usuários admin

### 🔄 Em Desenvolvimento

- **Clientes** (`customers.php`) - Gestão de clientes
- **Pedidos** (`orders.php`) - Controle de pedidos
- **Analytics** (`analytics.php`) - Gráficos e relatórios
- **Produtos** (`products.php`) - Catálogo de produtos
- **Mensagens** (`menssage.php`) - Sistema de comunicação
- **Adicionar Produtos** (`addproducts.php`) - Cadastro de produtos

### 🔐 Autenticação

- **Login** (`PHP/login.php`) - Página de acesso
- **Logout** (`PHP/logout.php`) - Encerrar sessão
- **Conexão** (`PHP/conexao.php`) - Configuração do banco

## 🎯 Como Usar

1. **Login**: Acesse `/PHP/login.php` com as credenciais
2. **Dashboard**: Navegue pelo painel principal
3. **Gerenciar Usuários**: Vá em Configurações → Gerenciamento de Usuários
4. **Criar Usuário**: Clique em "Novo Usuário" e preencha o modal
5. **Editar/Excluir**: Use os botões de ação na tabela
6. **Tema**: Toggle entre claro/escuro no canto superior direito

## 🔧 Funcionalidades Avançadas

### 🛡️ Segurança

- Senhas com hash bcrypt
- Prepared statements contra SQL injection
- Validação de sessão em todas as páginas
- Proteção contra CSRF e XSS

### 📱 Responsividade

- Mobile-first design
- Breakpoints: 480px, 768px, 840px, 1200px
- Touch-friendly buttons (44px mínimo)
- Scroll horizontal em tabelas pequenas

### 🎨 Customização

- CSS com variáveis para fácil personalização
- Tema dark/light automático
- Ícones Material Symbols
- Animações CSS suaves

## 📂 Estrutura de Arquivos

```
admin-teste/
├── PHP/
│   ├── conexao.php      # Conexão com banco
│   ├── login.php        # Página de login
│   ├── logout.php       # Logout
│   └── validar-login.php # Validação
├── images/              # Assets e logos
├── index.php           # Dashboard principal
├── settings.php        # CRUD de usuários
├── style.css          # Estilos principais
├── index.js           # JavaScript
└── *.php              # Outras páginas
```

## 🚀 Deploy

Para produção, configure:

- SSL/HTTPS obrigatório
- Variáveis de ambiente para credenciais do banco
- Backup automático do banco de dados
- Logs de auditoria para ações críticas

---

## 👨‍💻 Desenvolvedor

**Lucas Chacon**

- GitHub: [@ChaconLucas](https://github.com/ChaconLucas)
- Projeto: Sistema Admin D&Z

## 📄 Licença

Este projeto está sob licença MIT. Veja o arquivo `LICENSE` para mais detalhes.
