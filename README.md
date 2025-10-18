# 🚀 Agro Sistemas - Sistema Completo de Gestão Agropecuária

Sistema completo de gestão agropecuária desenvolvido com Laravel 12 (Backend) + Vue.js 3 (Frontend), para todo o território brasileiro. Interface moderna e robusta para gerenciamento de produtores rurais, propriedades, rebanhos, unidades de produção agrícola, relatórios avançados e dashboard em tempo real.

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.4-blue?style=flat-square&logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-blue?style=flat-square&logo=postgresql)
![Docker](https://img.shields.io/badge/Docker-Compose-blue?style=flat-square&logo=docker)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green?style=flat-square&logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5-blue?style=flat-square&logo=typescript)

## 🆕 **NOVAS FUNCIONALIDADES IMPLEMENTADAS**

### ✨ **Sistema de Atividades Recentes**
- **Auto-refresh** a cada 30 segundos
- **Logging automático** de todas as operações (CRUD)
- **Interface moderna** com ícones e cores específicas
- **Tradução automática** de ações para português

### 📊 **Sistema de Relatórios Avançado**
- **8 tipos de relatórios** especializados
- **Exportação em PDF, Excel e CSV** com formatação profissional
- **Validação inteligente** para evitar relatórios vazios
- **Busca com autocomplete** para propriedades
- **Design moderno** com cores e layout otimizados

### 🎯 **Dashboard em Tempo Real**
- **Estatísticas dinâmicas** atualizadas automaticamente
- **Gráficos interativos** com dados reais
- **Métricas de performance** do sistema
- **Interface responsiva** para todos os dispositivos

### 🔍 **Sistema de Busca Inteligente**
- **Busca com acentos** (melã encontra melão)
- **Paginação otimizada** com performance melhorada
- **Filtros avançados** por múltiplos campos
- **Autocomplete** para propriedades e produtores

## 🧪 **TESTES AUTOMATIZADOS**

O sistema possui testes automatizados básicos que garantem a qualidade e estabilidade do código:

### ✅ **Testes Implementados**

#### **Testes Unitários (Unit Tests)**
- **ModelsTest**: Testa criação e validação básica de usuários
- **EnumsTest**: Valida enums de tipos de cultura e suas traduções
- **Cobertura**: Modelos principais e enums essenciais

#### **Testes de Integração (Feature Tests)**
- **BasicApiTest**: Testa rotas básicas da API e autenticação
- **Cobertura**: Rotas principais da API (dashboard, produtores, propriedades, rebanhos, unidades, relatórios)
- **Validação**: Estrutura de resposta JSON, códigos de status HTTP

### 🚀 **Executando os Testes**

```bash
# Executar todos os testes
cd backend/laravel
php artisan test

# Executar apenas testes unitários
php artisan test --testsuite=Unit

# Executar apenas testes de integração
php artisan test --testsuite=Feature
```

### 📊 **Resultados dos Testes**
- **18 testes** implementados
- **282 asserções** executadas
- **100% de sucesso** em todos os testes
- **Tempo de execução**: ~0.44s

### 🔧 **Estrutura dos Testes**
```
tests/
├── Unit/
│   ├── ModelsTest.php          # Testes básicos de modelos
│   └── EnumsTest.php           # Testes de enums
└── Feature/
    └── Api/
        └── BasicApiTest.php    # Testes básicos de rotas da API
```

> **ℹ️ Nota**: Os testes foram simplificados para focar na funcionalidade básica sem interferir na estrutura do sistema em produção.

## 🚀 **SETUP COMPLETO - Passo a Passo**

> **⚡ QUER INSTALAR RÁPIDO?** Veja o [SETUP_RAPIDO.md](./SETUP_RAPIDO.md) para instalação em 5 minutos!

### 📋 **Pré-requisitos Obrigatórios**

#### 1. **Docker e Docker Compose**
```bash
# Verificar se tem Docker instalado
docker --version
# Deve retornar: Docker version 20.10.x ou superior

# Verificar se tem Docker Compose
docker compose version
# Deve retornar: Docker Compose version v2.x.x ou superior

# Se não tiver, instale em:
# Ubuntu/Debian: https://docs.docker.com/engine/install/ubuntu/
# macOS: https://docs.docker.com/desktop/mac/install/
# Windows: https://docs.docker.com/desktop/windows/install/
```

#### 2. **Make (Opcional mas Recomendado)**
```bash
# Verificar se tem Make
make --version
# Se não tiver, instale:
# Ubuntu/Debian: sudo apt install make
# macOS: xcode-select --install
# Windows: Use WSL ou instale via Chocolatey
```

#### 3. **Git (Para clonar o repositório)**
```bash
git --version
# Se não tiver, instale em: https://git-scm.com/downloads
```

### 🏗️ **Instalação Passo a Passo**

#### **Passo 1: Clonar o Repositório**
```bash
# Clonar o repositório
git clone <repository-url>
cd agro_sistemas

# Verificar estrutura do projeto
ls -la
# Deve mostrar: backend/ frontend/ README.md
```

#### **Passo 2: Configurar Backend**
```bash
# Entrar na pasta do backend
cd backend

# Copiar arquivo de configuração
cp laravel/.env.example laravel/.env

# Gerar chave da aplicação
docker compose exec php php artisan key:generate

# Verificar se o arquivo foi criado
ls laravel/.env
```

> **⚠️ ATENÇÃO**: O arquivo `.env.example` está configurado para Docker, mas você precisa gerar a `APP_KEY` após copiá-lo.

#### **Passo 3: Subir a Aplicação**

**🎯 Opção A: Com Make (Recomendado - Mais Fácil)**
```bash
# Construir e iniciar todos os serviços
make up_build && make up && make setup

# Aguardar alguns segundos para inicialização completa
sleep 10

# Verificar se tudo subiu corretamente
make status
```

**🔧 Opção B: Sem Make (Docker Direto)**
```bash
# Construir os containers
docker compose build

# Iniciar todos os serviços
docker compose up -d

# Aguardar inicialização
sleep 15

# Instalar dependências do PHP
docker compose exec laravel composer install

# Gerar chave da aplicação
docker compose exec laravel php artisan key:generate

# Executar migrações e popular banco
docker compose exec laravel php artisan migrate:fresh --seed

# Verificar status dos containers
docker compose ps
```

#### **Passo 4: Configurar Frontend**
```bash
# Voltar para a raiz do projeto
cd ../frontend

# Instalar dependências do Node.js
npm install

# Instalar Vite globalmente (se necessário)
npx vite --version

# Iniciar servidor de desenvolvimento
npm run dev

# O frontend estará disponível em: http://localhost:3000
```

### 🎨 **Configuração do Frontend**

#### **Tecnologias Utilizadas:**
- **Vue.js 3** - Framework JavaScript moderno
- **TypeScript** - Tipagem estática
- **Vite** - Build tool rápido
- **Tailwind CSS** - Framework CSS utilitário
- **PrimeVue** - Componentes UI
- **Pinia** - Gerenciamento de estado
- **Axios** - Cliente HTTP

#### **Estrutura do Frontend:**
```
frontend/
├── src/
│   ├── components/     # Componentes reutilizáveis
│   ├── views/         # Páginas principais
│   ├── stores/        # Gerenciamento de estado
│   ├── services/      # Serviços da API
│   ├── router/        # Configuração de rotas
│   └── assets/        # Recursos estáticos
├── package.json       # Dependências
└── vite.config.js     # Configuração do Vite
```

#### **Comandos do Frontend:**
```bash
# Instalar dependências
npm install

# Desenvolvimento (hot reload)
npm run dev

# Build para produção
npm run build

# Preview do build
npm run preview

# Linting
npm run lint
```

#### **Passo 5: Verificação Passo a Passo (OBRIGATÓRIO)**

**🔍 5.1 - Testar API Backend:**
```bash
echo "=== TESTANDO API BACKEND ==="
curl -H "Accept: application/json" http://localhost:8080/api/v1/dashboard

# ✅ SUCESSO: Deve retornar JSON com {"success":true,"data":{...}}
# ❌ ERRO: Se retornar HTML, verificar logs: docker compose logs php
```

**🔍 5.2 - Testar Frontend:**
```bash
echo "=== TESTANDO FRONTEND ==="
curl -s http://localhost:3000 | head -3

# ✅ SUCESSO: Deve retornar <!DOCTYPE html>
# ❌ ERRO: Se não retornar, verificar: ps aux | grep "npm run dev"
```

**🔍 5.3 - Testar Redis:**
```bash
echo "=== TESTANDO REDIS ==="
docker compose exec redis redis-cli --no-auth-warning -a redispassword ping

# ✅ SUCESSO: Deve retornar PONG
# ❌ ERRO: Se falhar, verificar: docker compose logs redis
```

**🔍 5.4 - Testar Mailpit:**
```bash
echo "=== TESTANDO MAILPIT ==="
curl -s http://localhost:32770 | head -1

# ✅ SUCESSO: Deve retornar <!DOCTYPE html>
# ❌ ERRO: Se falhar, verificar: docker compose logs mailer
```

**🔍 5.5 - Verificar Todos os Containers:**
```bash
echo "=== STATUS DOS CONTAINERS ==="
# Com Make:
make status

# Sem Make:
docker compose ps

# ✅ SUCESSO: Todos os containers devem estar "Up"
# ❌ ERRO: Se algum estiver "Exited", verificar logs
```

**🔍 5.6 - Abrir no Navegador:**
```bash
echo "=== ABRINDO NO NAVEGADOR ==="
# Linux:
xdg-open http://localhost:3000

# macOS:
# open http://localhost:3000

# Windows:
# start http://localhost:3000

# ✅ SUCESSO: Deve abrir a interface do sistema
# ❌ ERRO: Se não abrir, verificar se frontend está rodando
```

**Resposta esperada:**
```
NAME                    IMAGE                    STATUS
agro-sistemas-laravel   agro-sistemas-php:latest   Up
agro-sistemas-postgres  postgres:15-alpine        Up
agro-sistemas-redis     redis:7-alpine            Up
agro-sistemas-nginx     nginx:alpine              Up
agro-sistemas-mailer    axllent/mailpit:latest    Up
```

### 🎯 **URLs de Acesso**

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **Frontend** | http://localhost:3000 | Interface principal do sistema |
| **API Backend** | http://localhost:8080 | API REST completa |
| **Documentação** | http://localhost:8080/docs/api | Documentação interativa |
| **Mailpit** | http://localhost:32770 | Debug de emails (interface web) |
| **Dashboard** | http://localhost:8080/api/v1/dashboard | Estatísticas do sistema |
| **Redis** | localhost:6379 | Cache e sessões (via API) |

## 🧪 **COMO TESTAR O SISTEMA COMPLETO**

### 🎯 **Método 1: Interface Web (Mais Fácil)**

#### **Passo 1: Acessar o Frontend**
```bash
# Abrir o navegador em:
http://localhost:3003
```

#### **Passo 2: Fazer Login**
- **Email:** `admin@agrosistemas.com`
- **Senha:** `123456789`
- Ou **registrar novo usuário** clicando em "Registrar"

#### **Passo 3: Explorar as Funcionalidades**
1. **Dashboard** - Veja estatísticas em tempo real
2. **Atividades Recentes** - Monitore ações do sistema
3. **Produtores Rurais** - CRUD completo
4. **Propriedades** - Gestão de fazendas
5. **Unidades de Produção** - Cultivos agrícolas
6. **Rebanhos** - Gestão pecuária
7. **Relatórios** - 8 tipos de relatórios com exportação

### 🔧 **Método 2: API via Documentação Interativa**

#### **Passo 1: Acessar Documentação**
```bash
# Abrir no navegador:
http://localhost:8080/docs/api
```

#### **Passo 2: Autenticação**
1. **Registrar usuário:**
   - Endpoint: `POST /auth/register`
   - Body: `{"name":"Teste","email":"teste@agro.com","password":"123456","password_confirmation":"123456"}`
   - Clique "Try it out" → "Execute"

2. **Fazer login:**
   - Endpoint: `POST /auth/login`
   - Body: `{"email":"teste@agro.com","password":"123456"}`
   - **Copie o `access_token` da resposta**

3. **Autorizar:**
   - Clique no botão **"Authorize"** (cadeado) no topo
   - Digite: `Bearer SEU_TOKEN_AQUI`
   - Clique "Authorize"

#### **Passo 3: Testar Endpoints**
- Use **"Try it out"** em qualquer endpoint
- Veja respostas reais com dados do banco
- Teste todos os módulos: Produtores, Propriedades, Rebanhos, Unidades

### 📮 **Método 3: Postman Collection (Profissional)**

#### **Passo 1: Importar Collection**
1. Abra o **Postman**
2. Clique **"Import"**
3. Selecione o arquivo: `backend/AgroSistemas-API.postman_collection.json`

#### **Passo 2: Configurar Environment**
```json
{
  "base_url": "http://localhost:8080",
  "auth_token": "{{auth_token}}"
}
```

#### **Passo 3: Executar Workflow**
1. **Health & System Tests** - Verificar API
2. **Authentication** - Registrar/Login
3. **Dashboard** - Testar estatísticas
4. **Produtores Rurais** - CRUD completo
5. **Propriedades** - Gestão de fazendas
6. **Rebanhos** - Gestão pecuária
7. **Unidades de Produção** - Cultivos
8. **Relatórios** - Todos os tipos
9. **Exportação de Relatórios** - PDF, Excel, CSV

### 💻 **Método 4: cURL (Linha de Comando)**

#### **Passo 1: Obter Token**
```bash
# Registrar usuário
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste API",
    "email": "teste@agro.com", 
    "password": "12345678",
    "password_confirmation": "12345678"
  }'

# Fazer login e copiar token
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "teste@agro.com",
    "password": "12345678"
  }'

# Salvar token em variável
export TOKEN="cole_aqui_o_access_token"
```

#### **Passo 2: Testar Endpoints**
```bash
# Dashboard
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8080/api/v1/dashboard

# Produtores
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8080/api/v1/produtores-rurais

# Relatórios
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8080/api/v1/relatorios/produtores-rurais

# Exportar PDF
curl -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/pdf" \
     "http://localhost:8080/api/v1/relatorios/exportar/produtores-rurais?formato=pdf" \
     --output relatorio.pdf
```

### 🎯 **URLs de Acesso Rápido**

| Funcionalidade | URL | Descrição |
|----------------|-----|-----------|
| **Frontend** | http://localhost:3000 | Interface principal |
| **API Docs** | http://localhost:8080/docs/api | Documentação interativa |
| **Dashboard** | http://localhost:8080/api/v1/dashboard | Estatísticas |
| **Mailpit** | http://localhost:32770 | Debug de emails |

## 📊 Módulos Implementados
- ✅ **Autenticação** - Registro, login, recuperação de senha
- ✅ **Produtores Rurais** - CRUD completo com validações
- ✅ **Propriedades** - Gestão de fazendas com relacionamentos
- ✅ **Rebanhos** - Controle pecuário por espécie
- ✅ **Unidades de Produção** - 64+ cultivos disponíveis
- ✅ **Relatórios** - Analytics e dashboards
- ✅ **Cache** - Sistema Redis otimizado
```bash
# Construir containers (Docker Compose v2)
docker compose build

# Iniciar todos os serviços
docker compose up -d

# Instalar dependências do Composer
docker compose exec laravel composer install

# Gerar chave da aplicação
docker compose exec laravel php artisan key:generate

# Executar migrações e seeders
docker compose exec laravel php artisan migrate:fresh --seed
```

> **Nota**: Se você tem Docker Compose v1, use `docker-compose` (com hífen) ao invés de `docker compose`

### 3. Verificar Instalação

#### Com Make:
```bash
# Testar API
curl http://localhost:8080/api/v1/teste/health

# Verificar containers
docker-compose ps

# Ver logs
make logs_tail
```

#### Sem Make:
```bash
# Testar API
curl http://localhost:8080/api/v1/teste/health

# Verificar containers
docker-compose ps

# Ver logs
docker-compose logs -f
```

### 4. Acessos Disponíveis e Teste Inicial

#### URLs Principais:
- **API Base**: <http://localhost:8080/api/v1>
- **Documentação Interativa**: <http://localhost:8080/docs/api>
- **Mailpit (Debug de Emails)**: <http://localhost:32770>

#### Teste Básico da API:
```bash
# Verificar se a API está funcionando
curl http://localhost:8080/api/v1/teste/health

# Resposta esperada:
# {"status":"ok","message":"API funcionando corretamente","timestamp":"2025-10-15T..."}
```

#### Como Usar a Documentação Interativa:

1. **Acesse**: <http://localhost:8080/docs/api>
2. **Navegue pelos endpoints**: Use o menu lateral para encontrar os endpoints
3. **Autenticação**: 
   - Primeiro registre um usuário em `/auth/register`
   - Faça login em `/auth/login` para obter o token
   - Clique no botão **"Authorize"** (cadeado) no topo da página
   - Digite: `Bearer SEU_TOKEN_AQUI`
4. **Teste os endpoints**: Use o botão **"Try it out"** para testar
5. **Veja as respostas**: Exemplos reais serão mostrados

#### Verificar Emails (Mailpit):

1. **Acesse**: <http://localhost:32770>
2. **Cadastre um usuário** via API ou documentação
3. **Solicite recuperação de senha** via `/auth/forgot-password`
4. **Verifique o email** no Mailpit - o token aparecerá lá

## Guia de Uso da API

### Passo 1: Autenticação

#### 1.1 Registrar Usuário
```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@fazenda.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'
```

**Resposta esperada:**
```json
{
  "success": true,
  "data": {
    "access_token": "1|abc123def456...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "joao@fazenda.com"
    }
  }
}
```

#### 1.2 Fazer Login
```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "joao@fazenda.com",
    "password": "senha123"
  }'
```

#### 1.3 Usar Token nas Requisições
```bash
# Salvar o token em variável (facilita os testes)
export TOKEN="1|abc123def456..."

# Todas as próximas requisições devem incluir:
-H "Authorization: Bearer $TOKEN"
```

### Passo 2: Gerenciar Produtores Rurais

#### 2.1 Criar Produtor Rural
```bash
curl -X POST http://localhost:8080/api/v1/produtores-rurais \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "José Silva Santos",
    "cpf_cnpj": "123.456.789-00",
    "telefone": "85999999999",
    "email": "jose@fazenda.com",
    "endereco": "Zona Rural, Sobral/CE"
  }'
```

#### 2.2 Listar Produtores
```bash
curl -X GET "http://localhost:8080/api/v1/produtores-rurais?per_page=10" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

#### 2.3 Buscar Produtor
```bash
curl -X GET "http://localhost:8080/api/v1/produtores-rurais/buscar?q=Silva" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Passo 3: Gerenciar Propriedades

#### 3.1 Criar Propriedade
```bash
curl -X POST http://localhost:8080/api/v1/propriedades \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "Fazenda Santa Rita",
    "endereco": "Estrada CE-187, Km 45, Zona Rural",
    "area_total": 850.75,
    "municipio": "Sobral",
    "uf": "CE",
    "produtor_id": 1
  }'
```

#### 3.2 Listar Propriedades
```bash
curl -X GET "http://localhost:8080/api/v1/propriedades?per_page=10" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Passo 4: Gerenciar Rebanhos

#### 4.1 Criar Rebanho
```bash
curl -X POST http://localhost:8080/api/v1/rebanhos \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "especie": "bovinos",
    "raca": "Nelore",
    "quantidade": 120,
    "finalidade": "corte",
    "propriedade_id": 1,
    "observacoes": "Rebanho principal da fazenda"
  }'
