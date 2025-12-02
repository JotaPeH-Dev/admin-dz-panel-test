# Sistema D&Z - Chat com IA (Reorganizado)

Sistema de chat com inteligência artificial usando Groq API, organizado por linguagem para melhor manutenção.

## 🚀 Nova Estrutura Organizada

```
admin-teste/
├── backend/                 # Backend PHP consolidado
│   ├── api.php             # API única consolidada (endpoints client/admin)
│   └── conexao.php         # Configuração do banco
│
├── frontend/               # Frontend consolidado
│   └── chat-cliente.html   # Interface cliente completa (HTML/CSS/JS)
│
├── assets/                 # Assets e recursos
│   └── images/            # Imagens e logos
│       ├── Logodz.png
│       └── ...
│
├── PHP/                   # Scripts PHP legados (compatibilidade)
│   ├── login.php
│   ├── validar-login.php
│   └── ... (outros scripts do sistema)
│
├── Login_v3/             # Sistema de login
├── menssage.php          # Dashboard admin (atualizado)
├── index.php            # Dashboard principal
└── ... (outros arquivos do painel)
```

## 🎯 Consolidação Realizada

### Backend (1 arquivo)

- **`backend/api.php`**: API única consolidada contendo:
  - Configurações da Groq API
  - Classes GroqAPI e ChatManager
  - Conexão com banco de dados
  - Endpoints para cliente (`?endpoint=client`)
  - Endpoints para admin (`?endpoint=admin`)
  - Sistema de roteamento interno

### Frontend (1 arquivo)

- **`frontend/chat-cliente.html`**: Interface completa contendo:
  - HTML estruturado e semântico
  - CSS moderno com gradientes e animações
  - JavaScript para interação com API
  - Validações de formulário
  - Interface de chat responsiva

## 🔧 Configuração e Uso

### 1. Banco de Dados

```sql
-- Tabelas já existentes no banco 'teste_dz'
-- conversas: id, usuario_nome, usuario_email, status, created_at
-- mensagens: id, conversa_id, remetente, conteudo, timestamp, lida
```

### 2. API Endpoints

#### Cliente (endpoint=client)

```javascript
// Iniciar conversa
POST backend/api.php?endpoint=client&action=start_conversation
{
  "nome": "João Silva",
  "email": "joao@email.com",
  "mensagem": "Preciso de ajuda"
}

// Enviar mensagem
POST backend/api.php?endpoint=client&action=send_message
{
  "conversa_id": 123,
  "mensagem": "Nova mensagem"
}
```

#### Admin (endpoint=admin)

```javascript
// Listar conversas
GET backend/api.php?endpoint=admin&action=get_conversations

// Obter mensagens
GET backend/api.php?endpoint=admin&action=get_messages&conversa_id=123

// Enviar mensagem admin
POST backend/api.php?endpoint=admin&action=send_admin_message
{
  "conversa_id": 123,
  "mensagem": "Resposta do admin"
}

// Escalar para humano
POST backend/api.php?endpoint=admin&action=escalar_humano
{
  "conversa_id": 123
}

// Resolver conversa
POST backend/api.php?endpoint=admin&action=resolver_conversa
{
  "conversa_id": 123
}

// Estatísticas
GET backend/api.php?endpoint=admin&action=get_stats
```

## 🤖 Configuração Groq API

A API está configurada com:

- **Modelo**: `llama-3.3-70b-versatile`
- **API Key**: Configurada no início do `backend/api.php`
- **Temperature**: 0.7 para respostas naturais
- **Max Tokens**: 1000 por resposta

## 🎨 Interface

### Chat Cliente

- Design moderno com gradientes
- Formulário de contato inicial
- Interface de chat em tempo real
- Validações de email e campos
- Indicador de digitação da IA
- Responsivo para mobile

### Dashboard Admin

- Listagem de conversas em tempo real
- Estatísticas atualizadas
- Interface de chat integrada
- Botões para escalar/resolver
- Sistema de notificações

## 🚀 Como Usar

### Para o Cliente:

1. Acesse `frontend/chat-cliente.html`
2. Preencha nome, email e mensagem inicial
3. Clique em "Iniciar Conversa"
4. Continue a conversa normalmente

### Para Admin:

1. Faça login no sistema
2. Acesse `menssage.php`
3. Veja conversas ativas e estatísticas
4. Clique numa conversa para responder
5. Use botões para escalar ou resolver

## 📊 Vantagens da Reorganização

1. **Menos Arquivos**: De 6+ arquivos para 2 principais
2. **Organização**: Separação clara backend/frontend/assets
3. **Manutenção**: Código consolidado mais fácil de manter
4. **Paths Atualizados**: Todas referências corrigidas
5. **Performance**: Menos requisições HTTP
6. **Compatibilidade**: Sistema antigo preservado

## 🔄 Migrações Realizadas

- ✅ PHP consolidado em `backend/api.php`
- ✅ Frontend consolidado em `frontend/chat-cliente.html`
- ✅ Imagens movidas para `assets/images/`
- ✅ Referências atualizadas nos arquivos
- ✅ Sistema de roteamento implementado
- ✅ Endpoints padronizados
- ✅ Compatibilidade mantida

## 🛠️ Tecnologias

- **Backend**: PHP 8+, MySQL, Groq API
- **Frontend**: HTML5, CSS3 moderno, JavaScript ES6+
- **IA**: Groq com modelo Llama 3.3 70B
- **Servidor**: XAMPP (Apache + MySQL)

Sistema totalmente funcional e organizado! 🚀
