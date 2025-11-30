# 🏭 Melhores Práticas Magento 2 B2B - Fábrica de Peças de Motos

**Versão:** 1.0  
**Data:** Dezembro 2025  
**Projeto:** srv1113343.hstgr.cloud  
**Magento:** 2.4.8-p3  
**Contexto:** Grande Fábrica B2B de Peças de Motos

---

## 📋 Índice

1. [Visão Geral B2B para Fábrica](#1-visão-geral-b2b-para-fábrica)
2. [Configuração de Clientes B2B](#2-configuração-de-clientes-b2b)
3. [Gestão de Preços e Catálogos](#3-gestão-de-preços-e-catálogos)
4. [Sistema de Compatibilidade (Fitment)](#4-sistema-de-compatibilidade-fitment)
5. [Pedidos em Grande Volume](#5-pedidos-em-grande-volume)
6. [Aprovação de Pedidos](#6-aprovação-de-pedidos)
7. [Integração com ERP Industrial](#7-integração-com-erp-industrial)
8. [Gestão de Estoque Complexa](#8-gestão-de-estoque-complexa)
9. [Cotações e Negociação](#9-cotações-e-negociação)
10. [Números de Peças e Cross-Reference](#10-números-de-peças-e-cross-reference)
11. [Performance para Alto Volume](#11-performance-para-alto-volume)
12. [Checklist Completo B2B](#12-checklist-completo-b2b)

---

## 1. Visão Geral B2B para Fábrica

### 🎯 Características do Negócio

**Tipo de Cliente:**
- Revendedores de peças
- Oficinas mecânicas
- Distribuidores
- Lojas especializadas
- Atacadistas

**Volume de Negócios:**
- Pedidos grandes (centenas/milhares de unidades)
- Múltiplos SKUs por pedido
- Frequência alta de compras
- Relacionamento de longo prazo

**Necessidades Específicas:**
- Preços negociados por cliente
- Catálogos customizados
- Compatibilidade de peças (Fitment)
- Números OEM e cross-reference
- Aprovação de pedidos
- Limite de crédito
- Integração com ERP

### ✅ Módulos B2B Implementados

**Status no Projeto:**
- ✅ `GrupoAwamotos_B2B` - Módulo B2B customizado
- ✅ `GrupoAwamotos_Fitment` - Compatibilidade de peças
- ✅ `GrupoAwamotos_BrazilCustomer` - Validação CPF/CNPJ
- ✅ `GrupoAwamotos_CarrierSelect` - Transportadoras

---

## 2. Configuração de Clientes B2B

### 👥 Grupos de Clientes

**Grupos Implementados:**

| ID | Grupo | Desconto | Uso |
|----|-------|----------|-----|
| 4 | B2B Atacado | 15% | Grandes atacadistas |
| 5 | B2B VIP | 20% | Clientes estratégicos |
| 6 | B2B Revendedor | 10% | Revendedores |
| 7 | B2B Pendente | 0% | Aguardando aprovação |

**Configuração:**
```bash
# Ver grupos criados
php bin/magento customer:group:list

# Criar novo grupo (se necessário)
php bin/magento customer:group:create --name "B2B Distribuidor" --tax-class-id 3
```

### 📝 Atributos de Cliente B2B

**Campos Obrigatórios:**
- ✅ `b2b_cnpj` - CNPJ da empresa
- ✅ `b2b_razao_social` - Razão Social
- ✅ `b2b_inscricao_estadual` - Inscrição Estadual
- ✅ `b2b_approved` - Status de aprovação
- ✅ `b2b_company_phone` - Telefone comercial

**Campos Adicionais Recomendados:**
```php
// Adicionar via Data Patch
- b2b_limite_credito - Limite de crédito
- b2b_credito_utilizado - Crédito utilizado
- b2b_representante_vendas - Vendedor responsável
- b2b_condicao_pagamento - Prazo de pagamento
- b2b_categoria_cliente - Categoria (A, B, C)
- b2b_volume_mensal - Volume médio mensal
```

### 🔐 Processo de Aprovação

**Fluxo Implementado:**
1. Cliente preenche cadastro B2B (`/b2b/register/index`)
2. CNPJ é validado via API ReceitaWS
3. Conta criada no grupo "B2B Pendente"
4. Admin recebe notificação por email
5. Admin aprova/rejeita no painel
6. Cliente é movido para grupo apropriado
7. Cliente recebe email de aprovação

**Configuração:**
```
Admin > Stores > Configuration > Grupo Awamotos > B2B Settings > Aprovação de Clientes

- Exigir Aprovação: Sim
- Grupos com Auto-Aprovação: (opcional)
- Enviar E-mail de Aprovação: Sim
- Notificar Admin: Sim
```

### 💳 Limite de Crédito

**Tabela:** `grupoawamotos_b2b_credit_limit`

**Funcionalidades:**
- Limite total por cliente
- Crédito utilizado (pedidos pendentes)
- Bloqueio automático ao atingir limite
- Dashboard mostra crédito disponível

**Configuração:**
```php
// Via Admin ou API
$creditLimit->setCustomerId($customerId);
$creditLimit->setCreditLimit(50000.00); // R$ 50.000
$creditLimit->setUsedCredit(0);
$creditLimit->save();
```

### ✅ Checklist de Clientes B2B

- [x] Grupos de clientes criados
- [x] Atributos B2B adicionados
- [x] Processo de aprovação configurado
- [x] Validação de CNPJ funcionando
- [x] Limite de crédito implementado
- [ ] Campos adicionais configurados
- [ ] Representantes de vendas atribuídos
- [ ] Categorização de clientes (A/B/C)

---

## 3. Gestão de Preços e Catálogos

### 💰 Preços Negociados por Cliente

**Módulo Nativo do Magento:**
- **Shared Catalogs** (Catálogos Compartilhados)
- **Tier Prices** (Preços por quantidade)
- **Special Prices** (Preços especiais)

**Implementação Customizada:**
- ✅ `GroupPricePlugin` - Descontos automáticos por grupo
- ✅ Preços customizados por cliente (via API)

**Configuração de Catálogos Compartilhados:**
```
Admin > Catalog > Shared Catalogs

1. Criar catálogo "Atacado Premium"
2. Definir preços customizados
3. Associar clientes ao catálogo
4. Ativar catálogo
```

**Preços por Quantidade (Tier Prices):**
```
Admin > Catalog > Products > [Produto] > Advanced Pricing

Tier Prices:
- Qty: 10-49 → Desconto 5%
- Qty: 50-99 → Desconto 10%
- Qty: 100-499 → Desconto 15%
- Qty: 500+ → Desconto 20%
```

### 📦 Catálogos Customizados por Cliente

**Cenários:**
- Cliente A vê apenas peças Honda/Yamaha
- Cliente B vê apenas peças de alta performance
- Cliente C vê catálogo completo

**Implementação:**
```php
// Via Plugin ou Observer
// Restringir produtos por atributo customizado
$collection->addAttributeToFilter('customer_visibility', ['in' => $allowedCustomers]);
```

### 🔒 Visibilidade de Preços

**Configuração:**
```
Admin > Stores > Configuration > Grupo Awamotos > B2B Settings > Visibilidade de Preços

- Ocultar Preços para Visitantes: Sim
- Ocultar Botão Comprar: Sim
- Mensagem Personalizada: "Faça login para ver preços"
- Mostrar para Pendentes: Não
```

**Status no Projeto:** ✅ Implementado

### ✅ Checklist de Preços

- [x] Descontos por grupo funcionando
- [ ] Catálogos compartilhados configurados
- [ ] Preços por quantidade configurados
- [ ] Preços customizados por cliente
- [x] Visibilidade de preços configurada
- [ ] Catálogos customizados por cliente
- [ ] Histórico de alterações de preço

---

## 4. Sistema de Compatibilidade (Fitment)

### 🏍️ Fitment - Compatibilidade de Peças

**Módulo Implementado:** ✅ `GrupoAwamotos_Fitment`

**Funcionalidades:**
- Busca por Marca/Modelo/Ano da moto
- Filtro de produtos compatíveis
- Atributos de produto: `marca_moto`, `modelo_moto`, `ano_moto`
- Interface AJAX com seleção cascata

**URLs:**
- `/fitment/fitment/index` - Busca de compatibilidade
- `/fitment/ajax/models` - AJAX modelos
- `/fitment/ajax/years` - AJAX anos

**Atributos de Produto:**
```php
// Atributos criados automaticamente
- marca_moto (Marca da Moto)
- modelo_moto (Modelo da Moto)
- ano_moto (Ano da Moto)
```

**Uso no Catálogo:**
```
1. Cliente seleciona marca (ex: Honda)
2. Sistema carrega modelos disponíveis (ex: CB 600F, CBR 600RR)
3. Cliente seleciona modelo
4. Sistema carrega anos disponíveis (ex: 2015, 2016, 2017)
5. Cliente seleciona ano
6. Sistema exibe apenas peças compatíveis
```

### 🔧 Configuração Fitment

```
Admin > Stores > Configuration > Grupo Awamotos > Fitment Settings

- Habilitar Fitment: Sim
- Placeholder: "Digite marca, modelo ou ano"
- Sugestões: "Honda, Yamaha, Kawasaki, Suzuki"
```

### 📊 Importação em Massa

**Formato CSV:**
```csv
sku,marca_moto,modelo_moto,ano_moto
PECA-001,Honda,CB 600F,2015
PECA-001,Honda,CB 600F,2016
PECA-002,Yamaha,R6,2017
PECA-002,Yamaha,R6,2018
```

**Script de Importação:**
```bash
php scripts/import_fitment_data.php --file fitment_data.csv
```

### ✅ Checklist Fitment

- [x] Módulo Fitment instalado
- [x] Atributos de compatibilidade criados
- [x] Interface de busca funcionando
- [ ] Dados de compatibilidade importados
- [ ] Testes com diferentes marcas/modelos
- [ ] Performance otimizada (índices)
- [ ] Fallback para busca genérica

---

## 5. Pedidos em Grande Volume

### 📦 Quick Order (Pedido Rápido)

**Módulo Implementado:** ✅ `GrupoAwamotos_B2B`

**URL:** `/b2b/quickorder`

**Funcionalidades:**
- Adicionar múltiplos produtos por SKU
- Interface com linhas para SKU + Quantidade
- Importação em massa via texto
- Validação AJAX em tempo real

**Formato de Importação:**
```
SKU1,10
SKU2,25
SKU3,5
```

**Delimitadores Suportados:**
- Vírgula (`,`)
- Ponto-e-vírgula (`;`)
- Tab
- Espaço

### 🛒 Shopping Lists (Listas de Compras)

**URL:** `/b2b/shoppinglist`

**Funcionalidades:**
- Criar múltiplas listas
- Adicionar produtos às listas
- Adicionar toda lista ao carrinho
- Compartilhar listas entre usuários da empresa

**Casos de Uso:**
- Lista de peças para manutenção preventiva
- Lista de estoque mínimo
- Lista de produtos frequentes

### 📋 Requisições de Compra

**Funcionalidade Recomendada:**
- Usuários da empresa criam requisições
- Aprovador aprova requisição
- Requisição vira pedido automaticamente

**Implementação:**
```php
// Criar módulo customizado ou usar extensão
// Exemplo: Amasty_RequestQuote ou custom
```

### ✅ Checklist Pedidos

- [x] Quick Order implementado
- [x] Shopping Lists implementado
- [ ] Requisições de compra
- [ ] Importação CSV de pedidos
- [ ] Validação de estoque em massa
- [ ] Cálculo de frete para grandes volumes
- [ ] Desconto progressivo por volume

---

## 6. Aprovação de Pedidos

### ✅ Sistema de Aprovação

**Funcionalidades Necessárias:**
- Aprovação obrigatória para pedidos acima de X valor
- Múltiplos níveis de aprovação
- Notificações por email
- Histórico de aprovações

**Módulo Recomendado:**
```bash
composer require amasty/magento2-quote
# Ou implementação customizada
```

**Fluxo:**
1. Cliente cria pedido
2. Sistema verifica se precisa aprovação
3. Envia notificação para aprovador
4. Aprovador aprova/rejeita no admin
5. Cliente recebe notificação
6. Pedido é processado

**Configuração:**
```php
// Via Admin ou código
- Valor mínimo para aprovação: R$ 5.000
- Aprovadores por grupo de cliente
- Tempo limite para aprovação: 48h
```

### 📊 Dashboard de Aprovações

**Admin:**
```
Admin > B2B > Aprovações Pendentes

- Lista de pedidos aguardando aprovação
- Filtros por cliente, valor, data
- Ações: Aprovar, Rejeitar, Ver detalhes
```

### ✅ Checklist Aprovação

- [ ] Sistema de aprovação implementado
- [ ] Regras de aprovação configuradas
- [ ] Aprovadores definidos
- [ ] Notificações configuradas
- [ ] Dashboard de aprovações
- [ ] Histórico de aprovações
- [ ] Relatórios de aprovação

---

## 7. Integração com ERP Industrial

### 🔄 ERPs Comuns no Brasil

**Principais ERPs:**
- **TOTVS Protheus** - ERP industrial
- **SAP Business One** - ERP enterprise
- **Tiny ERP** - ERP brasileiro
- **Bling** - ERP e-commerce
- **Omie** - ERP cloud

### 📡 Integração via API REST

**Endpoints Magento:**
```php
// Produtos
GET /rest/default/V1/products
POST /rest/default/V1/products
PUT /rest/default/V1/products/:sku

// Clientes
GET /rest/default/V1/customers
POST /rest/default/V1/customers

// Pedidos
GET /rest/default/V1/orders
POST /rest/default/V1/orders

// Estoque
GET /rest/default/V1/inventory/sources
PUT /rest/default/V1/inventory/sources/:sourceCode
```

### 🔄 Sincronização Bidirecional

**Fluxo Recomendado:**
1. **Produtos:** ERP → Magento (criação/atualização)
2. **Estoque:** ERP → Magento (atualização em tempo real)
3. **Pedidos:** Magento → ERP (criação de pedido)
4. **Clientes:** Magento → ERP (novos clientes)
5. **Preços:** ERP → Magento (atualização de preços)

**Frequência:**
- Produtos: Diária (noite)
- Estoque: Tempo real (via webhook)
- Pedidos: Imediato (via API)
- Preços: Diária ou sob demanda

### 🔌 Middleware Recomendado

**Opções:**
1. **Magento Native API** - REST/SOAP
2. **Magento GraphQL** - Moderno, eficiente
3. **Magento Message Queue** - Assíncrono
4. **Custom Middleware** - MuleSoft, Zapier, etc.

### ✅ Checklist Integração ERP

- [ ] ERP escolhido e contratado
- [ ] API do ERP documentada
- [ ] Credenciais configuradas
- [ ] Sincronização de produtos testada
- [ ] Sincronização de estoque testada
- [ ] Sincronização de pedidos testada
- [ ] Sincronização de clientes testada
- [ ] Sincronização de preços testada
- [ ] Tratamento de erros implementado
- [ ] Logs de sincronização
- [ ] Testes end-to-end realizados

---

## 8. Gestão de Estoque Complexa

### 📊 Múltiplas Fontes de Estoque

**Magento Multi-Source Inventory (MSI):**
```
Admin > Stores > Inventory > Sources

Fontes:
- Armazém Principal (São Paulo)
- Armazém Regional (Rio de Janeiro)
- Armazém Regional (Belo Horizonte)
- Estoque de Terceiros
```

**Configuração:**
```bash
# Habilitar MSI
php bin/magento setup:upgrade

# Criar fontes via Admin ou API
```

### 🔄 Reserva de Estoque

**Funcionalidades:**
- Reserva automática ao adicionar ao carrinho
- Tempo de reserva configurável (ex: 30 minutos)
- Liberação automática após expiração
- Reserva manual para cotações aprovadas

### 📦 Gestão de Lotes e Validade

**Para Peças com Validade:**
- Número de lote
- Data de fabricação
- Data de validade
- FIFO (First In, First Out)

**Módulo Recomendado:**
```bash
# Extensão customizada ou
# Amasty_Inventory ou similar
```

### ✅ Checklist Estoque

- [ ] MSI habilitado e configurado
- [ ] Múltiplas fontes criadas
- [ ] Reserva de estoque configurada
- [ ] Sincronização com ERP funcionando
- [ ] Gestão de lotes (se necessário)
- [ ] Alertas de estoque baixo
- [ ] Relatórios de estoque

---

## 9. Cotações e Negociação

### 📋 Sistema de Cotações (RFQ)

**Módulo Implementado:** ✅ `GrupoAwamotos_B2B`

**URLs:**
- `/b2b/quote/request` - Solicitar cotação
- `/b2b/quote/history` - Histórico de cotações

**Fluxo:**
1. Cliente solicita cotação (produto ou lista)
2. Admin recebe notificação
3. Admin responde com preço e prazo
4. Cliente recebe notificação
5. Cliente aprova cotação
6. Cotação vira pedido automaticamente

**Status de Cotação:**
- `pending` - Aguardando análise
- `processing` - Em análise
- `approved` - Aprovada com valor
- `rejected` - Rejeitada
- `expired` - Expirada
- `converted` - Convertida em pedido

### 💬 Negociação de Preços

**Funcionalidades:**
- Cliente faz contraproposta
- Admin negocia preço
- Histórico de negociação
- Aprovação final

### ✅ Checklist Cotações

- [x] Sistema de cotações implementado
- [x] Notificações configuradas
- [ ] Negociação de preços
- [ ] Histórico de negociação
- [ ] Conversão automática em pedido
- [ ] Relatórios de cotações
- [ ] Taxa de conversão de cotações

---

## 10. Números de Peças e Cross-Reference

### 🔢 Números OEM e Cross-Reference

**Atributos Necessários:**
```php
// Atributos de produto
- oem_number - Número OEM original
- oem_numbers - Múltiplos números OEM (separados por vírgula)
- cross_reference - Referências cruzadas
- part_number - Número da peça do fabricante
- manufacturer_code - Código do fabricante
```

**Busca Inteligente:**
```php
// Buscar produto por:
- SKU
- Nome
- Número OEM
- Cross-reference
- Código do fabricante
```

### 🔍 Busca Avançada

**Funcionalidades:**
- Busca por número OEM
- Busca por cross-reference
- Sugestões automáticas
- Resultados com compatibilidade

**Implementação:**
```php
// Plugin em ProductRepository
// Adicionar busca por atributos customizados
$collection->addAttributeToFilter([
    ['attribute' => 'oem_number', 'like' => '%' . $query . '%'],
    ['attribute' => 'cross_reference', 'like' => '%' . $query . '%'],
]);
```

### 📊 Importação de Cross-Reference

**Formato CSV:**
```csv
sku,oem_number,cross_reference,manufacturer_code
PECA-001,12345-ABC,67890-XYZ,MFG-001
PECA-002,23456-DEF,78901-UVW,MFG-002
```

### ✅ Checklist Números de Peças

- [ ] Atributos OEM criados
- [ ] Atributos cross-reference criados
- [ ] Busca por OEM implementada
- [ ] Busca por cross-reference implementada
- [ ] Importação em massa configurada
- [ ] Validação de números OEM
- [ ] Sugestões automáticas

---

## 11. Performance para Alto Volume

### ⚡ Otimizações Específicas B2B

**Cache:**
```bash
# Cache de catálogos compartilhados
# Cache de preços por cliente
# Cache de compatibilidade (Fitment)
```

**Indexadores:**
```bash
# Modo schedule (agendado)
php bin/magento indexer:set-mode schedule

# Indexadores críticos:
# - Catalog Product Price
# - Catalog Product Flat
# - Inventory
```

**Banco de Dados:**
```sql
-- Índices adicionais para B2B
CREATE INDEX idx_customer_group ON catalog_product_index_price(customer_group_id);
CREATE INDEX idx_fitment ON catalog_product_entity_varchar(attribute_id, value);
```

### 📊 Monitoramento

**Métricas Importantes:**
- Tempo de carregamento de catálogo
- Tempo de busca de produtos
- Tempo de cálculo de preço
- Tempo de adição ao carrinho
- Tempo de checkout

**Ferramentas:**
- New Relic APM
- Magento Performance Toolkit
- Google PageSpeed Insights

### ✅ Checklist Performance

- [x] Cache habilitado
- [ ] Cache de preços B2B
- [ ] Cache de Fitment
- [ ] Índices de banco otimizados
- [ ] CDN configurado
- [ ] Redis para cache/sessões
- [ ] Varnish para FPC
- [ ] Monitoramento ativo

---

## 12. Checklist Completo B2B

### 🎯 Configuração Inicial

- [x] Magento 2.4.8-p3 instalado
- [x] Módulo B2B customizado instalado
- [x] Módulo Fitment instalado
- [x] Validação CPF/CNPJ implementada
- [x] Grupos de clientes B2B criados

### 👥 Clientes B2B

- [x] Grupos de clientes configurados
- [x] Atributos B2B adicionados
- [x] Processo de aprovação funcionando
- [x] Validação de CNPJ funcionando
- [x] Limite de crédito implementado
- [ ] Representantes de vendas configurados
- [ ] Categorização de clientes

### 💰 Preços e Catálogos

- [x] Descontos por grupo funcionando
- [ ] Catálogos compartilhados configurados
- [ ] Preços por quantidade configurados
- [ ] Preços customizados por cliente
- [x] Visibilidade de preços configurada
- [ ] Catálogos customizados por cliente

### 🏍️ Fitment

- [x] Módulo Fitment instalado
- [x] Atributos de compatibilidade criados
- [x] Interface de busca funcionando
- [ ] Dados de compatibilidade importados
- [ ] Testes realizados

### 📦 Pedidos

- [x] Quick Order implementado
- [x] Shopping Lists implementado
- [ ] Requisições de compra
- [ ] Importação CSV de pedidos
- [ ] Validação de estoque em massa

### ✅ Aprovação

- [ ] Sistema de aprovação implementado
- [ ] Regras de aprovação configuradas
- [ ] Aprovadores definidos
- [ ] Notificações configuradas

### 🔄 Integração ERP

- [ ] ERP escolhido
- [ ] API configurada
- [ ] Sincronização de produtos
- [ ] Sincronização de estoque
- [ ] Sincronização de pedidos
- [ ] Sincronização de preços

### 📊 Estoque

- [ ] MSI habilitado
- [ ] Múltiplas fontes criadas
- [ ] Reserva de estoque configurada
- [ ] Sincronização com ERP

### 📋 Cotações

- [x] Sistema de cotações implementado
- [x] Notificações configuradas
- [ ] Negociação de preços
- [ ] Conversão automática

### 🔢 Números de Peças

- [ ] Atributos OEM criados
- [ ] Busca por OEM implementada
- [ ] Cross-reference configurado
- [ ] Importação em massa

### ⚡ Performance

- [x] Cache habilitado
- [ ] Cache B2B otimizado
- [ ] Índices de banco otimizados
- [ ] CDN configurado
- [ ] Monitoramento ativo

---

## 📚 Recursos Adicionais

### 📖 Documentação

- **Módulo B2B:** `app/code/GrupoAwamotos/B2B/README.md`
- **Módulo Fitment:** `app/code/GrupoAwamotos/Fitment/README.md`
- **Configuração B2B:** `CONFIGURACAO_B2B.md`
- **Melhores Práticas Brasil:** `MELHORES_PRATICAS_MAGENTO2_BRASIL.md`

### 🔧 Comandos Úteis

```bash
# Verificar status dos módulos B2B
php bin/magento module:status | grep -i b2b

# Limpar cache
php bin/magento cache:flush

# Reindexar
php bin/magento indexer:reindex

# Compilar
php bin/magento setup:di:compile

# Deploy estático
php bin/magento setup:static-content:deploy pt_BR -f
```

### 📞 Suporte

**Logs:**
- `var/log/system.log`
- `var/log/exception.log`
- `var/log/b2b.log` (se configurado)

**Testes:**
```bash
php scripts/test_b2b_module.php
php scripts/test_b2b_enhancements.php
```

---

## 🎯 Conclusão

Este documento apresenta as **melhores práticas do Magento 2 B2B para uma grande fábrica de peças de motos**, cobrindo desde configurações básicas até integrações avançadas.

**Principais pontos:**
- ✅ Módulos B2B customizados implementados
- ✅ Sistema de compatibilidade (Fitment)
- ✅ Gestão de clientes e aprovação
- ✅ Preços negociados e catálogos compartilhados
- ✅ Pedidos em grande volume
- ✅ Integração com ERP industrial
- ✅ Performance otimizada

**Próximos passos:**
1. Revisar checklist completo
2. Implementar itens pendentes
3. Configurar integração com ERP
4. Importar dados de compatibilidade
5. Testar fluxo completo B2B
6. Treinar equipe de vendas
7. Fazer deploy para produção

---

**Última atualização:** Dezembro 2025  
**Versão:** 1.0  
**Mantido por:** Equipe de Desenvolvimento

---

**Happy Coding! 🚀🏍️**