```

#### 4.2 Estatísticas de Rebanhos
```bash
curl -X GET http://localhost:8080/api/v1/rebanhos/estatisticas-especies \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### Passo 5: Gerenciar Unidades de Produção

#### 5.1 Criar Unidade de Produção
```bash
curl -X POST http://localhost:8080/api/v1/unidades-producao \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome_cultura": "soja",
    "area_total_ha": 150.5,
    "data_plantio": "2025-03-15",
    "data_colheita_prevista": "2025-08-20",
    "propriedade_id": 1
  }'
```

#### 5.2 Cultivos Disponíveis
O sistema suporta 64+ tipos de cultivos organizados por categoria:
- **Frutas**: caju, manga, coco, mamao, banana, abacaxi, etc.
- **Grãos**: milho, feijao_caupi, soja, arroz, trigo, etc.
- **Hortaliças**: tomate, cebola, cenoura, alface, etc.

### Passo 6: Relatórios e Analytics

#### 6.1 Dashboard Geral
```bash
curl -X GET http://localhost:8080/api/v1/relatorios/dashboard \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

#### 6.2 Relatórios Específicos
```bash
# Propriedades por município
curl -X GET http://localhost:8080/api/v1/relatorios/propriedades-municipio \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Animais por espécie
curl -X GET http://localhost:8080/api/v1/relatorios/animais-especie \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## Endpoints Principais

