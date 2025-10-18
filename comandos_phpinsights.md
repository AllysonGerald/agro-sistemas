# 📋 Comandos PHP Insights - AgroSistemas

## 🚀 Instalação e Configuração

### **1. Navegação para o Diretório:**
```bash
cd /home/allyson-carvalho/Documentos/projects/agro_sistemas/backend/laravel
```

### **2. Verificação do Ambiente:**
```bash
# Verificar se está no diretório correto
ls -la composer.json

# Verificar versão do PHP Insights
./vendor/bin/phpinsights --version
```

## 🔍 Comandos de Análise

### **3. Análise Básica:**
```bash
# Análise completa do projeto
./vendor/bin/phpinsights

# Apenas resumo (sem detalhes)
./vendor/bin/phpinsights --summary

# Análise com configuração personalizada
./vendor/bin/phpinsights --config-path=phpinsights.php
```

### **4. Análise com Qualidade Mínima:**
```bash
# Falha se qualidade for menor que 80%
./vendor/bin/phpinsights --min-quality=80

# Falha se complexidade for menor que 70%
./vendor/bin/phpinsights --min-complexity=70

# Falha se arquitetura for menor que 75%
./vendor/bin/phpinsights --min-architecture=75

# Falha se estilo for menor que 80%
./vendor/bin/phpinsights --min-style=80

# Múltiplos critérios
./vendor/bin/phpinsights --min-quality=80 --min-complexity=70 --min-architecture=75 --min-style=80
```

### **5. Correções Automáticas:**
```bash
# Aplicar correções automáticas
./vendor/bin/phpinsights --fix

# Limpar cache antes da análise
./vendor/bin/phpinsights --flush-cache

# Aplicar correções com cache limpo
./vendor/bin/phpinsights --fix --flush-cache
```

## 📁 Análise de Arquivos Específicos

### **6. Arquivos Individuais:**
```bash
# Analisar um arquivo específico
./vendor/bin/phpinsights app/Models/User.php

# Analisar um controller
./vendor/bin/phpinsights app/Http/Controllers/Api/AuthController.php

# Analisar um service
./vendor/bin/phpinsights app/Services/DashboardService.php
```

### **7. Pastas Específicas:**
```bash
# Analisar pasta de models
./vendor/bin/phpinsights app/Models/

# Analisar pasta de services
./vendor/bin/phpinsights app/Services/

# Analisar pasta de controllers
./vendor/bin/phpinsights app/Http/Controllers/

# Analisar múltiplas pastas
./vendor/bin/phpinsights app/Models/ app/Services/
```

## 📊 Formatos de Saída

### **8. Saída em Diferentes Formatos:**
```bash
# Saída em JSON (para integração com CI/CD)
./vendor/bin/phpinsights --format=json

# Saída para GitHub Actions
./vendor/bin/phpinsights --format=github-action

# Saída em Checkstyle (para IDEs)
./vendor/bin/phpinsights --format=checkstyle

# Saída em CodeClimate
./vendor/bin/phpinsights --format=codeclimate
```

### **9. Salvar Resultados:**
```bash
# Salvar em arquivo JSON
./vendor/bin/phpinsights --format=json > phpinsights-report.json

# Salvar em arquivo de texto
./vendor/bin/phpinsights > phpinsights-report.txt

# Salvar com timestamp
./vendor/bin/phpinsights > "phpinsights-$(date +%Y%m%d-%H%M%S).txt"
```

## ⚙️ Comandos de Configuração

### **10. Configurações Avançadas:**
```bash
# Usar configuração personalizada
./vendor/bin/phpinsights --config-path=phpinsights.php

# Modo silencioso (apenas erros)
./vendor/bin/phpinsights --quiet

# Modo verbose (mais detalhes)
./vendor/bin/phpinsights --verbose

# Modo debug
./vendor/bin/phpinsights -vvv
```

### **11. Comandos de Verificação:**
```bash
# Verificar versão
./vendor/bin/phpinsights --version

# Ver ajuda completa
./vendor/bin/phpinsights --help

# Ver opções específicas
./vendor/bin/phpinsights --help | grep "min-"
```

## 🔄 Comandos para CI/CD

### **12. Integração Contínua:**
```bash
# Para falhar o build se qualidade for baixa
./vendor/bin/phpinsights --min-quality=80 --min-complexity=70 --min-architecture=75 --min-style=80

# Para GitHub Actions
./vendor/bin/phpinsights --min-quality=80 --format=github-action

# Para Jenkins/CI
./vendor/bin/phpinsights --min-quality=75 --format=json > quality-report.json
```

### **13. Scripts de Automação:**
```bash
# Script para análise diária
#!/bin/bash
cd /home/allyson-carvalho/Documentos/projects/agro_sistemas/backend/laravel
./vendor/bin/phpinsights --min-quality=80 --format=json > "reports/quality-$(date +%Y%m%d).json"

# Script para correção automática
#!/bin/bash
cd /home/allyson-carvalho/Documentos/projects/agro_sistemas/backend/laravel
./vendor/bin/phpinsights --fix
./vendor/bin/phpinsights --summary
```

## 📈 Sequência Recomendada

### **14. Workflow Completo:**
```bash
# 1. Navegar para o diretório
cd /home/allyson-carvalho/Documentos/projects/agro_sistemas/backend/laravel

# 2. Verificar se está no lugar certo
ls -la composer.json

# 3. Fazer análise completa
./vendor/bin/phpinsights

# 4. Aplicar correções automáticas
./vendor/bin/phpinsights --fix

# 5. Fazer análise novamente para ver melhorias
./vendor/bin/phpinsights

# 6. Verificar se atingiu qualidade mínima
./vendor/bin/phpinsights --min-quality=80
```

## 🎯 Comandos Úteis para Desenvolvimento

### **15. Análise Durante Desenvolvimento:**
```bash
# Analisar apenas arquivos modificados (Git)
git diff --name-only HEAD~1 | grep '\.php$' | xargs ./vendor/bin/phpinsights

# Analisar arquivos em staging
git diff --cached --name-only | grep '\.php$' | xargs ./vendor/bin/phpinsights

# Analisar arquivos específicos por padrão
./vendor/bin/phpinsights app/Http/Controllers/Api/
```

### **16. Monitoramento de Qualidade:**
```bash
# Criar relatório de qualidade
./vendor/bin/phpinsights --format=json | jq '.summary' > quality-summary.json

# Verificar tendência de qualidade
./vendor/bin/phpinsights --summary | grep -E "(Code|Complexity|Architecture|Style)"
```

## 📝 Notas Importantes

- **Sempre execute** os comandos do diretório do Laravel (`backend/laravel/`)
- **Use `--fix`** para correções automáticas antes de commit
- **Configure `--min-quality`** para falhar builds com qualidade baixa
- **Use `--format=json`** para integração com ferramentas de CI/CD
- **Execute `--flush-cache`** se houver problemas de cache

## 🔧 Troubleshooting

### **Problemas Comuns:**
```bash
# Se houver erro de permissão
chmod +x ./vendor/bin/phpinsights

# Se houver erro de memória
php -d memory_limit=512M ./vendor/bin/phpinsights

# Se houver erro de timeout
./vendor/bin/phpinsights --timeout=300
```

---

**📅 Última atualização:** $(date +%Y-%m-%d)  
**🔧 Versão PHP Insights:** 2.13.3  
**📁 Projeto:** AgroSistemas - Sistema de Gestão Agropecuária
