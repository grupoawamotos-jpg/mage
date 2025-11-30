# 🎨 Documentação Oficial - Tema Ayo Magento 2

**Fonte:** [https://ayo.nextsky.co/documentation/](https://ayo.nextsky.co/documentation/)  
**Versão do Tema:** Base Package 2.3.x + Patches 2.4.x  
**Magento:** 2.4.8-p3  
**Status no Projeto:** ✅ Instalado e Ativo

---

## 📋 Índice

1. [Estrutura de Arquivos](#1-estrutura-de-arquivos)
2. [Requisitos Técnicos](#2-requisitos-técnicos)
3. [Instalação](#3-instalação)
4. [Conteúdo do Tema](#4-conteúdo-do-tema)
5. [Personalização](#5-personalização)
6. [Módulos Rokanthemes](#6-módulos-rokanthemes)
7. [Suporte](#7-suporte)

---

## 1. Estrutura de Arquivos

### 📦 Pacote do Tema

O pacote do tema Ayo consiste em várias pastas:

#### **Document**
Contém arquivos de documentação.

#### **Themes Files**
Contém:
- **Base package** - Arquivos do tema Magento que devem ser enviados para o diretório raiz
- **Database** - Arquivo de backup do banco de dados (`ayo.sql`)
- **Quickstart package** - Pacote completo de instalação Magento + tema + conteúdo demo

### 📁 Base Package

**Estrutura:**
```
base_package_2.3.x/
├── app/
├── lib/
├── pub/
└── var/
```

**Patches Disponíveis:**
- `patch_2.4.x.zip` - Para Magento 2.4.0 ou superior
- `patch_2.4.4.zip` - Para Magento 2.4.4
- `patch_2.4.5.zip` - Para Magento 2.4.5
- `patch_2.4.6.zip` - Para Magento 2.4.6
- `patch_2.4.7.zip` - Para Magento 2.4.7

**Status no Projeto:** ✅ Patch 2.4.7 aplicado

---

## 2. Requisitos Técnicos

### 🔧 Magento 2.4.x

**Requisitos:**
- **Elasticsearch** obrigatório antes da instalação
- Ver documentação oficial: [Magento 2.4.x System Requirements](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html)

### 🔧 Magento 2.3.x

**Requisitos:**
- Ver documentação oficial: [Magento 2.3.x System Requirements](https://devdocs.magento.com/guides/v2.3/install-gde/system-requirements.html)

**Status no Projeto:** ✅ Magento 2.4.8-p3 com OpenSearch configurado

---

## 3. Instalação

### 📦 Base Package (Instalação Apenas do Tema)

**Quando usar:** Se você já tem um site Magento e não quer usar o banco de dados demo.

#### 1. Preparação

1. **Backup:**
   - Fazer backup dos arquivos do Magento
   - Fazer backup do banco de dados
   - ⚠️ **Muito importante** antes de instalar o tema

2. **Desabilitar Cache:**
   - Magento Cache (System > Cache Management)
   - Cache adicional no servidor (PHP cache, APC, etc.)

3. **Modo de Manutenção:**
   ```bash
   php bin/magento maintenance:enable
   ```

#### 2. Instalação

1. **Download e Extração:**
   - Baixar pacote do tema do ThemeForest
   - Extrair pacote

2. **Upload de Arquivos:**
   - Upload das pastas do Base package: `app`, `lib`, `pub` para a raiz do Magento
   - Para Magento 2.3.x: Upload e sobrescrever arquivos de `base_package_2.3.x`
   - Para Magento 2.4.x: Upload e sobrescrever arquivos de `patch_2.4.x.zip`
   - Para versões específicas: Aplicar patch correspondente (2.4.4, 2.4.5, 2.4.6, 2.4.7)

3. **Comandos SSH:**
   ```bash
   cd /caminho/para/magento
   
   php bin/magento indexer:reindex
   php bin/magento setup:upgrade
   php bin/magento setup:static-content:deploy -f
   php bin/magento cache:flush
   sudo chmod -R 777 var pub generated
   ```

4. **Desabilitar Modo de Manutenção:**
   ```bash
   php bin/magento maintenance:disable
   ```

#### 3. Importar Blocos e Páginas Estáticos

**Localização:** Admin > Rokanthemes > Import and Export

**Export:**
- Configurar opções de exportação
- Clicar em **Export Block** para exportar todos os blocos estáticos
- Clicar em **Export Page** para exportar todas as páginas
- Arquivos salvos em: `var/cms_blocks.xml`, `var/cms_pages.xml`

**Import:**
- Clicar em **Import Block** para importar blocos
- Clicar em **Import Page** para importar páginas
- Verificar em: Content > Blocks/Pages

#### 4. Configurar Homepage e Tema Padrão

**Homepage:**
1. Após importar CMS Pages e Static Blocks
2. Ir em: **Stores > Configuration > General > Web**
3. Aba **Default Pages**
4. Selecionar tema desejado como homepage
5. **Save config**

**Tema Padrão:**
1. Ir em: **Content > Configuration**
2. Editar store view
3. Selecionar tema que deseja usar
4. **Save configuration**

**Limpar Cache:**
```
System > Cache management > Flush Cache Storage
```

### 🚀 Quick-Start Package (Instalação Completa)

**Quando usar:** Se você está começando do zero e quer instalação completa com dados demo.

1. **Upload:**
   - Upload do arquivo `ayo.zip` da pasta Quickstart package
   - Usar FTP (FileZilla) ou cPanel

2. **Extração:**
   - Extrair `ayo.zip` na pasta do site

3. **Configuração:**
   - Seguir instruções de instalação do Magento
   - Banco de dados demo já incluído

**Status no Projeto:** ✅ Base Package instalado

---

## 4. Conteúdo do Tema

### 🏠 Diagrama de Blocos da Homepage

O tema Ayo organiza a homepage em blocos específicos que podem ser configurados via CMS.

### 📄 Adicionar Código de Slider em Páginas CMS

**Como adicionar slider em páginas CMS:**
1. Criar/Editar página CMS
2. Usar código do widget do slider
3. Inserir widget na página

---

## 5. Personalização

### 🎨 Opções do Tema

**Localização:** Admin > Rokanthemes > Theme Settings

#### Configurações Gerais
- **Auto Render Style Less** - Renderização automática de estilos LESS
- **Page Width** - Largura da página
- **Copyright** - Texto de copyright

#### Fontes
- **Custom Font** - Habilitar/Desabilitar fonte customizada
- **Basic Font Size** - Tamanho da fonte base
- **Basic Font Family** - Família da fonte (Google Fonts ou custom)

#### Cores Personalizadas
- **Text Color** - Cor do texto
- **Link Color** - Cor dos links
- **Link Hover** - Cor dos links ao passar mouse
- **Button Colors** - Cores dos botões
- **Button Hover** - Cor dos botões ao passar mouse

### 📧 Newsletter Popup

**Localização:** Rokanthemes > Theme Settings > Newsletter Popup

**Configurações:**
- **Enable** - Habilitar/Desabilitar popup
- **Width** - Largura do popup
- **Height** - Altura do popup
- **Background Color** - Cor de fundo
- **Background Image** - Imagem de fundo
- **Textos personalizados** - Títulos e descrições

### 🎯 Header

#### Sticky Header Setting
**Localização:** Rokanthemes > Theme Settings > Sticky Header

- Configurar comportamento do header fixo
- Upload de logo separado para sticky header

#### Adicionar Logo
**Localização:** Content > Design > Configuration

1. Editar tema ativo
2. **HTML Head** > Upload Favicon
3. **Header** > Logo Image
4. **Logo Attribute** > Width/Height

#### Customização do Header
- Configurações de layout
- Menu horizontal/vertical
- Links customizados

### 🦶 Footer

#### Estrutura do Footer

O footer do Ayo é dividido em três seções:

1. **Footer Top** - Área superior do rodapé
2. **Footer Primary** - Área principal do rodapé
3. **Footer Bottom** - Área inferior do rodapé (copyright)

#### Footer Top
- Blocos estáticos configuráveis
- Informações da loja
- Links rápidos

#### Footer Primary
- Menu do rodapé
- Links de categorias
- Informações de contato

#### Footer Bottom
- Copyright
- Links legais
- Métodos de pagamento

### 🎠 Slideshow

**Localização:** Rokanthemes > Manager Slider

**Configurações:**
- **Autoplay** - Reprodução automática
- **Navigation** - Botões next/prev
- **Stop On Hover** - Parar ao passar mouse
- **Pagination** - Indicadores de página
- **Items** - Quantidade de itens visíveis
- **Velocidades** - Rewind, pagination, slide
- **Responsividade** - Desktop, Tablet, Mobile

**Adicionar Slides:**
- Rokanthemes > Manage Slider Items
- Upload de imagens
- Links dos banners
- Textos personalizados

### 📋 Menu Customizado

**Localização:** Rokanthemes > Custom Menu

**Configurações:**
- **Enable** - Habilitar/Desabilitar
- **Default Menu Type** - Tipo de menu padrão
- **Visible Menu Depth** - Profundidade do menu visível
- **Static Block** - Blocos antes/depois do menu
- **Category Labels** - Labels (hot, new, sale)

**Customizar Submenu:**
1. Catalog > Categories
2. Escolher categoria
3. **Custom Menu Options:**
   - **Menu Type** - Classic, Full width, Static width
   - **Sub Category Columns** - Colunas de subcategorias
   - **Float** - Posição (left/right)
   - **Icon Image** - Upload de ícone
   - **Font Icon Class** - Classe de ícone de fonte

**Adicionar Conteúdo ao Submenu:**
- 4 editores para 4 posições: top, left, right, bottom
- Adicionar texto, imagens, vídeo, iframe, HTML
- **Left block width** - Largura do bloco esquerdo
- **Right block width** - Largura do bloco direito

**Nota:** No tema Ayo, usar principalmente: **Bottom Block**

### 📐 Menu Vertical

**Localização:** Rokanthemes > Vertical Menu

**Configurações:**
- **Limit show more Cat** - Quantidade inicial de categorias
- **Static Block** - Blocos antes/depois do menu

### 💬 Testimonials (Depoimentos)

#### Configurações
**Localização:** Rokanthemes > Testimonials > Settings

**Opções:**
- **Enable/Disable** - Habilitar módulo
- **Title** - Título dos depoimentos
- **Auto slider** - Slider automático
- **Items on desktop** - Itens no desktop
- **Items on tablet** - Itens no tablet
- **Items on mobile** - Itens no mobile
- **Quantity** - Quantidade de depoimentos
- **Background images** - Imagens de fundo

#### Adicionar Depoimento
**Localização:** Rokanthemes > Testimonials > Manage Testimonial

1. Clicar em **Add New Testimonial**
2. Preencher:
   - Nome
   - Foto
   - Depoimento
   - Avaliação (estrelas)
3. Salvar

### 📝 Blog Posts

#### Configurações do Blog
**Localização:** Rokanthemes > Blog > Blog Settings

**Opções:**
- **Enable/Disable** - Habilitar módulo
- **Title blog slider** - Título do slider na homepage
- **Short description** - Descrição curta
- **Items in desktop** - Itens no desktop
- **Items in mobile** - Itens no mobile
- **Items in tablet** - Itens no tablet
- **Sidebar of blog page** - Sidebar da página do blog

#### Adicionar Post
**Localização:** Rokanthemes > Blog > Posts

1. Clicar em **Add new post**
2. Preencher:
   - Título
   - Conteúdo
   - Imagem destacada
   - Categoria
   - Tags
   - Data de publicação
3. Publicar

---

## 6. Módulos Rokanthemes

### 🔍 Layered Ajax

**Localização:** Store > Configuration > Rokanthemes > Layered Ajax

**Configurações:**
- **Enable/Disable** - Habilitar módulo
- **Open All Tab** - Abrir todas as abas
- **Use Price Range Sliders** - Usar sliders de faixa de preço

### 🛒 One Page Checkout

**Localização:** Rokanthemes > One Page Checkout > Configuration

**Configurações:**
- **Enable** - Habilitar Terms and Conditions
- **Checkbox Text** - Texto do checkbox
- **Checkbox Content** - Conteúdo dos Terms and Conditions
- **Title warning** - Título do aviso
- **Content warning** - Conteúdo do aviso quando desmarcar checkbox

### 🎁 SuperDeals

**Localização:** Rokanthemes > Configuration > Super Deals Settings

**Requisito:** Habilitar módulo primeiro

### 📦 ProductTab

**Localização:** Rokanthemes > Configuration > ProductTab

**Tipos de Produtos Disponíveis:**
- **New Products** - Novos produtos
- **Onsale Products** - Produtos em oferta
- **Bestseller Products** - Produtos mais vendidos
- **Mostview Products** - Produtos mais vistos
- **Feature Products** - Produtos em destaque
- **Price Countdown Products** - Produtos com contagem regressiva

**Configurações Gerais (para cada tipo):**
- **Enable** - Habilitar/Desabilitar módulo
- **Auto** - Auto Play do carousel
- **Title** - Título (deixar vazio para não exibir)
- **Description** - Descrição curta após título
- **Show Price** - Exibir preço
- **Show Add To Cart** - Exibir botão adicionar ao carrinho
- **Show Add To Wishlist** - Exibir ícone wishlist
- **Show Rating** - Exibir avaliações
- **Qty Products** - Quantidade total de produtos no carousel
- **Number Row Show** - Número de linhas exibidas
- **Items Default** - Quantidade padrão de itens por linha
- **Items On Desktop** - Itens no desktop (min 992px)
- **Items On Desktop Small** - Itens no desktop pequeno (769px-991px)
- **Items On Tablet** - Itens no tablet (480px-768px)
- **Items On Mobile** - Itens no mobile (max 479px)
- **Show Next/Back control** - Exibir controles anterior/próximo
- **Show navigation control** - Exibir navegação

### 📑 Category Tab

**Localização:** Rokanthemes > Configuration > Category Tab

**Requisito:** Habilitar módulo primeiro

**Como usar:**
1. Content > Block
2. Clicar em **Show/hide editor** ou **Edit with Page Builder**
3. Escolher **Insert Widget**
4. Selecionar **Category Tab**

**Configurações:**
- **Title** - Título (deixar vazio para não exibir)
- **Description** - Descrição curta
- **Enter From Price** - Valor mínimo de preço
- **Category Ids** - IDs das categorias (separados por vírgula)
- **Slide columns Qty** - Quantidade total de produtos
- **Items Default** - Total de produtos exibidos
- **Items On Desktop** - Itens no desktop (min 992px)
- **Items On Desktop Small** - Itens no desktop pequeno (769px-991px)
- **Items On Tablet** - Itens no tablet (480px-768px)
- **Items On Mobile** - Itens no mobile (max 479px)
- **Display Page Control** - Exibir controles anterior/próximo

**Exibir na Homepage:**
- Ir em Static page
- Escolher **Insert Widget** > **Cms Static block**
- Selecionar bloco estático criado

### 🆕 New Products

**Como configurar:**
1. Catalog > Products
2. Editar produto
3. Definir **Set Product as New from Date**
4. Produto aparecerá como "Novo"

### 🏷️ Onsale Products

**Como configurar:**
1. Catalog > Products
2. Editar produto
3. Clicar em **Advanced Pricing** abaixo do campo Price
4. Definir **Special Price**
5. Produto aparecerá como "Em Oferta"

### ⏰ Price Countdown Products

**Como configurar:**
1. Catalog > Products
2. Editar produto
3. **Advanced Pricing:**
   - Definir **Special Price**
   - Definir **Special Price From Date**
   - Definir **Special Price To Date**
4. **Show Price Count Down** - Escolher **Yes**
5. Produto mostrará contagem regressiva

### ⭐ Featured Products

**Como configurar:**
1. Catalog > Products
2. Editar produto
3. **Featured Product** - Escolher **Yes**
4. Produto aparecerá como "Destaque"

### 🏆 Best Seller Product

**Funcionamento:**
- Automático
- Baseado em produtos adicionados ao carrinho e checkout bem-sucedido
- Não requer configuração manual

### 👁️ Mostviewed Product

**Funcionamento:**
- Automático
- Exibe produtos mais visualizados pelos clientes
- Destaque para produtos populares
- Não requer configuração manual

---

## 7. Suporte

### 📞 Canais de Suporte

**Suporte Oficial:**
- **Ticket System:** [https://support.nextsky.co/](https://support.nextsky.co/)
- **Comentários:** Sistema de comentários do item no ThemeForest

### ⭐ Avaliação

Se o tema atende suas necessidades, por favor:
- **Vote no Tema** - Sua avaliação nos ajuda a criar mais produtos
- **Compartilhe feedback** - Suas opiniões são valiosas

---

## 📚 Referências no Projeto

### ✅ Status de Instalação

- **Tema Ativo:** `ayo/ayo_default`
- **Módulos Rokanthemes:** 27 módulos instalados
- **Variações Disponíveis:** 16 temas (ayo_default, ayo_home2-16, versões RTL)
- **Patch Aplicado:** 2.4.7

### 📁 Arquivos Importantes

```
app/design/frontend/ayo/ayo_default/  # Tema ativo
app/code/Rokanthemes/                 # Módulos do tema
lib/web/rokanthemes/                  # Scripts JS globais
```

### 🔗 Links Relacionados

- **Documentação Oficial:** [https://ayo.nextsky.co/documentation/](https://ayo.nextsky.co/documentation/)
- **Suporte:** [https://support.nextsky.co/](https://support.nextsky.co/)
- **Guia do Projeto:** `TEMA_AYO_GUIA.md`
- **Configuração Completa:** `GUIA_CONFIGURACAO_COMPLETA.md`

---

**Última atualização:** Dezembro 2025  
**Versão:** 1.0  
**Baseado em:** Documentação oficial Ayo Theme

---

**Happy Theming! 🎨**