### Autenticação
```
POST   /auth/register           # Registrar usuário
POST   /auth/login              # Login
GET    /auth/user               # Dados do usuário autenticado
POST   /auth/logout             # Logout
POST   /auth/forgot-password    # Solicitar reset de senha
POST   /auth/reset-password     # Resetar senha
```

### Produtores Rurais
```
GET    /produtores-rurais       # Listar (paginado)
POST   /produtores-rurais       # Criar
GET    /produtores-rurais/{id}  # Buscar específico
PUT    /produtores-rurais/{id}  # Atualizar
DELETE /produtores-rurais/{id}  # Deletar
GET    /produtores-rurais/buscar # Busca por nome/CPF
```

### Propriedades
```
GET    /propriedades            # Listar (paginado)
POST   /propriedades            # Criar
GET    /propriedades/{id}       # Buscar específica
PUT    /propriedades/{id}       # Atualizar
DELETE /propriedades/{id}       # Deletar
```

### Rebanhos
```
GET    /rebanhos                # Listar (paginado)
POST   /rebanhos                # Criar
GET    /rebanhos/{id}           # Buscar específico
PUT    /rebanhos/{id}           # Atualizar
DELETE /rebanhos/{id}           # Deletar
GET    /rebanhos/estatisticas-especies # Estatísticas
```

