# 🚀 Deploy no Railway (Recomendado para PHP)

## Por que Railway é melhor para este projeto:
- ✅ Suporte completo ao PHP
- ✅ Banco de dados MySQL/PostgreSQL integrado
- ✅ Fácil configuração
- ✅ Gratuito para começar

## 📋 Passos para deploy no Railway:

### 1. Acesse Railway
- Vá para: https://railway.app
- Faça login com GitHub

### 2. Crie novo projeto
- Clique "Deploy from GitHub repo"
- Selecione: `ChaconLucas/admin-teste`

### 3. Configure banco de dados
- No dashboard do projeto, clique "Add Plugin"
- Escolha "MySQL" ou "PostgreSQL" 
- Railway criará automaticamente as variáveis:
  - `MYSQL_HOST`
  - `MYSQL_USER` 
  - `MYSQL_PASSWORD`
  - `MYSQL_DATABASE`

### 4. Configure variáveis de ambiente
Adicione no Railway:
```
DB_HOST=${{MYSQL_HOST}}
DB_USER=${{MYSQL_USER}}
DB_PASS=${{MYSQL_PASSWORD}}
DB_NAME=${{MYSQL_DATABASE}}
```

### 5. Deploy automático
- Railway detectará PHP automaticamente
- Deploy acontece em ~2 minutos

## 🔗 Seu projeto estará em:
`https://seu-projeto-production.up.railway.app`

---

# 🌐 Alternativa: Vercel (Limitado)

Se quiser usar Vercel mesmo assim:

1. **Renomeie:** `vercel-updated.json` → `vercel.json`
2. **Use apenas:** páginas estáticas + API routes
3. **Banco:** Use serviço externo (PlanetScale, Supabase)

## 💡 Recomendação Final:
**Use Railway** - É muito mais fácil e funciona 100% com PHP!