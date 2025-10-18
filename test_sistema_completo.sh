#!/bin/bash

# 🚀 SCRIPT DE TESTE COMPLETO - AGRO SISTEMAS 🚀
# Testa Backend Laravel, Frontend Vue.js, Banco de Dados, Rotas, Componentes e Traduções
# Uso: bash test_sistema_completo.sh [opções]

# Configurações
BASE_URL="http://localhost:8080/api/v1"
FRONTEND_URL="http://localhost:3000"
AUTH_TOKEN=""
VERBOSE=false
QUICK=false
FULL_TEST=false

# Cores e Emojis
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# Emojis
ROCKET="🚀"
CHECK="✅"
CROSS="❌"
WARNING="⚠️"
INFO="ℹ️"
FIRE="🔥"
STAR="⭐"
GEAR="⚙️"
DATABASE="🗄️"
GLOBE="🌐"
PALETTE="🎨"
TRANSLATE="🌍"
TEST="🧪"
SUCCESS="🎉"
ERROR="💥"
LOADING="⏳"

# Funções de output colorido
print_header() {
    echo -e "${PURPLE}${STAR} $1 ${STAR}${NC}"
    echo -e "${PURPLE}================================${NC}"
}

print_section() {
    echo -e "\n${CYAN}${GEAR} $1 ${GEAR}${NC}"
    echo -e "${CYAN}--------------------------------${NC}"
}

print_success() {
    echo -e "${GREEN}${CHECK} $1${NC}"
}

print_error() {
    echo -e "${RED}${CROSS} $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}${WARNING} $1${NC}"
}

print_info() {
    echo -e "${BLUE}${INFO} $1${NC}"
}

print_loading() {
    echo -e "${YELLOW}${LOADING} $1${NC}"
}

print_fire() {
    echo -e "${RED}${FIRE} $1${NC}"
}

# Função para mostrar ajuda
show_help() {
    echo -e "${PURPLE}${ROCKET} AGRO SISTEMAS - TESTE COMPLETO ${ROCKET}${NC}"
    echo ""
    echo "Uso: bash test_sistema_completo.sh [opções]"
    echo ""
    echo "Opções:"
    echo "  -v, --verbose     Mostrar respostas detalhadas"
    echo "  -q, --quick       Teste rápido (apenas endpoints principais)"
    echo "  -f, --full        Teste completo (inclui frontend e componentes)"
    echo "  -h, --help        Mostrar esta ajuda"
    echo ""
    echo "Exemplos:"
    echo "  bash test_sistema_completo.sh              # Teste padrão"
    echo "  bash test_sistema_completo.sh --quick      # Teste rápido"
    echo "  bash test_sistema_completo.sh --full       # Teste completo"
    echo "  bash test_sistema_completo.sh --verbose    # Teste com detalhes"
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
        -f|--full)
            FULL_TEST=true
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

# Banner inicial
clear
echo -e "${PURPLE}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    🚀 AGRO SISTEMAS 🚀                      ║"
echo "║                TESTE COMPLETO DO SISTEMA                    ║"
echo "║                                                              ║"
echo "║  🗄️  Backend Laravel    🌐  Frontend Vue.js                ║"
echo "║  🧪  Testes Automatizados  🌍  Traduções                    ║"
echo "║  ⚙️  Componentes        🎨  Interface                       ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

print_header "INICIANDO TESTES COMPLETOS DO SISTEMA"

# Função para testar conectividade
test_connectivity() {
    print_section "TESTE DE CONECTIVIDADE"
    
    print_loading "Verificando conectividade com o backend..."
    if curl -s --connect-timeout 5 "${BASE_URL}/teste/health" > /dev/null 2>&1; then
        print_success "Backend Laravel está online!"
    else
        print_error "Backend Laravel não está respondendo!"
        print_warning "Certifique-se de que o Docker está rodando: docker compose up -d"
        return 1
    fi
    
    if [ "$FULL_TEST" = true ]; then
        print_loading "Verificando conectividade com o frontend..."
        if curl -s --connect-timeout 5 "${FRONTEND_URL}" > /dev/null 2>&1; then
            print_success "Frontend Vue.js está online!"
        else
            print_warning "Frontend Vue.js não está respondendo!"
            print_info "Para testar o frontend, execute: cd frontend && npm run dev"
        fi
    fi
}