### Unidades de Produção
```
GET    /unidades-producao       # Listar (paginado)
POST   /unidades-producao       # Criar
GET    /unidades-producao/{id}  # Buscar específica
PUT    /unidades-producao/{id}  # Atualizar
DELETE /unidades-producao/{id}  # Deletar
```

### Cache Management
```
GET    /cache/stats             # Estatísticas do cache
DELETE /cache/flush             # Limpar todo cache
DELETE /cache/forget/{key}      # Limpar chave específica
```

## Desenvolvimento e Testes

### Comandos Make vs Docker Compose

#### Com Make (Recomendado)
```bash
make up           # Iniciar todos os serviços
make down         # Parar todos os serviços
make logs_tail    # Ver logs em tempo real
make bash         # Acessar container do Laravel
make test         # Executar testes PHPUnit
make migrate_fresh # Reset completo do banco
make cache_clear  # Limpar cache da aplicação
```

#### Sem Make (Docker direto)
```bash
docker compose up -d              # Iniciar todos os serviços
docker compose down               # Parar todos os serviços
docker compose logs -f            # Ver logs em tempo real
docker compose exec laravel bash # Acessar container do Laravel
docker compose exec laravel php artisan test           # Executar testes PHPUnit
docker compose exec laravel php artisan migrate:fresh --seed # Reset completo do banco
docker compose exec laravel php artisan cache:clear          # Limpar cache da aplicação
```

