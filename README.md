# Admin D&Z - Sistema de Dashboard com PHP

## 🚀 Deploy

Este projeto está configurado para deploy no Vercel.

## 📋 Funcionalidades

- ✅ Sistema de login/logout com PHP
- ✅ Dashboard responsivo
- ✅ CRUD de usuários
- ✅ Navegação protegida por sessão
- ✅ MySQL/MariaDB integrado
- ✅ Bootstrap 5

## 🔧 Configuração

### Banco de Dados

1. Configure as variáveis de ambiente no Vercel:

   - `DB_HOST`: Host do banco de dados
   - `DB_USER`: Usuário do banco
   - `DB_PASS`: Senha do banco
   - `DB_NAME`: Nome do banco

2. Execute o SQL para criar a tabela:

```sql
CREATE TABLE teste_dz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    data_nascimento DATE,
    senha VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🖥️ Como usar

1. Acesse a página de login: `/PHP/login.php`
2. Crie usuários através de: `/PHP/criar-usuarios-teste.php`
3. Dashboard principal: `/index.php`

## 👤 Usuários padrão

- **Admin**: admin@teste.com / Senha: admin123

## 📱 Páginas

- Dashboard (`/index.php`)
- Clientes (`/customers.php`)
- Pedidos (`/orders.php`)
- Analytics (`/analytics.php`)
- Produtos (`/products.php`)
- Mensagens (`/menssage.php`)
- Configurações (`/settings.php`)
- Adicionar Produtos (`/addproducts.php`)

## 🔐 Sistema de Login

- Login: `/PHP/login.php`
- Logout: `/PHP/logout.php`
- CRUD Usuários: `/PHP/index.php`

---

Desenvolvido por **ChaconLucas**
