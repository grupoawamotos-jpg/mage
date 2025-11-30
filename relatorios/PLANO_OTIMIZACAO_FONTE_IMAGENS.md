# Plano de otimização de fontes e imagens (Home Ayo)

## 1. Fontes Rubik — preload + self-hosting

> **Status (30/11/2025):** concluído. Pesos 400/500/700 baixados para `app/design/frontend/ayo/ayo_default/web/fonts/`, declarados via `.lib-font-face` em `_extend.less`, `@font-family__sans-serif` atualizado para priorizar Rubik e `default_head_blocks.xml` agora usa apenas preloads locais (sem chamadas Google Fonts).

1. **Inventário atual**
   - `default_head_blocks.xml` injeta Google Fonts para `Rubik` (300–900). Cada peso carrega de `fonts.gstatic.com`, atrasando o LCP.
   - `_typography.less` já força `font-display: swap`, então falta apenas priorizar o carregamento inicial e reduzir a quantidade de variantes.
2. **Etapas sugeridas**
   - **Selecionar pesos essenciais**: mapear uso real (provavelmente 400, 500 e 700). Remover pesos não utilizados do link Google Fonts para evitar transferências extras.
   - **Preconnect / Preload**:
     - Adicionar no `default_head_blocks.xml`:
       ```xml
       <link rel="preconnect" href="https://fonts.googleapis.com"/>
       <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
       <link rel="preload" as="font" type="font/woff2" href="{{base_url}}frontend/ayo/ayo_default/pt_BR/fonts/rubik-latin-400.woff2" crossorigin="anonymous"/>
       ```
     - Enquanto os arquivos não estiverem self-hosted, usar a mesma ideia apontando para as URLs `fonts.gstatic.com` copiadas do CSS entregue pelo Google Fonts.
   - **Self-hosting** (recomendado para remover bloqueio externo):
     1. Baixar os WOFF2 de `Rubik` via Google Fonts ou https://gwfh.mranftl.com/.
     2. Salvar em `app/design/frontend/ayo/ayo_default/web/fonts/` (ex.: `Rubik-Regular.woff2`).
     3. Declarar `@font-face` customizado em `_typography.less` utilizando `.lib-font-face` apontando para `@{baseDir}fonts/Rubik-Regular`.
     4. Atualizar `default_head_blocks.xml` removendo o `<link src="https://fonts.googleapis.com..."/>` e adicionando preloads para os arquivos locais.
   - **Verificação**: após deploy, medir novamente com `Lighthouse` / `WebPageTest` e garantir que `First Contentful Paint` não ficou pior por excesso de preloads (máximo 2–3 fontes críticas).

## 2. Imagens — WebP + srcset/picture

1. **Inventário Home atual**
   - Hero e cartões usam `<img>` direto em blocos CMS com arquivos `jpg/png`. Não há `srcset` nem `loading` configurado (deslocamentos já mitigados com JS `home-media-optimizations.js`).
2. **Etapas sugeridas**
   - **Gerar WebP**
     - Converter os assets principais (`hero_home5.jpg`, `promo_home5.jpg`, etc.) para WebP mantendo um fallback JPG na mesma pasta (`pub/media/home/`).
     - Usar `bin/magento catalog:image:resize` apenas para produtos; para CMS, basta enviar via Admin > Conteúdo > Blocos.
   - **Atualizar blocos CMS**
     - Empacotar cada imagem estratégica em `<picture>`:
       ```html
       <picture class="ayo-home5-hero-media">
         <source srcset="{{media url=\"home/hero.webp\"}}" type="image/webp" />
         <img src="{{media url=\"home/hero.jpg\"}}" alt="" width="1440" height="640" loading="lazy" />
       </picture>
       ```
     - Para grids/carrosséis, aplicar `srcset` com diferentes larguras (480/768/1024) geradas manualmente ou via `Image Optimizer`.
   - **Fetch Priority / Lazy**
     - Reutilizar `home-media-optimizations.js` apenas para casos dinâmicos. Para hero principal, definir diretamente `fetchpriority="high" decoding="async"` no `<img>`.
   - **CMS repetível**
     - Documentar no bloco `home_slider` e `home_featured` que todo novo asset deve seguir o padrão `<picture>` para manter coerência.
3. **Validação**
   - Após atualizar blocos e fazer deploy estático, executar `curl -I` nos WebP para garantir `content-type: image/webp`.
   - Rodar Lighthouse (modo mobile) e comparar LCP/CLS antes e depois; registrar resultados em `relatorios/LCP_HOME.md` para referência histórica.

## 3. Sequência de execução sugerida

1. Self-hosting das fontes + preloads (impacto direto em FCP/LCP).
2. Conversão e atualização de imagens críticas com `<picture>`.
3. Deploy completo (`setup:upgrade` se necessário, `static-content:deploy pt_BR -f`, `cache:flush`).
4. Métricas: Lighthouse + `curl -w time_starttransfer` (já usado) e logs no relatório.
