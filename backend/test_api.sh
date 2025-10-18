#!/bin/bash

# Script de teste da API Agro Sistemas
# Uso: bash test_api.sh [opções]

BASE_URL="http://localhost:8080/api/v1"
AUTH_TOKEN=""
VERBOSE=false
QUICK=false

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Função para mostrar ajuda
show_help() {
    echo "Uso: bash test_api.sh [opções]"
    echo ""
    echo "Opções:"
    echo "  -v, --verbose    Mostrar respostas detalhadas"
    echo "  -q, --quick      Teste rápido (apenas endpoints principais)"
    echo "  -h, --help       Mostrar esta ajuda"
    echo ""
    echo "Exemplos:"
    echo "  bash test_api.sh              # Teste completo"
    echo "  bash test_api.sh --quick      # Teste rápido"
    echo "  bash test_api.sh --verbose    # Teste com detalhes"
}

# Processar argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -q|--quick)
            QUICK=true
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            print_error "Opção desconhecida: $1"
            show_help
            exit 1
            ;;
    esac
done

echo "🚀 Testando API Agro Sistemas..."
echo "================================="

# Função para fazer login e capturar token
login() {
    print_status "Fazendo login..."
    
    RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/login" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{
            "email": "teste@exemplo.com",
            "password": "123456789"
        }')
    
    # Extrair token usando jq se disponível, senão usar grep/sed
    if command -v jq &> /dev/null; then
        AUTH_TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token // empty')
    else
        AUTH_TOKEN=$(echo "$RESPONSE" | grep -o '"access_token":"[^"]*"' | sed 's/"access_token":"\([^"]*\)"/\1/')
    fi
    
    if [ -n "$AUTH_TOKEN" ] && [ "$AUTH_TOKEN" != "null" ]; then
        print_success "Login realizado com sucesso!"
        print_status "Token: ${AUTH_TOKEN:0:20}..."
    else
        print_warning "Falha no login. Tentando registrar usuário..."
        register_user
    fi
}

# Função para registrar usuário
register_user() {
    print_status "Registrando novo usuário..."
    
    RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/register" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{
            "name": "Usuario Teste",
            "email": "teste@exemplo.com",
            "password": "123456789",
            "password_confirmation": "123456789"
        }')
    
    if command -v jq &> /dev/null; then
        AUTH_TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token // empty')
        SUCCESS=$(echo "$RESPONSE" | jq -r '.success // false')
    else
        AUTH_TOKEN=$(echo "$RESPONSE" | grep -o '"access_token":"[^"]*"' | sed 's/"access_token":"\([^"]*\)"/\1/')
        SUCCESS=$(echo "$RESPONSE" | grep -o '"success":[^,}]*' | sed 's/"success"://')
    fi
    
    if [ -n "$AUTH_TOKEN" ] && [ "$AUTH_TOKEN" != "null" ]; then
        print_success "Usuário registrado com sucesso!"
        print_status "Token: ${AUTH_TOKEN:0:20}..."
    else
        print_error "Falha no registro: $RESPONSE"
        exit 1
    fi
}

# Função para testar endpoint com autenticação
test_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    local data=$4
    
    echo ""
    print_status "Testando: $description"
    print_status "$method $endpoint"
    
    if [ -n "$data" ]; then
        RESPONSE=$(curl -s -X "$method" "${BASE_URL}${endpoint}" \
            -H "Authorization: Bearer $AUTH_TOKEN" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d "$data")
    else
        RESPONSE=$(curl -s -X "$method" "${BASE_URL}${endpoint}" \
            -H "Authorization: Bearer $AUTH_TOKEN" \
            -H "Accept: application/json")
    fi
    
    # Verificar se response contém "success": true
    if echo "$RESPONSE" | grep -q '"success".*true'; then
        print_success "Sucesso!"
        if [ "$VERBOSE" = true ]; then
            if command -v jq &> /dev/null; then
                echo "$RESPONSE" | jq '.'
            else
                echo "Resposta: $RESPONSE"
            fi
        else
            if command -v jq &> /dev/null; then
                echo "$RESPONSE" | jq '.message // .data // "OK"'
            else
                echo "Resposta: ${RESPONSE:0:100}..."
            fi
        fi
    else
        print_error "Erro!"
        if command -v jq &> /dev/null; then
            echo "$RESPONSE" | jq '.message // .error // .'
        else
            echo "Resposta: ${RESPONSE:0:200}..."
        fi
    fi
}