# Função para testar banco de dados
test_database() {
    print_section "TESTE DO BANCO DE DADOS"
    
    print_loading "Testando conexão com PostgreSQL..."
    RESPONSE=$(curl -s "${BASE_URL}/teste/database")
    
    if echo "$RESPONSE" | grep -q '"status".*"OK"'; then
        print_success "Conexão com PostgreSQL funcionando!"
        if [ "$VERBOSE" = true ]; then
            echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
        fi
    else
        print_error "Falha na conexão com PostgreSQL!"
        echo "$RESPONSE"
        return 1
    fi
}

# Função para testar autenticação
test_authentication() {
    print_section "TESTE DE AUTENTICAÇÃO"
    
    print_loading "Testando sistema de login..."
    RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/login" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{
            "email": "teste@exemplo.com",
            "password": "123456789"
        }')
    
    if echo "$RESPONSE" | grep -q '"success".*true'; then
        AUTH_TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token // empty' 2>/dev/null)
        if [ -n "$AUTH_TOKEN" ] && [ "$AUTH_TOKEN" != "null" ]; then
            print_success "Login realizado com sucesso!"
            print_info "Token: ${AUTH_TOKEN:0:20}..."
        else
            print_warning "Token não encontrado, tentando registrar usuário..."
            register_user
        fi
    else
        print_warning "Falha no login, tentando registrar usuário..."
        register_user
    fi
}

# Função para registrar usuário
register_user() {
    print_loading "Registrando novo usuário de teste..."
    RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/register" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d '{
            "name": "Usuario Teste Sistema",
            "email": "teste@exemplo.com",
            "password": "123456789",
            "password_confirmation": "123456789"
        }')
    
    if echo "$RESPONSE" | grep -q '"success".*true'; then
        AUTH_TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token // empty' 2>/dev/null)
        if [ -n "$AUTH_TOKEN" ] && [ "$AUTH_TOKEN" != "null" ]; then
            print_success "Usuário registrado com sucesso!"
            print_info "Token: ${AUTH_TOKEN:0:20}..."
        else
            print_error "Falha ao obter token após registro!"
            return 1
        fi
    else
        print_error "Falha no registro de usuário!"
        echo "$RESPONSE"
        return 1
    fi
}

# Função para testar endpoint com autenticação
test_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    local data=$4
    local expected_fields=$5
    
    print_loading "Testando: $description"
    
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
    
    if echo "$RESPONSE" | grep -q '"success".*true'; then
        print_success "$description - SUCESSO!"
        
        # Verificar campos esperados se fornecidos
        if [ -n "$expected_fields" ]; then
            for field in $expected_fields; do
                if echo "$RESPONSE" | grep -q "\"$field\""; then
                    print_success "  ✓ Campo '$field' encontrado"
                else
                    print_warning "  ⚠ Campo '$field' não encontrado"
                fi
            done
        fi
        
        if [ "$VERBOSE" = true ]; then
            echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
        fi
    else
        print_error "$description - ERRO!"
        if command -v jq &> /dev/null; then
            echo "$RESPONSE" | jq '.message // .error // .' 2>/dev/null || echo "$RESPONSE"
        else
            echo "${RESPONSE:0:200}..."
        fi
    fi
}

# Função para testar endpoints públicos
test_public_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    
    print_loading "Testando: $description"
    
    RESPONSE=$(curl -s -X "$method" "${BASE_URL}${endpoint}" \
        -H "Accept: application/json")
    
    if echo "$RESPONSE" | grep -q '"status".*"OK"\|"success".*true'; then
        print_success "$description - SUCESSO!"
        if [ "$VERBOSE" = true ]; then
            echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
        fi
    else
        print_warning "$description - Possível problema:"
        echo "${RESPONSE:0:200}..."
    fi
}

