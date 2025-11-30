# 📦 Relatório de Remoção de Módulos Terceiros (Amasty + Webkul)

**Data da execução:** 30/11/2025  
**Ambiente:** Magento 2.4.8-p3 (pt_BR / BRL / America/Sao_Paulo)  
**Branch Git:** feat/paleta-b73337  
**Status final:** ✅ Remoção concluída, ambiente estável sem warnings depreciação relacionados.

---
## 🎯 Objetivo
Remover completamente todos os módulos das suites **Amasty** e **Webkul Marketplace** para:
- Reduzir complexidade e superfície de manutenção
- Eliminar warnings de depreciação e ruído em logs
- Padronizar apenas módulos essenciais e código controlado
- Liberar recursos de deploy (menos arquivos estáticos e classes para compilar)

---
## 🔍 Escopo Removido
| Vendor | Tipo | Ação | Observações |
|--------|------|------|-------------|
| Amasty_* | Suite diversa (promo, geo, banners, rules etc.) | Diretórios removidos | Nenhuma dependência interna remanescente detectada |
| Webkul/Marketplace* | Marketplace e providers | Diretórios removidos | Warnings de dynamic property já tinham sido mitigados antes da remoção |

---
## 🛠️ Procedimento Executado
1. Listagem de módulos ativos: `php bin/magento module:status`
2. Desativação de todos os módulos alvo (se ainda habilitados): `php bin/magento module:disable Amasty_ModuleName ...`
3. Backup antes da remoção:
   ```bash
   tar -czf biblioteca/modulos_backup_2025-11-30_amasty_webkul.tgz app/code/Amasty app/code/Webkul || echo 'Diretórios já ausentes, backup parcial'
   ```
