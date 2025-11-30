# 🧩 Mapa de Seletores do Tema Ayo (ativo)

Data: 30/11/2025  
Tema: `app/design/frontend/ayo/ayo_default`  
Fonte: LESS em `web/css/source/`

---
## Núcleo do Tema
- `styles-l.less`, `styles-m.less`: folhas principais (desktop/mobile) que importam parciais.
- Parciais principais detectadas:
  - `_reset_themes.less`: base, mixins, tipografia, botões, mensagens.
  - `_responsive_themes.less`: breakpoints e responsividade (importado indiretamente).
  - `_search_ajax.less`: autocomplete de busca.
  - `_forms.less`: campos, labels e estados (via `_extend.less`).
  - `_extend.less`: overrides e acessibilidade (skip links, focus-visible, sticky header).
  - `_homepage_awamotos.less`: customizações da Home (hero, grids, cartões).

---
## Seletores Relevantes por Parcial

### `_reset_themes.less`
- `.btn`, `.button`, `.input-control`, `.switcher-dropdown`, `.label`, `.message.info|success|error`, `.page.messages`
- Acessibilidade: `._keyfocus *:focus`, `div.mage-error[generated]`
- Mixins: `.transition()`, `.rounded-corners()`, `.border-radius()`, `.buttonMixin()`

### `_search_ajax.less`
- Contêiner: `.searchsuite-autocomplete`
- Subáreas: `.suggest`, `.product`, `.title`, `.see-all`, `.no-result`
- Estrutura lista: `.qs-option-image`, `.qs-option-info(.noimage)`, `.qs-option-title`, `.qs-option-reviews .product-reviews-summary`, `.reviews-actions`, `.qs-option-price`, `.qs-option-addtocart`
- Estados: `.active`, `.selected`, `.gray-row`
- Integrações: `.block-search .control`, `.block-search .block-content .field-by-cat-search`, `.chosen-container(.single)`, `.chosen-results`

### `_extend.less`
- Cabeçalho: `.header-container`, `.header-control`, `.header-wrapper-sticky.enable-sticky`, `.header-control.header-nav`
- Acessibilidade e navegação: `.action:focus-visible`, `.quickview-link:focus-visible`, `.wishlist-link:focus-visible`, `.compare-link:focus-visible`, `.navigation a:focus-visible`
- Skip links: `.skip-link`, `.skip-link:focus`
- Navegação/links: `.navigation a`, `.breadcrumbs a` (+ estados hover)
- Componentes comuns: `.wrapper_slider`

### `_homepage_awamotos.less`
- Estruturas Home: `.home-hero`, `.home-fitment`, `.home-benefits`, `.home-categories`, `.home-featured`, `.home-promo`, `.home-new`, `.home-community`
- Cartões de produto: `.product-item`, `.product-item-info`, `.product-image-photo`, `.product-item-name`, `.price-box`, `.actions-primary`, `.actions-secondary`
- Grid responsivo: classes de colunas e media queries específicas

---
## Padrões de Cartões (PLP/PDP compatíveis)
- `.product-item`, `.product-item-info`, `.product-image-photo`, `.product-item-name`, `.price-box`
- Ações: `.actions-primary` (add to cart), `.actions-secondary` (wishlist/compare se ativos)

---
## Observações
- Seletores relacionados a Amasty nos templates QuickView aparecem apenas como referências CSS (`.amasty-hide-price-text`) e não impactam funcionalidade.
- Nenhum seletor Webkul ativo no tema (somente menções documentais).

---
## Próximos passos sugeridos
- Consolidar tokens de cor/tipografia em variáveis e revisar contraste AA.
- Mapear dependências JS associadas (autocomplete, quickview) e lazy loading de imagens.
- Rodar Lighthouse e ajustar CLS (font-display, dimensões de mídia).
