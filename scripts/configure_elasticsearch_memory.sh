#!/bin/bash
# ============================================
# CONFIGURAÇÃO ELASTICSEARCH - REDUÇÃO DE MEMÓRIA
# Execute como root: sudo bash scripts/configure_elasticsearch_memory.sh
# ============================================

set -e

echo "============================================"
echo "CONFIGURAÇÃO ES/OPENSEARCH - AJUSTE DE MEMÓRIA"
echo "============================================"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo "ERRO: Este script deve ser executado como root"
    echo "Use: sudo bash $0"
    exit 1
fi

# Detectar serviço e diretórios (OpenSearch ou Elasticsearch)
SERVICE=""
CONF_DIR=""
JVM_DIR=""
YML_FILE=""

if systemctl list-unit-files | grep -q '^opensearch.service'; then
    SERVICE="opensearch"
    CONF_DIR="/etc/opensearch"
    JVM_DIR="/etc/opensearch/jvm.options.d"
    YML_FILE="/etc/opensearch/opensearch.yml"
elif systemctl list-unit-files | grep -q '^elasticsearch.service'; then
    SERVICE="elasticsearch"
    CONF_DIR="/etc/elasticsearch"
    JVM_DIR="/etc/elasticsearch/jvm.options.d"
    YML_FILE="/etc/elasticsearch/elasticsearch.yml"
else
    echo "ERRO: Nenhum serviço 'opensearch' ou 'elasticsearch' encontrado via systemd."
    echo "Instale e habilite um dos serviços antes de rodar este script."
    exit 1
fi

echo ">> Serviço detectado: $SERVICE"

# 1. Criar diretório se não existir
mkdir -p "$JVM_DIR"

# 2. Criar arquivo de configuração customizada de JVM
echo ">> Criando configuração JVM customizada..."
cat > "$JVM_DIR/custom.options" << 'EOF'
# Heap Memory Settings - Reduzido para 1GB
-Xms1g
-Xmx1g

# Garbage Collector Settings
-XX:+UseG1GC
-XX:G1ReservePercent=25
-XX:InitiatingHeapOccupancyPercent=30
EOF
echo "   OK: $JVM_DIR/custom.options criado"

# 3. Fazer backup da configuração atual
if [ -f "$YML_FILE" ]; then
    cp "$YML_FILE" "${YML_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
    echo "   OK: Backup da configuração criado"
fi

# 4. Verificar se as configurações já existem antes de adicionar
echo ">> Ajustando configuração principal ($YML_FILE)..."
if ! grep -q "bootstrap.memory_lock" "$YML_FILE" 2>/dev/null; then
    cat >> "$YML_FILE" << 'EOF'

# ============================================
# Memory Settings (adicionado automaticamente)
# ============================================
bootstrap.memory_lock: false
indices.memory.index_buffer_size: 10%

# Thread Pool Settings
thread_pool.write.queue_size: 200
thread_pool.search.queue_size: 500

# Circuit Breaker Settings
indices.breaker.total.limit: 70%
indices.breaker.request.limit: 40%
indices.breaker.fielddata.limit: 40%
EOF
    echo "   OK: Configurações adicionadas ao arquivo de configuração"
else
    echo "   SKIP: Configurações já existem no arquivo de configuração"
fi

# 5. Recarregar daemon e reiniciar Elasticsearch
echo ">> Reiniciando $SERVICE..."
systemctl daemon-reload
systemctl restart "$SERVICE"

# 6. Aguardar 15 segundos para inicialização
echo ">> Aguardando inicialização (15 segundos)..."
sleep 15

# 7. Verificar status
echo ">> Status do serviço ($SERVICE):"
systemctl status "$SERVICE" --no-pager || true

# 8. Testar conexão
echo ""
echo ">> Testando conexão:"
curl -s -X GET "http://localhost:9200/_cluster/health?pretty" || echo "   AVISO: Engine ainda está inicializando..."

# 9. Verificar uso de memória
echo ""
echo ">> Uso de memória do sistema:"
free -h

# 10. Matar processos de indexação travados
echo ""
echo ">> Limpando processos travados..."
ps aux | grep "indexer" | grep -v grep | awk '{print $2}' | xargs -r kill -9 2>/dev/null || true
ps aux | grep "cron:run" | grep -v grep | awk '{print $2}' | xargs -r kill -9 2>/dev/null || true
echo "   OK: Processos limpos"

echo ""
echo "============================================"
echo "CONFIGURAÇÃO CONCLUÍDA!"
echo "============================================"
echo ""
echo "PRÓXIMOS PASSOS (como usuário jessessh):"
echo ""
echo "cd /home/jessessh/htdocs/srv1113343.hstgr.cloud"
echo "php bin/magento indexer:reset catalogsearch_fulltext"
echo "php -d memory_limit=8G -d max_execution_time=7200 bin/magento indexer:reindex catalogsearch_fulltext"
echo "php bin/magento indexer:status"
echo ""