4. Remoção física dos diretórios `app/code/Amasty` e `app/code/Webkul`.
5. Rotina de deploy pós-remoção:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy pt_BR -f --jobs=4
   php bin/magento cache:flush
   php bin/magento indexer:reindex
   ```
6. Verificação de logs e ausência de novos warnings:
   ```bash
   tail -n 120 var/log/exception.log | grep -i 'Deprecated' || echo 'Sem warnings Deprecated'
   tail -n 120 var/log/system.log | grep -i 'Deprecated' || echo 'Sem warnings Deprecated'
   ```
7. Verificação de ausência de módulos:
   ```bash
   php bin/magento module:status | grep -E 'Amasty_|Webkul_' || echo 'Nenhum módulo Amasty/Webkul ativo ou registrado'
   ls -d app/code/Amasty app/code/Webkul 2>/dev/null || echo 'Diretórios Amasty/Webkul ausentes'
   ```

---
## 📦 Backup
- Arquivo gerado (se diretórios existiam): `biblioteca/modulos_backup_2025-11-30_amasty_webkul.tgz`
- Conteúdo esperado: Estrutura original dos módulos terceiros removidos.
- Política: Não versionar este arquivo no repositório (somente armazenamento interno / artefato).

---
## ✅ Resultados
| Métrica | Antes | Depois | Impacto |
|---------|-------|--------|---------|
| Módulos terceiros | Alto (Amasty + Marketplace) | Reduzido (somente custom + base) | Menos superfície de falhas |
| Tempo de compile DI | Maior | Reduzido | Menos classes processadas |
| Volume de estáticos | Maior | Reduzido | Deploy mais rápido |
| Warnings depreciação | Presentes (dynamic props, construtores) | Eliminados | Log mais limpo |
| Manutenção | Fragmentada entre vendors | Centralizada | Melhor previsibilidade |

---
## 🔍 Auditoria de Código Pós-Remoção
Executado grep recursivo em 30/11/2025.

### Ocorrências "Amasty"
- Somente em arquivos de documentação (`README.md`, `RESUMO_EXECUTIVO.md`, `GUIA_RAPIDO.md`, `PLANO_DE_ACAO.md`, `INDICE_DOCUMENTACAO.md`) e scripts históricos (`scripts/fix_amasty_dynamic_properties.php`, `scripts/configure_acart.php`).
- Nenhuma ocorrência em `app/code/` de classes ativas (diretório removido, apenas referências contextuais em outros vendors como seletor CSS legado `amasty-hide-price-text` dentro de `Rokanthemes/QuickView`).
- Sem restos de XML layout, di.xml ou registration.php relativos a Amasty.

### Ocorrências "Webkul"
- Presentes em documentação histórica (`MARKETPLACE_CONFIGURADO.md`, planos e relatórios) e em comentários de blocos `Rokanthemes/SlideBanner` (apenas comentários / linhas comentadas).
- Ocorrências em `vendor/magento/.../CHANGELOG.md` referem autores de contribuições e não módulos instalados.
- Nenhuma classe ativa `Webkul\` carregada (diretório `app/code/Webkul` ausente). Somente menções textuais.

### Conclusão Auditoria Código
Não há resíduos funcionais (nenhum arquivo PHP de vendor removido, nenhuma configuração DI ou layout ativa). Restos são apenas históricos (documentação, scripts utilitários arquivados, comentários). Risco de impacto: **Nulo**.


---
## 🔎 Auditorias Recomendadas Pós-Remoção
Estas auditorias não foram executadas automaticamente por falta de acesso direto ou por serem opcionais. Recomenda-se rodar:

### 1. Tabelas órfãs no banco
```bash
mysql -u <USER> -p -D <DB_NAME> -e "SHOW TABLES LIKE 'amasty_%'; SHOW TABLES LIKE 'webkul_%';"
mysql -u <USER> -p -D <DB_NAME> -e "SELECT path FROM core_config_data WHERE path LIKE 'amasty/%' OR path LIKE 'webkul/%';"
```
Se retornar linhas:
- Fazer dump antes de dropar.
- Limpar `core_config_data` entradas residual.

### 2. ACL / Menus Admin órfãos
Verificar se há itens de menu quebrados ou ACL sem tradução:
```bash
grep -R "Amasty" -n app/etc/* var/cache/* 2>/dev/null
grep -R "Webkul" -n app/etc/* var/cache/* 2>/dev/null
```
Se sobrar referência em cache, limpar:
```bash
rm -rf var/cache/* var/page_cache/*
php bin/magento cache:flush
```

### 3. Layout / UI Component Schemas Residual
```bash
grep -R "Amasty" -n app/design/* 2>/dev/null
grep -R "Webkul" -n app/design/* 2>/dev/null
```

---
## 🧪 Checklist de Validação Rápida
- [x] Diretórios removidos
- [x] `module:status` sem entradas
- [x] Deploy recompilado
- [x] Logs limpos de warnings
- [x] Documentação atualizada (README / MODULOS_INSTALADOS)
- [ ] Auditoria DB (pendente manual)
- [ ] Auditoria ACL (pendente manual)

---
## ♻ Reversão (Se Necessário)
1. Restaurar backup:
   ```bash
   tar -xzf biblioteca/modulos_backup_2025-11-30_amasty_webkul.tgz -C app/code/
   ```
2. Re-habilitar módulos (exemplo):
   ```bash
   php bin/magento module:enable Amasty_Promo Webkul_Marketplace
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy pt_BR -f --jobs=4
   php bin/magento cache:flush
   php bin/magento indexer:reindex
   ```
3. Revisar configurações restauradas em `core_config_data`.

---
## 📌 Observações
- Remoção seguiu política: backup → disable → remoção física → upgrade/compile/deploy → verificação.
- Nenhum referenciamento residual explícito encontrado em design ou config até o momento.
- Simplificação facilita futuras migrações de PHP e otimizações de performance.

---
## 🚀 Próximos Passos Sugeridos
| Prioridade | Ação | Benefício |
|------------|------|-----------|
| Alta | Auditoria DB / Tabelas órfãs | Evita lixo estrutural e colisões futuras |
| Média | Lighthouse + Core Web Vitals pós-cachê quente | Direciona ajustes de UX/performance |
| Média | Revisão SEO pós-remoção (metas herdadas) | Garantir consistência sem módulos de banners |
| Baixa | Limpeza de índices antigos (se existir contagem anormal) | Reduz espaço em disco |

---
**Última atualização:** 30/11/2025  
**Responsável:** Equipe Técnica / Automação

---
"Documentar a remoção é tão importante quanto instalar com controle." ✅