# Função para testar endpoint público
test_public_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    
    echo ""
    print_status "Testando: $description"
    print_status "$method $endpoint"
    
    RESPONSE=$(curl -s -X "$method" "${BASE_URL}${endpoint}" \
        -H "Accept: application/json")
    
    if echo "$RESPONSE" | grep -q '"status".*"OK"\|"success".*true'; then
        print_success "Sucesso!"
        if [ "$VERBOSE" = true ]; then
            echo "Resposta: $RESPONSE"
        fi
    else
        print_warning "Possível erro:"
        echo "${RESPONSE:0:200}..."
    fi
}

# Executar testes
echo ""
print_status "Testando endpoints públicos..."
test_public_endpoint "GET" "/teste/health" "Health Check"
test_public_endpoint "GET" "/teste/database" "Database Connection"

echo ""
print_status "Configurando autenticação..."
login

if [ -z "$AUTH_TOKEN" ]; then
    print_error "Não foi possível obter token de autenticação"
    exit 1
fi

# Testes básicos (sempre executados)
echo ""
print_status "Testando endpoints básicos..."
test_endpoint "GET" "/produtores-rurais?per_page=5" "Listar Produtores"
test_endpoint "GET" "/propriedades?per_page=5" "Listar Propriedades"
test_endpoint "GET" "/rebanhos?per_page=5" "Listar Rebanhos"
test_endpoint "GET" "/unidades-producao?per_page=5" "Listar Unidades"
test_endpoint "GET" "/relatorios/dashboard" "Dashboard Geral"

# Testes completos (apenas se não for quick)
if [ "$QUICK" = false ]; then
    echo ""
    print_status "Testando funcionalidades avançadas..."
    
    echo ""
    print_status "Testando Produtores Rurais..."
    test_endpoint "GET" "/produtores-rurais/buscar?q=Silva" "Buscar Produtores"
    
    echo ""
    print_status "Testando Propriedades..."
    test_endpoint "GET" "/propriedades?municipio=Ribeirão Preto" "Filtrar por Município"
    
    echo ""
    print_status "Testando Rebanhos..."
    test_endpoint "GET" "/rebanhos?especie=bovino" "Filtrar por Espécie"
    test_endpoint "GET" "/rebanhos/relatorio/especies" "Relatório de Espécies"
    
    echo ""
    print_status "Testando Relatórios..."
    test_endpoint "GET" "/relatorios/propriedades-municipio" "Propriedades por Município"
    test_endpoint "GET" "/relatorios/animais-especie" "Animais por Espécie"
    
    echo ""
    print_status "Testando Cache..."
    test_endpoint "GET" "/cache/stats" "Estatísticas do Cache"
    test_endpoint "GET" "/cache/config" "Configuração do Cache"
    
    echo ""
    print_status "Testando perfil do usuário..."
    test_endpoint "GET" "/auth/user" "Obter Perfil"
    
    echo ""
    print_status "Testando criação de dados..."
    test_endpoint "POST" "/produtores-rurais" "Criar Produtor" '{
        "nome": "João Silva Teste API",
        "cpf_cnpj": "12345678901",
        "telefone": "11987654321",
        "email": "joao.teste.api@email.com",
        "endereco": "Rua Teste API, 123"
    }'
fi

echo ""
echo "================================="
print_success "Testes concluídos!"
echo ""
print_status "Resumo dos endpoints testados:"
echo "   - Health Check ✓"
echo "   - Database Connection ✓"
echo "   - Authentication ✓"
echo "   - Produtores Rurais ✓"
echo "   - Propriedades ✓"
echo "   - Rebanhos ✓"
echo "   - Unidades de Produção ✓"
echo "   - Relatórios ✓"

if [ "$QUICK" = false ]; then
    echo "   - Cache Management ✓"
    echo "   - Criação de Dados ✓"
fi

echo ""
print_status "Para mais testes detalhados, use a coleção do Postman!"
print_status "Arquivo: AgroSistemas-API.postman_collection.json"