### Executar Testes
```bash
# Todos os testes
make test

# Testes unitários
php artisan test --testsuite=Unit

# Testes de integração
php artisan test --testsuite=Feature
```

### Estrutura de Testes Simplificada
```
tests/
├── TestCase.php                    # Classe base com autenticação
├── Unit/
│   ├── ModelsTest.php              # Testes básicos de modelos
│   └── EnumsTest.php               # Testes de enums
└── Feature/
    └── Api/
        └── BasicApiTest.php        # Testes básicos de rotas da API
```

### Características dos Testes

#### ✅ **Testes Básicos Implementados**
- **Autenticação**: Testes de rotas protegidas vs não protegidas
- **Rotas da API**: Validação de estrutura JSON e códigos de status
- **Modelos**: Criação e validação básica de usuários
- **Enums**: Validação de tipos de cultura e traduções

#### 🔍 **Cobertura Atual**
- **BasicApiTest**: Rotas principais (dashboard, produtores, propriedades, rebanhos, unidades, relatórios)
- **ModelsTest**: Criação de usuários e validação de campos
- **EnumsTest**: Validação de enums de cultivos

#### 📊 **Validações Incluídas**
- Estrutura de resposta JSON
- Códigos de status HTTP (200, 401, 404)
- Validação de autenticação
- Testes de rotas inválidas