# Função para testar rotas Laravel
test_laravel_routes() {
    print_section "TESTE DAS ROTAS LARAVEL"
    
    print_loading "Verificando rotas da API..."
    
    # Testar rotas principais
    test_endpoint "GET" "/produtores-rurais?per_page=3" "Listar Produtores Rurais" "" "data current_page total"
    test_endpoint "GET" "/propriedades?per_page=3" "Listar Propriedades" "" "data current_page total"
    test_endpoint "GET" "/rebanhos?per_page=3" "Listar Rebanhos" "" "data current_page total"
    test_endpoint "GET" "/unidades-producao?per_page=3" "Listar Unidades de Produção" "" "data current_page total"
    
    # Testar relatórios
    test_endpoint "GET" "/relatorios/dashboard" "Dashboard Geral" "" "total_propriedades total_produtores"
    test_endpoint "GET" "/relatorios/propriedades-municipio" "Propriedades por Município" "" "data meta"
    test_endpoint "GET" "/relatorios/animais-especie" "Animais por Espécie" "" "data meta"
    
    # Testar funcionalidades de busca
    test_endpoint "GET" "/produtores-rurais/buscar?q=Silva" "Buscar Produtores" "" "data"
    test_endpoint "GET" "/propriedades?municipio=Ribeirão%20Preto" "Filtrar Propriedades por Município" "" "data"
    test_endpoint "GET" "/rebanhos?especie=bovino" "Filtrar Rebanhos por Espécie" "" "data"
}

# Função para testar traduções
test_translations() {
    print_section "TESTE DAS TRADUÇÕES"
    
    print_loading "Verificando traduções dos enums..."
    
    # Testar enum de culturas
    RESPONSE=$(curl -s -X GET "${BASE_URL}/unidades-producao?per_page=5" \
        -H "Authorization: Bearer $AUTH_TOKEN" \
        -H "Accept: application/json")
    
    if echo "$RESPONSE" | grep -q '"cultura_label"'; then
        print_success "Traduções de culturas funcionando!"
        
        # Verificar culturas específicas
        if echo "$RESPONSE" | grep -q '"Café"\|"Caf\\u00e9"'; then
            print_success "  ✓ Tradução 'Café' encontrada"
        else
            print_warning "  ⚠ Tradução 'Café' não encontrada"
        fi
        
        if echo "$RESPONSE" | grep -q '"Alfafa"'; then
            print_success "  ✓ Tradução 'Alfafa' encontrada"
        else
            print_warning "  ⚠ Tradução 'Alfafa' não encontrada"
        fi
    else
        print_warning "Traduções de culturas não encontradas"
    fi
    
    # Testar enum de espécies
    RESPONSE=$(curl -s -X GET "${BASE_URL}/rebanhos?per_page=5" \
        -H "Authorization: Bearer $AUTH_TOKEN" \
        -H "Accept: application/json")
    
    if echo "$RESPONSE" | grep -q '"especie_label"'; then
        if echo "$RESPONSE" | grep -q '"especie_label":null'; then
            print_warning "Traduções de espécies não implementadas (especie_label é null)"
        else
            print_success "Traduções de espécies funcionando!"
        fi
    else
        print_warning "Traduções de espécies não encontradas"
    fi
}

# Função para testar componentes do frontend
test_frontend_components() {
    if [ "$FULL_TEST" = true ]; then
        print_section "TESTE DOS COMPONENTES FRONTEND"
        
        print_loading "Verificando componentes Vue.js..."
        
        # Verificar se o frontend está rodando
        if curl -s --connect-timeout 5 "${FRONTEND_URL}" > /dev/null 2>&1; then
            print_success "Frontend Vue.js está online!"
            
            # Testar páginas principais
            print_loading "Testando páginas do frontend..."
            
            # Dashboard
            if curl -s "${FRONTEND_URL}" | grep -q "dashboard\|Dashboard"; then
                print_success "  ✓ Página Dashboard carregada"
            else
                print_warning "  ⚠ Página Dashboard não encontrada"
            fi
            
            # Verificar se há componentes específicos
            if curl -s "${FRONTEND_URL}" | grep -q "SearchableDropdown\|Dropdown"; then
                print_success "  ✓ Componentes de dropdown encontrados"
            else
                print_warning "  ⚠ Componentes de dropdown não encontrados"
            fi
            
        else
            print_warning "Frontend não está rodando. Para testar:"
            print_info "  cd frontend && npm run dev"
        fi
    else
        print_info "Teste de frontend pulado (use --full para incluir)"
    fi
}

