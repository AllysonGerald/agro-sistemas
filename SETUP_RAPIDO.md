# 🚀 **SETUP RÁPIDO - Agro Sistemas**

## ⚡ **Instalação em 5 Minutos**

### **1. Pré-requisitos**
```bash
# Verificar se tem Docker
docker --version
docker compose --version

# Verificar se tem Make (opcional)
make --version

# Verificar se tem Git
git --version
```

### **2. Clonar e Preparar**
```bash
# Clonar repositório
git clone <repository-url>
cd agro_sistemas

# Copiar configuração do backend
cd backend
cp laravel/.env.example laravel/.env
```

### **3. Subir Aplicação**

**Com Make (Recomendado):**
```bash
make up_build && make up && make setup
```

**Sem Make:**
```bash
docker compose up -d --build
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
```

### **4. Configurar Frontend**
```bash
cd ../frontend
npm install
npx vite --version  # Instalar Vite se necessário
npm run dev
```

### **5. Verificar se Funcionou**
```bash
# Testar API
curl -H "Accept: application/json" http://localhost:8080/api/v1/dashboard

# Testar Frontend
curl -s http://localhost:3000 | head -3

# Testar Redis (do diretório backend)
cd backend
docker compose exec redis redis-cli --no-auth-warning -a redispassword ping

# Testar Mailpit
curl -s http://localhost:32770 | head -1
```

### **6. URLs de Acesso**
- **Frontend**: http://localhost:3000
- **API**: http://localhost:8080
- **Documentação**: http://localhost:8080/docs/api
- **Mailpit**: http://localhost:32770
- **Dashboard**: http://localhost:8080/api/v1/dashboard

---

## 🔧 **Problemas Comuns**

### **Erro "vite: not found"**
```bash
cd frontend
npx vite --version
npm run dev
```

### **Erro de Conexão com Banco**
```bash
docker compose ps
docker compose logs postgres
```

### **Erro "APP_KEY not defined"**
```bash
docker compose exec php php artisan key:generate
```

### **API retorna HTML em vez de JSON**
```bash
docker compose exec php php artisan config:clear
docker compose exec php php artisan cache:clear
```

### **Frontend não carrega**
```bash
# Verificar se está na porta 3000
curl -s http://localhost:3000

# Verificar processo
ps aux | grep "npm run dev"
```

---

## 📋 **Comandos Úteis**

```bash
# Ver status dos containers
docker compose ps

# Ver logs
docker compose logs php
docker compose logs postgres
docker compose logs redis

# Parar tudo
docker compose down

# Reiniciar
docker compose restart
```

---

**Sistema desenvolvido para otimizar a gestão agropecuária em todo o Brasil**