## Collection Postman

A collection completa está disponível em: `AgroSistemas-API.postman_collection.json`

### Configurar Environment no Postman
```json
{
  "base_url": "http://localhost:8080",
  "auth_token": "{{auth_token}}"
}
```

### Workflow Recomendado no Postman
1. **Health & System Tests** - Verificar se API está funcionando
2. **Autenticação** - Registrar/Login para obter token
3. **Produtores Rurais** - Criar e gerenciar produtores
4. **Propriedades** - Criar propriedades vinculadas aos produtores
5. **Rebanhos** - Criar rebanhos nas propriedades
6. **Unidades de Produção** - Criar cultivos nas propriedades
7. **Relatórios** - Consultar analytics e estatísticas

## Monitoramento e Logs

### Health Checks
```bash
# Verificar se API está respondendo
curl http://localhost:8080/api/v1/teste/health

# Verificar conexão com banco
curl http://localhost:8080/api/v1/teste/database
```

### Logs da Aplicação
```bash
# Logs em tempo real de todos os containers
make logs_tail

# Logs específicos do Laravel
docker-compose exec laravel tail -f storage/logs/laravel.log

# Logs específicos do PostgreSQL
docker-compose logs postgres

# Logs específicos do Redis
docker-compose logs redis
```

## Troubleshooting

### Problemas Comuns

**🔴 Containers não sobem**
```bash
# Com Make:
make down
docker system prune -f
make up_build
make up

# Sem Make:
docker compose down
docker system prune -f
docker compose build
docker compose up -d
```

**🔴 Erro de conexão com banco**
```bash
# Com Make:
make down
docker volume prune -f
make up
make migrate_fresh

# Sem Make:
docker compose down
docker volume prune -f
docker compose up -d
docker compose exec laravel php artisan migrate:fresh --seed
```