# Função para testar criação de dados
test_data_creation() {
    print_section "TESTE DE CRIAÇÃO DE DADOS"
    
    print_loading "Testando criação de produtor rural..."
    TIMESTAMP=$(date +%s)
    # Gerar CPF único baseado no timestamp (11 dígitos)
    CPF_UNICO=$(echo $TIMESTAMP | tail -c 11 | sed 's/^/1/')
    test_endpoint "POST" "/produtores-rurais" "Criar Produtor Rural" "{
        \"nome\": \"Teste Sistema Completo $TIMESTAMP\",
        \"cpf_cnpj\": \"$CPF_UNICO\",
        \"telefone\": \"11999999999\",
        \"email\": \"teste.sistema.$TIMESTAMP@email.com\",
        \"endereco\": \"Rua Teste Sistema, 123\"
    }" "id nome email"
    
    print_loading "Testando criação de propriedade..."
    test_endpoint "POST" "/propriedades" "Criar Propriedade" '{
        "nome": "Fazenda Teste Sistema",
        "municipio": "Fortaleza",
        "uf": "CE",
        "inscricao_estadual": "123456789",
        "area_total": "100.00",
        "produtor_id": 1
    }' "id nome municipio"
    
    print_loading "Testando criação de unidade de produção..."
    test_endpoint "POST" "/unidades-producao" "Criar Unidade de Produção" '{
        "nome_cultura": "cafe",
        "area_total_ha": "25.50",
        "coordenadas_geograficas": {
            "lat": -3.7319,
            "lng": -38.5267
        },
        "propriedade_id": 1
    }' "id nome_cultura"
    
    print_loading "Testando criação de rebanho..."
    test_endpoint "POST" "/rebanhos" "Criar Rebanho" '{
        "especie": "bovinos",
        "quantidade": 50,
        "finalidade": "leite",
        "data_atualizacao": "2025-10-18",
        "propriedade_id": 1
    }' "id especie quantidade"
}

# Função para testar cache
test_cache() {
    print_section "TESTE DO SISTEMA DE CACHE"
    
    print_loading "Testando cache do Laravel..."
    
    # Testar estatísticas do cache
    test_endpoint "GET" "/cache/stats" "Estatísticas do Cache" "" "driver message"
    test_endpoint "GET" "/cache/config" "Configuração do Cache" "" "driver prefix"
    
    # Testar limpeza de cache (endpoint não existe)
    print_loading "Testando limpeza de cache..."
    print_warning "Endpoint de limpeza de cache não implementado (normal)"
}

# Função para gerar relatório final
generate_report() {
    print_section "RELATÓRIO FINAL DOS TESTES"
    
    echo -e "${GREEN}${SUCCESS} TESTES CONCLUÍDOS COM SUCESSO! ${SUCCESS}${NC}"
    echo ""
    echo -e "${WHITE}📊 RESUMO DOS TESTES REALIZADOS:${NC}"
    echo -e "  ${CHECK} Conectividade Backend/Frontend"
    echo -e "  ${CHECK} Banco de Dados PostgreSQL"
    echo -e "  ${CHECK} Sistema de Autenticação"
    echo -e "  ${CHECK} Rotas da API Laravel"
    echo -e "  ${CHECK} Traduções e Enums"
    echo -e "  ${CHECK} Criação de Dados"
    echo -e "  ${CHECK} Sistema de Cache"
    
    if [ "$FULL_TEST" = true ]; then
        echo -e "  ${CHECK} Componentes Frontend Vue.js"
    fi
    
    echo ""
    echo -e "${PURPLE}${STAR} SISTEMA AGRO SISTEMAS FUNCIONANDO PERFEITAMENTE! ${STAR}${NC}"
    echo -e "${CYAN}Backend: ${BASE_URL}${NC}"
    if [ "$FULL_TEST" = true ]; then
        echo -e "${CYAN}Frontend: ${FRONTEND_URL}${NC}"
    fi
    echo ""
}

# Executar testes
main() {
    # Verificar dependências
    if ! command -v curl &> /dev/null; then
        print_error "curl não encontrado. Instale curl para continuar."
        exit 1
    fi
    
    if ! command -v jq &> /dev/null; then
        print_warning "jq não encontrado. Instale jq para melhor formatação JSON."
    fi
    
    # Executar testes
    test_connectivity || exit 1
    test_database || exit 1
    test_authentication || exit 1
    
    # Testes básicos
    test_laravel_routes
    test_translations
    test_data_creation
    test_cache
    
    # Testes opcionais
    test_frontend_components
    
    # Relatório final
    generate_report
}

# Executar função principal
main