**🔴 Cache não funciona**
```bash
# Com Make:
make cache_clear
docker compose restart redis

# Sem Make:
docker compose exec laravel php artisan cache:clear
docker compose restart redis
```

**🔴 Tokens expirados**
```bash
# Ambos os casos:
docker compose exec laravel php artisan tokens:clean
```

**🔴 Permissões no Laravel**
```bash
# Com Make:
make bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Sem Make:
docker compose exec laravel bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Metodologia de Desenvolvimento

Este projeto foi desenvolvido seguindo a metodologia **Scrum**, com sprints organizadas e entregas incrementais:

### Sprint Planning & Backlog

**Sprint 1 - Fundação do Sistema**
- ✅ Configuração do ambiente Docker (Laravel + PostgreSQL + Redis)
- ✅ Estrutura base da API com autenticação Sanctum
- ✅ Modelos e migrações principais
- ✅ Testes básicos automatizados

**Sprint 2 - Módulos Core**
- ✅ CRUD de Produtores Rurais com validações
- ✅ CRUD de Propriedades com relacionamentos
- ✅ Sistema de cache Redis implementado
- ✅ Enumerações específicas da região (cultivos, espécies)

**Sprint 3 - Gestão Pecuária e Agrícola**
- ✅ CRUD de Rebanhos com estatísticas
- ✅ CRUD de Unidades de Produção (64+ cultivos)
- ✅ Relatórios analíticos e dashboard
- ✅ Exportações em PDF, Excel e CSV

**Sprint 4 - Funcionalidades Avançadas**
- ✅ Sistema completo de recuperação de senha
- ✅ Cache inteligente por módulos
- ✅ Documentação automática com Scramble
- ✅ Collection Postman completa

### Definition of Done (DoD)
- ✅ Código revisado e testado
- ✅ Testes básicos automatizados funcionando
- ✅ Documentação atualizada
- ✅ Endpoints testados no Postman
- ✅ Performance validada com cache Redis

## Tecnologias e Arquitetura

### Backend Stack
- **Laravel 12** - Framework PHP moderno
- **PHP 8.4-FPM** - Runtime otimizado
- **PostgreSQL 15** - Banco de dados principal
- **Redis 7** - Cache e sessões
- **Laravel Sanctum** - Autenticação JWT/Bearer

### DevOps
- **Docker Compose** - Containerização completa
- **Nginx** - Servidor web de produção
- **Mailpit** - Debug de emails em desenvolvimento
- **Make** - Scripts de automação

### Principais Recursos Técnicos

#### Arquitetura
- **Design Pattern:** Repository + Service Layer
- **Validação:** Form Requests customizados
- **Cache:** Estratégias inteligentes por módulo
- **Testes:** Cobertura de features críticas
- **API:** RESTful com versionamento

#### Funcionalidades Avançadas
- **Recuperação de senha** via email
- **Busca avançada** em produtores
- **Relatórios estatísticos** em tempo real
- **Exportação** de dados em múltiplos formatos
- **Rate limiting** para proteção
- **Health checks** para monitoramento

#### Qualidade do Código
- **PSR-12** compliance
- **PHPDoc** annotations completas
- **Type hints** em todos os métodos
- **Exception handling** robusto
- **Logging** estruturado
- **Testes básicos** automatizados

> **Nota**: Para Docker Compose v1, substitua `docker compose` por `docker-compose` em todos os comandos

## Segurança

- **Autenticação**: Laravel Sanctum com tokens Bearer
- **Validação**: FormRequest em todos os endpoints
- **Rate Limiting**: Configurado para prevenir abuse
- **CORS**: Configurado adequadamente
- **Hash de Senhas**: bcrypt com salt

## Ambiente de Produção

### Configurações do .env (Pré-configurado)

O arquivo `.env.example` já está configurado com todas as variáveis necessárias:

```env
# Aplicação
APP_NAME="Agro Sistemas API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_TIMEZONE=America/Fortaleza
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Configuração do Banco de Dados PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=setup-laravel_postgres
DB_PORT=5432
DB_DATABASE=db_laravel
DB_USERNAME=developer
DB_PASSWORD=123456

# Configuração do Cache Redis
CACHE_STORE=redis
CACHE_PREFIX=agro_cache

# Configuração do Redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=redispassword
REDIS_PORT=6379

# Sessões
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Configuração do Email (Mailpit para desenvolvimento)
MAIL_MAILER=smtp
MAIL_HOST=setup-laravel_mailer
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="sistema@agropecuaria.local"
MAIL_FROM_NAME="${APP_NAME}"

# Queue
QUEUE_CONNECTION=redis

# Vite
VITE_APP_NAME="${APP_NAME}"
```

> **IMPORTANTE**: Essas configurações funcionam automaticamente com Docker. Não é necessário alterar nada.

## 📋 Guia Rápido para Recrutadores

### Passo 1: Setup (5 minutos)
```bash
# 1. Clonar projeto
git clone <repository-url>
cd agro_sistemas/backend

# 2. Copiar configurações (já pré-configurado)
cp laravel/.env.example laravel/.env

# 3. Subir ambiente (escolha uma opção):

# Opção A - Com Make:
make up_build && make up && make setup

# Opção B - Sem Make:
docker compose build
docker compose up -d
docker compose exec laravel composer install
docker compose exec laravel php artisan key:generate
docker compose exec laravel php artisan migrate:fresh --seed
```

### Passo 2: Verificar Funcionamento
```bash
# Teste básico da API
curl http://localhost:8080/api/v1/teste/health

# Deve retornar: {"status":"ok","message":"API funcionando corretamente",...}
```

### Passo 3: Explorar a API
1. **Documentação Interativa**: <http://localhost:8080/docs/api>
2. **Mailpit (emails)**: <http://localhost:32770>
3. **Collection Postman**: Importar `AgroSistemas-API.postman_collection.json`

### Passo 4: Testar Endpoints

#### Via Documentação (Recomendado):
1. Acesse <http://localhost:8080/docs/api>
2. Teste `/auth/register` para criar usuário
3. Use `/auth/login` para obter token
4. Clique em **"Authorize"** e insira: `Bearer SEU_TOKEN`
5. Teste os demais endpoints com **"Try it out"**

#### Via cURL (Manual):
```bash
# 1. Registrar usuário
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Teste","email":"teste@agro.com","password":"123456","password_confirmation":"123456"}'

# 2. Fazer login e copiar o token
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teste@agro.com","password":"123456"}'

# 3. Usar token nas próximas requisições
export TOKEN="cole_o_token_aqui"

# 4. Testar endpoints protegidos
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8080/api/v1/produtores-rurais
```

### Passo 5: Parar Ambiente
```bash
# Com Make:
make down

# Sem Make:
docker compose down
```

## 🔧 **TROUBLESHOOTING**

### Problemas Comuns e Soluções

#### **1. Erro "vite: not found" no Frontend**
```bash
# Solução: Instalar Vite
cd frontend
npx vite --version
npm run dev
```

#### **2. Erro de Conexão com Banco de Dados**
```bash
# Verificar se os containers estão rodando
docker compose ps

# Reiniciar containers
make down && make up

# Verificar logs do PostgreSQL
docker compose logs postgres
```

#### **3. Erro "APP_KEY not defined"**
```bash
# Gerar chave da aplicação
docker compose exec php php artisan key:generate
```

#### **4. Erro 500 na API**
```bash
# Verificar logs do Laravel
docker compose logs php

# Limpar cache
docker compose exec php php artisan cache:clear
docker compose exec php php artisan config:clear
```

#### **5. Frontend não carrega**
```bash
# Verificar se está na porta correta
# Frontend: http://localhost:3000
# Backend: http://localhost:8080

# Verificar se o processo está rodando
ps aux | grep "npm run dev"
```

#### **6. Migrações não executam**
```bash
# Executar migrações manualmente
docker compose exec php php artisan migrate

# Se houver erro, verificar permissões
docker compose exec php php artisan migrate:status
```

#### **7. Problemas com Redis**
```bash
# Verificar se Redis está rodando
docker compose ps redis

# Testar conexão direta com Redis
docker compose exec redis redis-cli --no-auth-warning -a redispassword ping
# Deve retornar: PONG

# Testar via Laravel (se configurado corretamente)
docker compose exec php php artisan tinker
# No tinker: app('redis')->ping()

# Verificar logs do Redis
docker compose logs redis
```

#### **8. Problemas com Mailpit (Email)**
```bash
# Verificar se Mailpit está rodando
docker compose ps mailer

# Acessar interface do Mailpit
open http://localhost:32770

# Verificar logs do Mailpit
docker compose logs mailer

# Testar envio de email
docker compose exec php php artisan tinker
# No tinker: Mail::raw('Teste', function($msg) { $msg->to('teste@teste.com')->subject('Teste'); });
```

### **Logs Úteis para Debug**
```bash
# Logs gerais
docker compose logs

# Logs específicos
docker compose logs php
docker compose logs postgres
docker compose logs nginx

# Logs em tempo real
docker compose logs -f php
```

---

**Sistema desenvolvido para otimizar a gestão agropecuária em todo o Brasil**
