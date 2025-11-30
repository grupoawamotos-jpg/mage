<?php

declare(strict_types=1);

namespace GrupoAwamotos\StoreSetup\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StoreConfigurator
{
    private State $appState;
    private BlockFactory $blockFactory;
    private PageFactory $pageFactory;
    private WriterInterface $configWriter;
    private ReinitableConfigInterface $reinitableConfig;
    private ScopeConfigInterface $scopeConfig;
    private CategoryFactory $categoryFactory;
    private CategoryCollectionFactory $categoryCollectionFactory;
    private CategoryRepositoryInterface $categoryRepository;
    private StoreManagerInterface $storeManager;
    private DirectoryList $directoryList;
    private \Rokanthemes\SlideBanner\Model\SliderFactory $sliderFactory;
    private \Rokanthemes\SlideBanner\Model\SlideFactory $slideFactory;

    public function __construct(
        State $appState,
        BlockFactory $blockFactory,
        PageFactory $pageFactory,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        \Rokanthemes\SlideBanner\Model\SliderFactory $sliderFactory,
        \Rokanthemes\SlideBanner\Model\SlideFactory $slideFactory
    ) {
        $this->appState = $appState;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->sliderFactory = $sliderFactory;
        $this->slideFactory = $slideFactory;
    }

    public function run(OutputInterface $output): void
    {
        $this->ensureAreaCode();

        $this->createBlocks($output);
        $this->createOrUpdateHomepage($output);
        $this->configureHomepage($output);
        $this->createCategories($output);
        $this->applyThemeConfigurations($output);

        $this->ensurePlaceholderBanners($output);
        $this->seedSlider($output);

        $this->reinitableConfig->reinit();
    }

    private function ensureAreaCode(): void
    {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode('adminhtml');
        }
    }

    private function createBlocks(OutputInterface $output): void
    {
        foreach ($this->getBlockDefinitions() as $blockData) {
            try {
                $block = $this->blockFactory->create();
                $block->setStoreId(0);
                $block->load($blockData['identifier'], 'identifier');
                $wasExisting = (bool)$block->getId();

                $block->addData([
                    'title' => $blockData['title'],
                    'identifier' => $blockData['identifier'],
                    'content' => $blockData['content'],
                    'is_active' => 1
                ]);
                $block->setStores([0]);
                $block->save();
                $output->writeln(sprintf(' - Bloco %s %s', $blockData['identifier'], $wasExisting ? 'atualizado' : 'criado'));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('<error>   ✗ Erro ao criar/atualizar bloco %s: %s</error>', $blockData['identifier'], $e->getMessage()));
            }
        }
    }

    private function createOrUpdateHomepage(OutputInterface $output): void
    {
        $page = $this->pageFactory->create();
        $page->setStoreId(0);
        $page->load('home', 'identifier');

        $pageContent = $this->getHomepageContent();

        try {
            if ($page->getId()) {
                $page->setTitle('Home Page');
                $page->setIdentifier('home');
                $page->setContent($pageContent);
                $page->setIsActive(true);
                $page->setPageLayout('1column');
                $page->setStores([0]);
            } else {
                $page->setData([
                    'title' => 'Home Page',
                    'identifier' => 'home',
                    'content' => $pageContent,
                    'is_active' => 1,
                    'page_layout' => '1column'
                ]);
                $page->setStores([0]);
            }

            $page->save();
            $output->writeln(' - Página inicial criada/atualizada');
        } catch (\Throwable $exception) {
            $output->writeln(sprintf('<error>   ✗ Erro na página inicial: %s</error>', $exception->getMessage()));
        }
    }

    private function configureHomepage(OutputInterface $output): void
    {
        try {
            $this->configWriter->save('web/default/cms_home_page', 'home');
            $output->writeln(' - Homepage padrão configurada');
        } catch (\Throwable $exception) {
            $output->writeln(sprintf('<error>   ✗ Erro ao configurar homepage: %s</error>', $exception->getMessage()));
        }
    }

    private function createCategories(OutputInterface $output): void
    {
        $rootCategoryId = (int)$this->storeManager->getStore()->getRootCategoryId();

        foreach ($this->getCategoryDefinitions() as $categoryData) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->setStoreId(0);
            $collection->addAttributeToFilter('url_key', $categoryData['url_key']);
            $existingCategory = $collection->getFirstItem();

            if ($existingCategory && $existingCategory->getId()) {
                $output->writeln(sprintf(' - Categoria já existe: %s', $categoryData['name']));
                continue;
            }

            try {
                $category = $this->categoryFactory->create();
                $category->setStoreId(0);
                $category->setName($categoryData['name']);
                $category->setUrlKey($categoryData['url_key']);
                $category->setIsActive(true);
                $category->setIncludeInMenu(true);
                $category->setParentId($rootCategoryId);
                $category->setAttributeSetId($category->getDefaultAttributeSetId());
                $category->setIsAnchor(true);

                $this->categoryRepository->save($category);
                $output->writeln(sprintf(' - Categoria criada: %s', $categoryData['name']));
            } catch (\Throwable $exception) {
                $output->writeln(sprintf('<error>   ✗ Erro ao criar categoria %s: %s</error>', $categoryData['name'], $exception->getMessage()));

            }
        }
    }

    private function applyThemeConfigurations(OutputInterface $output): void
    {
        foreach ($this->getThemeConfigurations() as $config) {
            try {
                $this->configWriter->save($config['path'], $config['value']);

                $output->writeln(sprintf(' - Configuração aplicada: %s', $config['path']));
            } catch (\Throwable $exception) {
                $output->writeln(sprintf('<error>   ✗ Erro ao salvar %s: %s</error>', $config['path'], $exception->getMessage()));
            }
        }
    }

    private function getBlockDefinitions(): array
    {
        return [
            [
                'identifier' => 'top-left-static',
                'title' => 'Barra superior - Endereço',
                'content' => $this->topLeftStaticContent()
            ],
            [
                'identifier' => 'head_contact',
                'title' => 'Head Contact',
                'content' => $this->headContactContent()
            ],
            [
                'identifier' => 'hotline_header',
                'title' => 'Hotline Header',
                'content' => $this->hotlineHeaderContent()
            ],
            [
                'identifier' => 'top-contact',
                'title' => 'Top Contact',
                'content' => $this->topContactContent()
            ],
            [
                'identifier' => 'footer_info',
                'title' => 'Footer - Informações',
                'content' => $this->footerInfoContent()
            ],
            [
                'identifier' => 'social_block',
                'title' => 'Redes Sociais',
                'content' => $this->socialBlockContent()
            ],
            [
                'identifier' => 'footer_menu',
                'title' => 'Footer - Menu',
                'content' => $this->footerMenuContent()
            ],
            [
                'identifier' => 'footer_static',
                'title' => 'Footer - Conteúdo principal',
                'content' => $this->footerStaticContent()
            ],
            [
                'identifier' => 'footer_payment',
                'title' => 'Footer - Pagamentos',
                'content' => $this->footerPaymentContent()
            ],
            [
                'identifier' => 'fixed_right',
                'title' => 'Atalhos Flutuantes',
                'content' => $this->fixedRightContent()
            ],
            [
                'identifier' => 'home_slider',
                'title' => 'Home - Slider Principal',
                'content' => $this->homeSliderContent()
            ],
            [
                'identifier' => 'home_fitment',
                'title' => 'Home - Busca por Aplicação',
                'content' => $this->homeFitmentContent()
            ],
            [
                'identifier' => 'home_featured',
                'title' => 'Home - Produtos em Destaque',
                'content' => $this->homeFeaturedContent()
            ],
            [
                'identifier' => 'home_new_products',
                'title' => 'Home - Novos Produtos',
                'content' => $this->homeNewProductsContent()
            ],
            [
                'identifier' => 'home_banner_promo',
                'title' => 'Home - Banner Promocional',
                'content' => $this->homeBannerPromoContent()
            ],
            [
                'identifier' => 'top_slideshow_home1',
                'title' => 'Home 1 - Slider + Banners',
                'content' => $this->homeTopSlideshowContent()
            ],
            [
                'identifier' => 'list_ads1',
                'title' => 'Home 1 - Banners Laterais',
                'content' => $this->homeListAdsContent()
            ],
            [
                'identifier' => 'block_top',
                'title' => 'Home - Benefícios superiores',
                'content' => $this->homeBenefitsContent()
            ],
            [
                'identifier' => 'category1_home1',
                'title' => 'Home 1 - Categorias destaque 1',
                'content' => $this->homeCategory1Content()
            ],
            [
                'identifier' => 'category2_home1',
                'title' => 'Home 1 - Categorias destaque 2',
                'content' => $this->homeCategory2Content()
            ],
            [
                'identifier' => 'featured_categories',
                'title' => 'Home 1 - Compre por categoria',
                'content' => $this->homeFeaturedCategoriesContent()
            ],
            [
                'identifier' => 'home1_product_thumb',
                'title' => 'Home 1 - Produtos com imagem',
                'content' => $this->homeProductThumbContent()
            ]
        ];
    }

    private function getCategoryDefinitions(): array
    {
        return [
            ['name' => 'Eletrônicos', 'url_key' => 'eletronicos'],
            ['name' => 'Moda', 'url_key' => 'moda'],
            ['name' => 'Casa e Decoração', 'url_key' => 'casa-decoracao'],
            ['name' => 'Esportes', 'url_key' => 'esportes']
        ];
    }

    private function getThemeConfigurations(): array
    {
        return [
            // Header layout & visibility (added for idempotence of Ayo header preset)
            ['path' => 'themeoption/header/header_type', 'value' => '5'],
            ['path' => 'themeoption/header/show_hotline', 'value' => '1'],
            ['path' => 'themeoption/header/show_search', 'value' => '1'],
            ['path' => 'themeoption/header/search_enable', 'value' => '1'],
            ['path' => 'themeoption/header/show_account', 'value' => '1'],
            ['path' => 'themeoption/header/show_minicart', 'value' => '1'],
            ['path' => 'themeoption/header/show_wishlist', 'value' => '1'],
            ['path' => 'themeoption/header/show_compare', 'value' => '0'],
            ['path' => 'themeoption/general/layout', 'value' => 'full_width'],
            ['path' => 'themeoption/header/sticky_enable', 'value' => '1'],
            ['path' => 'themeoption/header/sticky_select_bg_color', 'value' => 'custom'],
            ['path' => 'themeoption/header/sticky_bg_color_custom', 'value' => '#ffffff'],
            ['path' => 'themeoption/footer/footer_menu_mobile', 'value' => '1'],
            ['path' => 'themeoption/fake_order/enable_f_o', 'value' => '0'],
            ['path' => 'themeoption/newsletter/enable', 'value' => '1'],
            ['path' => 'themeoption/newsletter/content', 'value' => $this->newsletterPopupContent()],
            ['path' => 'themeoption/newsletter/width', 'value' => '520'],
            ['path' => 'themeoption/newsletter/height', 'value' => '420'],
            ['path' => 'themeoption/newsletter/bg_color', 'value' => '#ffffff'],
            ['path' => 'themeoption/newsletter/bg_custom_style', 'value' => 'text-align:center;padding:30px 20px;'],
            ['path' => 'producttab/new_status/enabled', 'value' => '1'],
            ['path' => 'producttab/new_status/items', 'value' => '5'],
            ['path' => 'producttab/new_status/row', 'value' => '1'],
            ['path' => 'producttab/new_status/speed', 'value' => '400'],
            ['path' => 'producttab/new_status/qty', 'value' => '20'],
            ['path' => 'producttab/new_status/addtocart', 'value' => '1'],
            ['path' => 'producttab/new_status/wishlist', 'value' => '1'],
            ['path' => 'producttab/new_status/compare', 'value' => '0'],
            ['path' => 'producttab/new_status/navigation', 'value' => '1'],
            ['path' => 'producttab/new_status/pagination', 'value' => '0'],
            ['path' => 'producttab/new_status/auto', 'value' => '1'],
            ['path' => 'producttab/new_status/shownew', 'value' => '1'],
            ['path' => 'producttab/new_status/newname', 'value' => 'Lançamentos'],
            ['path' => 'producttab/new_status/showbestseller', 'value' => '1'],
            ['path' => 'producttab/new_status/bestsellername', 'value' => 'Mais vendidos'],
            ['path' => 'producttab/new_status/showfeature', 'value' => '1'],
            ['path' => 'producttab/new_status/featurename', 'value' => 'Destaques'],
            ['path' => 'producttab/new_status/showonsale', 'value' => '1'],
            ['path' => 'producttab/new_status/onsalename', 'value' => 'Promoções'],
            ['path' => 'producttab/new_status/showrandom', 'value' => '0'],
            ['path' => 'producttab/new_status/randomname', 'value' => 'Descubra também'],
            ['path' => 'rokanthemes_custommenu/general/enable', 'value' => '1'],
            ['path' => 'rokanthemes_quickview/general/enable', 'value' => '1'],
            ['path' => 'rokanthemes_ajaxsuite/general/ajaxcart_enable', 'value' => '1'],
            ['path' => 'rokanthemes_ajaxsuite/general/ajaxcompare_enable', 'value' => '1'],
            ['path' => 'rokanthemes_ajaxsuite/general/ajaxwishlist_enable', 'value' => '1']
            ,['path' => 'grupoawamotos_store/contact/whatsapp', 'value' => '5516997367588']
            ,['path' => 'grupoawamotos_store/contact/hours', 'value' => 'Seg a Sex: 8h às 18h | Sáb: 8h às 12h']
        ];
    }

    private function homeFitmentContent(): string
    {
        $enabled = (string)$this->scopeConfig->getValue('grupoawamotos_fitment/general/enable') === '1';
        $placeholder = (string)($this->scopeConfig->getValue('grupoawamotos_fitment/general/placeholder') ?: 'Ex.: Honda CG 160 2022');
        $hint = (string)($this->scopeConfig->getValue('grupoawamotos_fitment/general/hint') ?: 'Dica: use marca + modelo + ano para resultados mais precisos.');
        $suggestionsRaw = (string)($this->scopeConfig->getValue('grupoawamotos_fitment/general/suggestions') ?: 'Honda CG 160 2022;Yamaha Fazer 250 2023;Bauletos 34L;Manete esportivo;Retrovisor esportivo');
        // Normaliza lista: separa por ; ou quebra de linha
        $suggestionsArray = array_filter(array_map('trim', preg_split('/[;\n\r]+/', $suggestionsRaw)));
        if (count($suggestionsArray) > 50) {
            $suggestionsArray = array_slice($suggestionsArray, 0, 50);
        }

        if (!$enabled) {
            return <<<HTML
<section class="ayo-home5-fitment ayo-home5-fitment--disabled" role="note" aria-label="Busca por aplicação desativada">
    <div class="ayo-home5-fitment__box">
        <p class="ayo-home5-fitment__disabled-msg">Busca por aplicação temporariamente indisponível. Use a busca geral abaixo.</p>
        <form class="ayo-home5-fitment__fallback-form" action="{{store url='catalogsearch/result'}}" method="get">
            <label for="q-fallback" class="sr-only">Buscar produtos</label>
            <input id="q-fallback" type="text" name="q" placeholder="Ex.: retrovisor honda" required />
            <button class="action primary" type="submit">Buscar</button>
        </form>
    </div>
    <style>
        .ayo-home5-fitment__disabled-msg{margin:0 0 12px;font-weight:600;font-size:14px}
        .ayo-home5-fitment__fallback-form{display:flex;gap:12px}
        .ayo-home5-fitment__fallback-form input{flex:1 1 auto;height:46px;padding:0 14px;border:1px solid #d0d0d0;border-radius:12px;transition:border-color .2s ease, box-shadow .2s ease}
        .ayo-home5-fitment__fallback-form input:focus{outline:none;border-color:#b73337;box-shadow:0 0 0 3px rgba(183,51,55,.16)}
        .ayo-home5-fitment__fallback-form button{height:46px;padding:0 20px;border-radius:12px;font-weight:600;background:#b73337;border:0;transition:background .2s ease, transform .15s ease}
        .ayo-home5-fitment__fallback-form button:hover{background:#8e2629;transform:translateY(-1px)}
        @media(max-width:639px){.ayo-home5-fitment__fallback-form{flex-direction:column}.ayo-home5-fitment__fallback-form button{width:100%}}
    </style>
</section>
HTML;
        }

        $placeholderEsc = htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hintEsc = htmlspecialchars($hint, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $datalistOptions = '';
        foreach ($suggestionsArray as $sug) {
            $safe = htmlspecialchars($sug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $datalistOptions .= "<option value=\"{$safe}\"></option>";
        }

        return <<<HTML
<section class="ayo-home5-fitment" role="search" aria-label="Busca por aplicação de peças">
    <div class="ayo-home5-fitment__box">
        <header class="ayo-home5-fitment__intro">
            <p>Busque por <strong>marca</strong>, <strong>modelo</strong> e <strong>ano</strong> para encontrar peças compatíveis.</p>
        </header>
        <form class="ayo-home5-fitment__form" action="{{store url='catalogsearch/result'}}" method="get" novalidate>
            <div class="ayo-home5-fitment__fields">
                <label for="fitment-query" class="sr-only">Digite marca, modelo e ano</label>
                <input id="fitment-query" list="fitment-suggestions" type="text" name="q" placeholder="{$placeholderEsc}" aria-describedby="fitment-hint" autocomplete="off" required />
                <datalist id="fitment-suggestions">{$datalistOptions}</datalist>
                <button class="action primary ayo-home5-fitment__submit" type="submit" aria-label="Executar busca por aplicação">Buscar</button>
            </div>
            <small id="fitment-hint" class="ayo-home5-fitment__hint">{$hintEsc}</small>
        </form>
        <div class="ayo-home5-fitment__suggestion" aria-live="polite" aria-atomic="true"></div>
    </div>
    <style>
        .ayo-home5-fitment__box{background:#fff;border-radius:24px;padding:24px;box-shadow:0 16px 44px rgba(15,31,53,.12);position:relative;border:1px solid rgba(183,51,55,.08)}
        .ayo-home5-fitment__fields{display:flex;gap:12px;align-items:stretch}
        .ayo-home5-fitment__fields input{flex:1 1 auto;height:48px;padding:0 16px;border:1px solid #d0d0d0;border-radius:14px;font-size:15px;transition:border-color .2s ease, box-shadow .2s ease}
        .ayo-home5-fitment__fields input:focus{outline:none;border-color:#b73337;box-shadow:0 0 0 3px rgba(183,51,55,.18)}
        .ayo-home5-fitment__submit{height:48px;padding:0 22px;border-radius:14px;font-weight:600;background:#b73337;border:0;transition:background .2s ease, transform .15s ease, box-shadow .15s ease}
        .ayo-home5-fitment__submit:hover{background:#8e2629;transform:translateY(-1px);box-shadow:0 10px 24px rgba(183,51,55,.24)}
        .ayo-home5-fitment__hint{display:block;margin-top:8px;opacity:.78;font-size:12px;color:#51607c}
        .ayo-home5-fitment__suggestion{margin-top:10px;font-size:13px;color:#2c2c2c;min-height:18px}
        .sr-only{position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0 0 0 0);border:0}
        @media (max-width:639px){.ayo-home5-fitment__fields{flex-direction:column}.ayo-home5-fitment__submit{width:100%}}
    </style>
    <script type="text/x-magento-init">
        {"#fitment-query": {"GrupoAwamotos_Fitment/js/hint": {"min": 3}}}
    </script>
</section>
HTML;
    }

    /**
     * Coleta e normaliza dados reais da loja vindos das configurações globais.
     */
    private function getStoreInfo(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache; // cache leve em memória por execução
        }

        $name         = trim((string)$this->scopeConfig->getValue('general/store_information/name'));
        $phoneRaw     = trim((string)$this->scopeConfig->getValue('general/store_information/phone'));
        $street1      = trim((string)$this->scopeConfig->getValue('general/store_information/street_line1'));
        $street2      = trim((string)$this->scopeConfig->getValue('general/store_information/street_line2'));
        $postcode     = trim((string)$this->scopeConfig->getValue('general/store_information/postcode'));
        $city         = trim((string)$this->scopeConfig->getValue('general/store_information/city'));
        $region       = trim((string)$this->scopeConfig->getValue('general/store_information/region_id'));
        $supportEmail = trim((string)$this->scopeConfig->getValue('trans_email/ident_support/email'));
        $supportName  = trim((string)$this->scopeConfig->getValue('trans_email/ident_support/name'));
        $whatsRaw     = trim((string)$this->scopeConfig->getValue('grupoawamotos_store/contact/whatsapp'));
        $hoursRaw     = trim((string)$this->scopeConfig->getValue('grupoawamotos_store/contact/hours'));

        // Fallbacks
        if ($phoneRaw === '') { $phoneRaw = '(16) 3301-1890'; }
        if ($whatsRaw === '') { $whatsRaw = '5516992451890'; }
        if ($hoursRaw === '') { $hoursRaw = 'Seg a Sex: 8h às 18h | Sáb: 8h às 12h'; }

        // Normaliza telefone para exibição e para link (somente dígitos)
        $digits = preg_replace('/\D+/', '', $phoneRaw);
        $formatted = $phoneRaw; // mantém formato se já estiver adequado
        if (strlen($digits) >= 10) {
            $ddd = substr($digits, 0, 2);
            $rest = substr($digits, 2);
            if (strlen($rest) === 8) { // formato clássico
                $formatted = sprintf('(%s) %s-%s', $ddd, substr($rest, 0, 4), substr($rest, 4));
            } elseif (strlen($rest) === 9) { // inclui dígito 9
                $formatted = sprintf('(%s) %s-%s', $ddd, substr($rest, 0, 5), substr($rest, 5));
            }
        }

        // Normaliza endereço (colapsa espaços e vírgulas extras)
        $addressParts = array_filter([$street1, $street2, $city, $region]);
        $address = preg_replace('/\s{2,}/', ' ', implode(', ', $addressParts));
        $address = trim(preg_replace('/,+/', ',', $address), ' ,');
        if ($postcode) {
            $address .= ' - CEP ' . $postcode;
        }
        if ($address === '') {
            $address = 'R. Lavineo de Arruda Falcão, 1272 - Jardim Cruzeiro do Sul, Araraquara/SP - CEP 14808-390';
        }

        $cache = [
            'name'        => $name !== '' ? $name : 'AWA Motos',
            'phone'       => $formatted,
            'phone_digits'=> $digits,
            'phone_whats' => '(16) 99736-7588',
            'whatsapp'    => $whatsRaw !== '' && $whatsRaw !== '5516992451890' ? $whatsRaw : '5516997367588',
            'email'       => $supportEmail !== '' ? $supportEmail : 'sac@awamotos.com.br',
            'email_name'  => $supportName !== '' ? $supportName : 'SAC AWA Motos',
            'address'     => $address,
            'city'        => $city !== '' ? $city : 'Araraquara',
            'cnpj'        => '06.093.812/0001-05',
            'hours'       => $hoursRaw,
        ];

        return $cache;
    }

    private function headContactContent(): string
    {
        $info = $this->getStoreInfo();
        $phone = htmlspecialchars($info['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars($info['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="head-contact" aria-label="Contato rápido">
    Atendimento: <a href="tel:$phoneDigits" class="head-contact__phone">$phone</a> • <a href="mailto:$email" class="head-contact__email">$email</a>
</div>
HTML;
    }

    private function topLeftStaticContent(): string
    {
        $info = $this->getStoreInfo();
        $address = htmlspecialchars($info['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hours = htmlspecialchars($info['hours'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="top-left-static" aria-label="Informações da loja">
    <span class="address">$address</span>
    <span class="separator" aria-hidden="true">•</span>
    <span class="hours">$hours</span>
    <span class="separator" aria-hidden="true">•</span>
    <a class="store-link" href="{{store url='contact'}}">Fale conosco</a>
</div>
HTML;
    }

    private function hotlineHeaderContent(): string
    {
        $info = $this->getStoreInfo();
        $phone = htmlspecialchars($info['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $whats = htmlspecialchars($info['whatsapp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hours = htmlspecialchars($info['hours'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars($info['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cnpj = htmlspecialchars($info['cnpj'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="hoteline_header" aria-label="Telefone principal, WhatsApp e horário comercial">
    <div class="image_hotline" aria-hidden="true"></div>
    <div class="wrap">
        <span class="hotline_label">Central:</span>
        <a href="tel:$phoneDigits" class="hotline_phone">$phone</a>
        <span class="separator" aria-hidden="true">•</span>
        <a href="https://wa.me/$whats?text=Ol%C3%A1%2C%20vim%20pelo%20site" target="_blank" rel="noopener" class="hotline_whats" aria-label="WhatsApp atendimento $whats">WhatsApp</a>
        <span class="separator" aria-hidden="true">•</span>
        <span class="hotline_hours" aria-label="Horário comercial">$hours</span>
        <span class="separator" aria-hidden="true">•</span>
        <a href="mailto:$email" class="hotline_email" aria-label="E-mail de suporte $email">$email</a>
        <span class="separator" aria-hidden="true">•</span>
        <span class="hotline_cnpj" aria-label="CNPJ">$cnpj</span>
    </div>
</div>
HTML;
    }

    private function topContactContent(): string
    {
        $info = $this->getStoreInfo();
        $phone = htmlspecialchars($info['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars($info['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $whatsapp = htmlspecialchars($info['whatsapp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="top-contact" aria-label="Canais de atendimento">
    <div class="phone">
        <span class="label">Telefone</span>
        <a href="tel:$phoneDigits" class="contact-phone">$phone</a>
    </div>
    <div class="whatsapp">
        <span class="label">WhatsApp</span>
        <a href="https://wa.me/$whatsapp" target="_blank" rel="noopener" class="contact-whatsapp">$whatsapp</a>
    </div>
    <div class="email">
        <span class="label">E-mail</span>
        <a href="mailto:$email" class="contact-email">$email</a>
    </div>
</div>
HTML;
    }

    private function footerInfoContent(): string
    {
        $info = $this->getStoreInfo();
        $address = htmlspecialchars($info['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = htmlspecialchars($info['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="footer-info" aria-label="Informações institucionais">
    <h4>Sobre a Loja</h4>
    <p>Especialistas em peças e acessórios premium para motos.</p>
    <p><strong>Endereço:</strong> $address</p>
    <p><strong>Telefone:</strong> <a href="tel:$phoneDigits">$phone</a></p>
</div>
HTML;
    }

    private function socialBlockContent(): string
    {
        $info = $this->getStoreInfo();
        $whats = htmlspecialchars($info['whatsapp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars($info['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $phone = htmlspecialchars($info['phone'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hours = htmlspecialchars($info['hours'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<nav class="social-links" aria-label="Redes sociais e canais oficiais">
    <ul class="social-links__list" role="list">
        <li class="social-links__item" role="listitem"><a href="https://www.facebook.com/awamotos" target="_blank" rel="noopener" class="facebook" aria-label="Facebook AWA Motos"><i class="fa fa-facebook" aria-hidden="true"></i> facebook.com/awamotos</a></li>
        <li class="social-links__item" role="listitem"><a href="https://www.instagram.com/awamotos/" target="_blank" rel="noopener" class="instagram" aria-label="Instagram AWA Motos"><i class="fa fa-instagram" aria-hidden="true"></i> instagram.com/awamotos</a></li>
        <li class="social-links__item" role="listitem"><a href="https://www.youtube.com/@awamotos7661" target="_blank" rel="noopener" class="youtube" aria-label="YouTube AWA Motos"><i class="fa fa-youtube" aria-hidden="true"></i> youtube.com/@awamotos7661</a></li>
        <li class="social-links__item" role="listitem"><a href="https://wa.me/$whats?text=Ol%C3%A1%2C%20vim%20pelo%20site" target="_blank" rel="noopener" class="whatsapp" aria-label="WhatsApp AWA Motos $whats"><i class="fa fa-whatsapp" aria-hidden="true"></i> $whats</a></li>
        <li class="social-links__item" role="listitem"><a href="mailto:$email" class="email" aria-label="E-mail de suporte $email"><i class="fa fa-envelope" aria-hidden="true"></i> $email</a></li>
        <li class="social-links__item" role="listitem"><a href="tel:$phoneDigits" class="phone" aria-label="Telefone comercial $phone"><i class="fa fa-phone" aria-hidden="true"></i> $phone</a></li>
        <li class="social-links__item" role="listitem"><span class="hours" aria-label="Horário comercial">$hours</span></li>
    </ul>
</nav>
<style>
    .social-links__list{margin:0;padding:0;display:flex;flex-wrap:wrap;gap:10px}
    .social-links__item{list-style:none;font-size:13px}
    .social-links__item a{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;background:#fff;border-radius:8px;text-decoration:none;color:#333;box-shadow:0 2px 8px rgba(0,0,0,.08);transition:background .2s ease,transform .2s ease}
    .social-links__item a:focus-visible{outline:2px solid #b73337;outline-offset:2px}
    .social-links__item a:hover{background:#b73337;color:#fff;transform:translateY(-2px);box-shadow:0 6px 18px rgba(183,51,55,.25)}
    .social-links__item .hours{display:inline-block;padding:6px 10px;border-radius:8px;background:#f5f5f5;border:1px solid rgba(183,51,55,.12)}
    @media(max-width:639px){.social-links__list{flex-direction:column}}
    @media (prefers-reduced-motion: reduce){.social-links__item a{transition:none}}
</style>
HTML;
    }

    private function footerMenuContent(): string
    {
        return <<<HTML
<div class="footer-menu">
    <h4>Links Úteis</h4>
    <ul>
        <li><a href="{{store url="about-us"}}">Sobre Nós</a></li>
        <li><a href="{{store url="customer/account"}}">Minha Conta</a></li>
        <li><a href="{{store url="contact"}}">Contato</a></li>
    </ul>
</div>
HTML;
    }

    private function footerStaticContent(): string
    {
        return <<<HTML
<div class="velaNewsletterFooter">
    <div class="velaNewsletterInner clearfix">
        <h4 class="velaFooterTitle">Assine e receba novidades</h4>
        <div class="velaContent">
            <div class="newsletterDescription">
                Receba lançamentos, ofertas exclusivas e conteúdos técnicos sobre performance para motos e scooters.
            </div>
            {{block class="Magento\Newsletter\Block\Subscribe" template="subscribe.phtml"}}
        </div>
    </div>
</div>
<div class="container">
    <div class="rowFlex rowFlexMargin">
        <div class="col-xs-12 col-sm-12 col-md-4">
            <div class="vela-contactinfo velaBlock">
                <div class="vela-content">
                    <div class="contacinfo-logo clearfix">
                        <div class="velaFooterLogo"><a href="{{store url=''}}" title="AWA Motos">AWA Motos</a></div>
                    </div>
                    <div class="intro-footer d-flex">
                        Especialistas em peças e acessórios para motos. +18 linhas de produtos com entrega para todo Brasil.
                    </div>
                    <div class="contacinfo-phone contactinfo-item clearfix">
                        <div class="d-flex">
                            <div class="image_hotline"></div>
                            <div class="wrap"><label>Telefone:</label><a href="tel:551633011890">(16) 3301-1890</a></div>
                        </div>
                    </div>
                    <div class="contacinfo-phone contactinfo-item clearfix">
                        <div class="d-flex">
                            <div class="image_hotline"></div>
                            <div class="wrap"><label>WhatsApp:</label><a href="https://wa.me/5516997367588" target="_blank" rel="noopener">(16) 99736-7588</a></div>
                        </div>
                    </div>
                    <div class="contacinfo-address contactinfo-item d-flex"><label>Endereço:</label>R. Lavineo de Arruda Falcão, 1272 - Jardim Cruzeiro do Sul, Araraquara/SP - CEP 14808-390</div>
                    <div class="contacinfo-cnpj contactinfo-item d-flex"><label>CNPJ:</label>06.093.812/0001-05</div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4">
            <div class="rowFlex rowFlexMargin">
                <div class="col-xs-12 col-sm-6">
                    <div class="velaFooterMenu velaBlock">
                        <h4 class="velaFooterTitle">Institucional</h4>
                        <div class="velaContent">
                            <ul class="velaFooterLinks list-unstyled">
                                <li><a href="{{store url='about-us'}}">Sobre nós</a></li>
                                <li><a href="https://drive.google.com/drive/folders/1Wj0vtveapWFx5Eu7mcYAVXb5VUY7OnRi" target="_blank" rel="noopener">Catálogo de Produtos</a></li>
                                <li><a href="{{store url='contact'}}">Contato</a></li>
                                <li><a href="{{store url='privacy-policy-cookie-restriction-mode'}}">Política de Privacidade</a></li>
                                <li><a href="{{store url='sales/guest/form'}}">Rastrear pedido</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="velaFooterMenu velaBlock">
                        <h4 class="velaFooterTitle">Ajuda rápida</h4>
                        <div class="velaContent">
                            <ul class="velaFooterLinks list-unstyled">
                                <li><a href="{{store url='customer-service'}}">Atendimento</a></li>
                                <li><a href="{{store url='faq'}}">FAQ</a></li>
                                <li><a href="{{store url='returns'}}">Trocas e devoluções</a></li>
                                <li><a href="{{store url='warranty'}}">Garantia</a></li>
                                <li><a href="{{store url='store-locator'}}">Lojas parceiras</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4">
            <div class="rowFlex rowFlexMargin">
                <div class="col-xs-12 col-sm-6">
                    <div class="velaFooterMenu velaBlock">
                        <h4 class="velaFooterTitle">Minha conta</h4>
                        <div class="velaContent">
                            <ul class="velaFooterLinks list-unstyled">
                                <li><a href="{{store url='wishlist'}}">Lista de desejos</a></li>
                                <li><a href="{{store url='checkout/cart'}}">Carrinho</a></li>
                                <li><a href="{{store url='customer/account/login'}}">Login</a></li>
                                <li><a href="{{store url='customer/account/create'}}">Criar conta</a></li>
                                <li><a href="{{store url='newsletter/manage'}}">Preferências de e-mail</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="velaFooterMenu velaBlock">
                        <h4 class="velaFooterTitle">Redes sociais</h4>
                        <div class="velaContent">
                            <ul class="velaFooterLinks list-unstyled">
                                <li><a href="https://www.facebook.com/awamotos" target="_blank" rel="noopener"><i class="fa fa-facebook"></i> Facebook</a></li>
                                <li><a href="https://www.instagram.com/awamotos/" target="_blank" rel="noopener"><i class="fa fa-instagram"></i> Instagram</a></li>
                                <li><a href="https://www.youtube.com/@awamotos7661" target="_blank" rel="noopener"><i class="fa fa-youtube"></i> YouTube</a></li>
                                <li><a href="https://wa.me/5516997367588?text=Ol%C3%A1%2C%20vim%20pelo%20site" target="_blank" rel="noopener"><i class="fa fa-whatsapp"></i> WhatsApp</a></li>
                                <li><a href="mailto:sac@awamotos.com.br"><i class="fa fa-envelope"></i> sac@awamotos.com.br</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function footerPaymentContent(): string
    {
        return <<<HTML
<div class="payment-method">
    <img alt="Pagamentos" src="{{media url='wysiwyg/payment/pagamentos.svg'}}" style="max-width: 100%; height:auto;" />
</div>
HTML;
    }

    private function newsletterPopupContent(): string
    {
        return <<<HTML
<div class="ayo-newsletter-popup">
    <h3>Receba novidades e ofertas</h3>
    <p>Assine e fique por dentro dos lançamentos e promoções.</p>
    {{block class="Magento\\Newsletter\\Block\\Subscribe" template="subscribe.phtml"}}
</div>
HTML;
    }

    private function homeSliderContent(): string
    {
        return <<<HTML
<div class="ayo-slider-real" role="region" aria-label="Destaques em banner">
    {{widget type="Rokanthemes\SlideBanner\Block\Slider" slider_identifier="homepageslider" template="slider.phtml"}}
</div>
<style>
    .ayo-slider-real .owl-carousel .owl-item img { border-radius: 24px; }
    .ayo-slider-real .owl-dots { margin-top: 16px; text-align: center; }
    .ayo-slider-real .owl-dot { display: inline-block; width: 14px; height: 14px; margin: 0 6px; border-radius: 50%; background: #d5d5d5; position:relative; }
    .ayo-slider-real .owl-dot:focus-visible { outline: 3px solid #b73337; outline-offset: 2px; }
    .ayo-slider-real .owl-dot.active { background: #b73337; }
    @media (prefers-reduced-motion: reduce){ .ayo-slider-real .owl-dot{ transition:none; } }
</style>
HTML;
    }

    private function homeFeaturedContent(): string
    {
        return <<<HTML
<div class="ayo-home5-product-grid ayo-home5-product-grid--carousel" aria-label="Produtos em Destaque">
    {{widget type="Rokanthemes\\Featuredpro\\Block\\Widget\\Featuredpro"
        template="widget/featuredpro_list.phtml"
        limit="12"
        row="1"
        navigation="1"
        pagination="0"}}
</div>
HTML;
    }

    private function homeNewProductsContent(): string
    {
        return <<<HTML
<div class="ayo-home5-product-grid ayo-home5-product-grid--carousel" aria-label="Novos Produtos">
    {{widget type="Rokanthemes\\Newproduct\\Block\\Widget\\Newproduct"
        template="widget/newproduct_list.phtml"
        limit="12"
        row="1"
        navigation="1"
        pagination="0"}}
</div>
HTML;
    }

    private function homeBannerPromoContent(): string
    {
        return <<<HTML
<section class="ayo-home5-promo" role="region" aria-label="Banner promocional linha esportiva">
    <div class="ayo-home5-promo__inner">
        <span class="ayo-home5-promo__badge" aria-hidden="true">Linha esportiva</span>
        <h2 class="ayo-home5-promo__title">Equipe-se para qualquer pista</h2>
        <p class="ayo-home5-promo__desc">Guidões, manetes, pedaleiras e retrovisores esportivos com condições especiais para quem vive a estrada.</p>
        <a class="action primary ayo-home5-promo__cta" href="{{store url='linha-esportiva'}}" aria-label="Ver ofertas da linha esportiva">Ver ofertas</a>
    </div>
    <div class="ayo-home5-promo__image">
        <img src="{{media url='wysiwyg/banners/promo-banner.svg'}}" alt="Equipamentos e acessórios de alta performance" width="260" height="260" loading="lazy" decoding="async" fetchpriority="low" />
    </div>
</section>
HTML;
    }

    private function homeTopSlideshowContent(): string
    {
        return <<<HTML
<section class="ayo-home5-hero-layout" aria-label="Destaques principais da loja">
    <div class="ayo-home5-hero-layout__main" role="region" aria-label="Slider principal">
        {{widget type="Rokanthemes\SlideBanner\Block\Slider" slider_identifier="homepageslider" template="slider.phtml"}}
    </div>
    <nav class="ayo-home5-hero-layout__side" aria-label="Atalhos de linhas de motos">
        <ul class="ayo-hero-side-list" role="list">
            <li class="ayo-hero-side-item" role="listitem">
                <a class="ayo-home5-hero-card ayo-home5-hero-card--primary" href="{{store url='linha-honda'}}" aria-label="Ver peças da linha Honda">
                    <img src="{{media url='wysiwyg/home/side-honda.svg'}}" alt="Linha Honda" width="260" height="260" loading="lazy" decoding="async" fetchpriority="low" />
                    <span class="ayo-home5-hero-card__content">
                        <span class="ayo-home5-hero-card__eyebrow">Linha Original</span>
                        <strong class="ayo-home5-hero-card__title">Honda</strong>
                        <span class="ayo-home5-hero-card__cta" aria-hidden="true">Ver peças →</span>
                    </span>
                </a>
            </li>
            <li class="ayo-hero-side-item" role="listitem">
                <a class="ayo-home5-hero-card ayo-home5-hero-card--secondary" href="{{store url='linha-yamaha'}}" aria-label="Ver peças da linha Yamaha">
                    <img src="{{media url='wysiwyg/home/side-yamaha.svg'}}" alt="Linha Yamaha" width="260" height="260" loading="lazy" decoding="async" fetchpriority="low" />
                    <span class="ayo-home5-hero-card__content">
                        <span class="ayo-home5-hero-card__eyebrow">Linha Completa</span>
                        <strong class="ayo-home5-hero-card__title">Yamaha</strong>
                        <span class="ayo-home5-hero-card__cta" aria-hidden="true">Ver peças →</span>
                    </span>
                </a>
            </li>
        </ul>
    </nav>
</section>
<style>
    .ayo-hero-side-list{margin:0;padding:0;display:flex;flex-direction:column;gap:18px}
    .ayo-hero-side-item{list-style:none}
    .ayo-home5-hero-card{outline:none}
    .ayo-home5-hero-card:focus-visible{outline:3px solid #b73337;outline-offset:4px}
    @media (max-width:991px){.ayo-hero-side-list{flex-direction:row}}
    @media (max-width:639px){.ayo-hero-side-list{flex-direction:column}}
</style>
HTML;
    }

    private function homeListAdsContent(): string
    {
        return <<<HTML
<div class="ayo-home5-hero-card-stack">
    <a class="ayo-home5-hero-card ayo-home5-hero-card--primary" href="{{store url='linha-esportiva'}}" title="Linha Esportiva">
        <div class="ayo-hero-card-bg" style="background: linear-gradient(135deg, #b73337 0%, #ff6f00 100%);"></div>
        <span class="ayo-home5-hero-card__content">
            <span class="ayo-home5-hero-card__eyebrow">Alta performance</span>
            <strong class="ayo-home5-hero-card__title">Linha Esportiva</strong>
            <span class="ayo-home5-hero-card__cta">Ver coleção →</span>
        </span>
    </a>
    <a class="ayo-home5-hero-card ayo-home5-hero-card--secondary" href="{{store url='manetes'}}" title="Manetes">
        <div class="ayo-hero-card-bg" style="background: linear-gradient(135deg, #0f1f35 0%, #394f76 100%);"></div>
        <span class="ayo-home5-hero-card__content">
            <span class="ayo-home5-hero-card__eyebrow">Conforto e controle</span>
            <strong class="ayo-home5-hero-card__title">Manetes</strong>
            <span class="ayo-home5-hero-card__cta">Conferir →</span>
        </span>
    </a>
</div>
<style>
    .ayo-hero-card-bg { position: absolute; inset: 0; border-radius: 24px; }
    .ayo-home5-hero-card-stack .ayo-home5-hero-card { position: relative; min-height: 180px; }
    .ayo-home5-hero-card-stack .ayo-home5-hero-card::after { display: none; }
</style>
HTML;
    }

    private function homeBenefitsContent(): string
    {
        return <<<HTML
<section class="velaServicesInner velaServicesInner--home5" role="list" aria-label="Benefícios da loja">
    <div class="velaContent">
        <ul class="rowFlex rowFlexMargin flexJustifyCenter velaBenefitsList" role="list">
            <li class="col-xs-6 col-sm-3 col-2 velaBenefit" role="listitem">
                <div class="boxService d-flex flexJustifyCenter">
                    <div class="boxServiceImage boxServiceImage1" aria-hidden="true"></div>
                    <div class="boxServiceContent">
                        <h4 class="boxServiceTitle">Atendemos Todo o Brasil!</h4>
                        <p class="boxServiceDesc">Envio via Correios e transportadoras para todo território nacional</p>
                    </div>
                </div>
            </li>
            <li class="col-xs-6 col-sm-3 col-2 velaBenefit" role="listitem">
                <div class="boxService d-flex flexJustifyCenter">
                    <div class="boxServiceImage boxServiceImage2" aria-hidden="true"></div>
                    <div class="boxServiceContent">
                        <h4 class="boxServiceTitle">+18 Linhas de Produtos</h4>
                        <p class="boxServiceDesc">Retrovisores, Bauletos, Bagageiros, Guidões, Manoplas e muito mais</p>
                    </div>
                </div>
            </li>
            <li class="col-xs-6 col-sm-3 col-2 velaBenefit" role="listitem">
                <div class="boxService d-flex flexJustifyCenter">
                    <div class="boxServiceImage boxServiceImage3" aria-hidden="true"></div>
                    <div class="boxServiceContent">
                        <h4 class="boxServiceTitle">Entrega mais Rápida!</h4>
                        <p class="boxServiceDesc">Agilidade no despacho e rastreamento em tempo real</p>
                    </div>
                </div>
            </li>
            <li class="col-xs-6 col-sm-3 col-2 velaBenefit" role="listitem">
                <div class="boxService d-flex flexJustifyCenter">
                    <div class="boxServiceImage boxServiceImage4" aria-hidden="true"></div>
                    <div class="boxServiceContent">
                        <h4 class="boxServiceTitle">Atendimento Especializado</h4>
                        <p class="boxServiceDesc"><a href="https://wa.me/5516997367588?text=Ol%C3%A1" target="_blank" rel="noopener">WhatsApp</a> ou <a href="tel:551633011890">(16) 3301-1890</a></p>
                    </div>
                </div>
            </li>
            <li class="col-xs-6 col-sm-3 col-2 velaBenefit" role="listitem">
                <div class="boxService d-flex flexJustifyCenter">
                    <div class="boxServiceImage boxServiceImage5" aria-hidden="true"></div>
                    <div class="boxServiceContent">
                        <h4 class="boxServiceTitle">Catálogo Completo</h4>
                        <p class="boxServiceDesc"><a href="https://drive.google.com/drive/folders/1Wj0vtveapWFx5Eu7mcYAVXb5VUY7OnRi" target="_blank" rel="noopener">Acesse nosso catálogo digital</a></p>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</section>
<style>
    .velaBenefitsList{margin:0;padding:0;}
    .velaBenefit{list-style:none;}
    .boxServiceTitle{font-size:15px;margin:0 0 4px;}
    .boxServiceDesc{font-size:13px;margin:0;opacity:.85;}
    .boxServiceDesc a{color:inherit;text-decoration:underline;}
    @media (prefers-reduced-motion: reduce){ .boxService, .velaBenefit{ transition:none; } }
</style>
HTML;
    }

    private function homeCategory1Content(): string
    {
        return <<<HTML
<div class="ayo-home5-product-grid">
    {{widget type="Rokanthemes\\Categorytab\\Block\\CateWidget"
        title="Retrovisores"
        color_box="red-box"
        identify="categorytab_retro"
        category_id="41"
        limit_qty="8"
        show_pager="0"
        slide_row="1"
        slide_limit="4"
        default="4"
        desktop="4"
        desktop_small="3"
        tablet="2"
        mobile="1"
        navigation="1"
        template="categorytab/grid.phtml"}}
</div>
HTML;
    }

    private function homeCategory2Content(): string
    {
        return <<<HTML
<div class="ayo-home5-product-grid">
    {{widget type="Rokanthemes\\Categorytab\\Block\\CateWidget"
        title="Bauletos"
        color_box="blue-box"
        identify="categorytab_bauletos"
        category_id="45,46,75,77"
        limit_qty="8"
        show_pager="0"
        slide_row="1"
        slide_limit="4"
        default="4"
        desktop="4"
        desktop_small="3"
        tablet="2"
        mobile="1"
        navigation="1"
        template="categorytab/grid.phtml"}}
</div>
HTML;
    }

    private function homeFeaturedCategoriesContent(): string
    {
        return <<<HTML
<nav class="ayo-home5-categories-grid" aria-label="Compre por categoria">
    <ul class="ayo-categories-row" role="list">
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='retrovisores'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">🔍</span>
                <h3 class="ayo-category-card__title">Retrovisores</h3>
                <span class="ayo-category-card__count">+150 produtos</span>
                <span class="sr-only">Ver categoria Retrovisores</span>
            </a>
        </li>
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='bauletos'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">📦</span>
                <h3 class="ayo-category-card__title">Bauletos</h3>
                <span class="ayo-category-card__count">29L, 34L, 41L</span>
                <span class="sr-only">Ver categoria Bauletos</span>
            </a>
        </li>
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='guidoes'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">🏍️</span>
                <h3 class="ayo-category-card__title">Guidões</h3>
                <span class="ayo-category-card__count">Esportivos e originais</span>
                <span class="sr-only">Ver categoria Guidões</span>
            </a>
        </li>
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='manoplas'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">✋</span>
                <h3 class="ayo-category-card__title">Manoplas</h3>
                <span class="ayo-category-card__count">Racing e conforto</span>
                <span class="sr-only">Ver categoria Manoplas</span>
            </a>
        </li>
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='bagageiros'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">🎒</span>
                <h3 class="ayo-category-card__title">Bagageiros</h3>
                <span class="ayo-category-card__count">Suportes universais</span>
                <span class="sr-only">Ver categoria Bagageiros</span>
            </a>
        </li>
        <li class="ayo-category-card-wrapper">
            <a class="ayo-category-card" href="{{store url='pedaleiras'}}">
                <span class="ayo-category-card__icon" aria-hidden="true">👟</span>
                <h3 class="ayo-category-card__title">Pedaleiras</h3>
                <span class="ayo-category-card__count">Alta performance</span>
                <span class="sr-only">Ver categoria Pedaleiras</span>
            </a>
        </li>
    </ul>
</nav>
<style>
    .ayo-home5-categories-grid { padding: 24px; }
    .ayo-categories-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; margin: 0; padding: 0; }
    .ayo-category-card-wrapper { list-style: none; }
    .ayo-category-card { position: relative; display: flex; flex-direction: column; align-items: center; padding: 28px 18px; background: #fff; border-radius: 20px; border: 1px solid rgba(0,0,0,0.04); text-decoration: none; color: var(--home-text, #1f1f1f); box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: transform 0.25s ease, box-shadow 0.25s ease, outline-color 0.25s ease, border-color 0.25s ease; }
    .ayo-category-card:focus-visible { outline: 3px solid var(--home-primary, #b73337); outline-offset: 3px; }
    .ayo-category-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); border-color: rgba(183,51,55,0.26); color: var(--home-primary, #b73337); }
    .ayo-category-card__icon { font-size: 36px; margin-bottom: 12px; }
    .ayo-category-card__title { margin: 0 0 6px; font-size: 17px; font-weight: 600; color: inherit; }
    .ayo-category-card__count { font-size: 13px; color: rgba(31,31,31,0.58); }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }
    @media (max-width: 639px) { .ayo-category-card { padding: 22px 16px; } }
    @media (prefers-reduced-motion: reduce) { .ayo-category-card { transition: none; } }
</style>
HTML;
    }

    private function homeProductThumbContent(): string
    {
        return <<<HTML
<div class="ayo-home5-product-grid">
    {{widget type="Rokanthemes\\Categorytab\\Block\\CateWidget"
        title=""
        color_box="orange-box"
        identify="categorytab_community"
        category_id="41,44,45,67,74,86"
        limit_qty="12"
        show_pager="0"
        slide_row="1"
        slide_limit="6"
        default="6"
        desktop="5"
        desktop_small="4"
        tablet="3"
        mobile="2"
        navigation="1"
        template="categorytab/grid.phtml"}}
</div>
HTML;
    }

    private function getHomepageContent(): string
    {
        return <<<'HTML'
<div class="ayo-home5-wrapper">
    <section class="ayo-home5-section container ayo-home5-section--hero">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Experiência premium</span>
            <h2>Novidades em duas rodas</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="top_slideshow_home1"}}
    </section>

    <section class="ayo-home5-section container ayo-home5-section--fitment">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Encontre a peça certa</span>
            <h2>Busca por aplicação</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="home_fitment"}}
    </section>

    <section class="ayo-home5-section container">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Benefícios exclusivos</span>
            <h2>Por que comprar com a Awamoto's</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="block_top"}}
    </section>

    <section class="ayo-home5-section container ayo-home5-section--categories">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Categorias em alta</span>
            <h2>Sua próxima aventura começa aqui</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        <div class="ayo-home5-category-rows">
            <div class="ayo-home5-category-column">
                {{block class="Magento\Cms\Block\Block" block_id="category1_home1"}}
            </div>
            <div class="ayo-home5-category-column">
                {{block class="Magento\Cms\Block\Block" block_id="category2_home1"}}
            </div>
        </div>
    </section>

    <section class="ayo-home5-section container">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Coleções</span>
            <h2>Compre por categoria</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="featured_categories"}}
    </section>

    <section class="ayo-home5-section container">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Destaques</span>
            <h2>Escolhas do time</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="home_featured"}}
    </section>

    <section class="ayo-home5-section container ayo-home5-section--promo">
        {{block class="Magento\Cms\Block\Block" block_id="home_banner_promo"}}
    </section>

    <section class="ayo-home5-section container">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Lançamentos</span>
            <h2>Chegou na loja</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="home_new_products"}}
    </section>

    <section class="ayo-home5-section container">
        <header class="ayo-home5-heading">
            <span class="ayo-home5-label">Mais buscados</span>
            <h2>Seleções da comunidade</h2>
            <span class="ayo-home5-divider"></span>
        </header>
        {{block class="Magento\Cms\Block\Block" block_id="home1_product_thumb"}}
    </section>
</div>
HTML;
    }

    

    private function ensurePlaceholderBanners(OutputInterface $output): void
    {
        try {
            $mediaDir = rtrim($this->directoryList->getPath(DirectoryList::MEDIA), '/');
            $sliderDir = $mediaDir . '/slidebanner';
            if (!is_dir($sliderDir)) {
                @mkdir($sliderDir, 0755, true);
            }

            for ($i = 1; $i <= 3; $i++) {
                $file = $sliderDir . "/banner{$i}.svg";
                if (!file_exists($file)) {
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="520">'
                        . '<rect width="100%" height="100%" fill="#f2f2f2"/>'
                        . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="42" fill="#333">'
                        . "Banner {$i} Placeholder"
                        . '</text></svg>';
                    @file_put_contents($file, $svg);
                }
            }
            // pagamentos sprite simples
            $paymentDir = $mediaDir . '/wysiwyg/payment';
            if (!is_dir($paymentDir)) {
                @mkdir($paymentDir, 0755, true);
            }
            $paymentFile = $paymentDir . '/pagamentos.svg';
            if (!file_exists($paymentFile)) {
                $svgPay = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="60">'
                    . '<rect width="100%" height="100%" fill="#ffffff"/>'
                    . '<g font-family="sans-serif" font-size="20" font-weight="700" fill="#333">'
                    . '<text x="20" y="38">PIX</text>'
                    . '<text x="90" y="38">BOLETO</text>'
                    . '<text x="210" y="38">VISA</text>'
                    . '<text x="290" y="38">MASTERCARD</text>'
                    . '<text x="440" y="38">AMEX</text>'
                    . '</g></svg>';
                @file_put_contents($paymentFile, $svgPay);
            }

            $output->writeln(' - Placeholders de banners e pagamentos verificados/criados');
        } catch (\Throwable $e) {
            $output->writeln('<error>   ✗ Falha ao criar placeholders de banners: ' . $e->getMessage() . '</error>');
        }
    }

    private function fixedRightContent(): string
    {
        $info = $this->getStoreInfo();
        $phoneDigits = htmlspecialchars($info['phone_digits'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $email = htmlspecialchars($info['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $whatsapp = htmlspecialchars($info['whatsapp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="fixed-right-links" aria-label="Atalhos rápidos">
    <ul class="list-unstyled">
        <li><a class="fixed-call" href="tel:$phoneDigits" title="Ligar"><span>Ligação</span></a></li>
        <li><a class="fixed-whatsapp" href="https://wa.me/$whatsapp" target="_blank" rel="noopener" title="WhatsApp"><span>WhatsApp</span></a></li>
        <li><a class="fixed-email" href="mailto:$email" title="E-mail"><span>E-mail</span></a></li>
        <li><a class="fixed-top" href="#top" title="Ir ao topo"><span>Topo</span></a></li>
    </ul>
</div>
HTML;
    }

    private function seedSlider(OutputInterface $output): void
    {
        try {
            $identifier = 'homepageslider';
            $slider = $this->sliderFactory->create();
            // load by identifier (resource model supports non-numeric via slider_identifier)
            $slider->load($identifier);

            $wasExisting = (bool)$slider->getId();
            if (!$wasExisting) {
                $slider->setData([
                    'slider_identifier' => $identifier,
                    'slider_title' => 'Homepage Slider',
                    'slider_status' => 1,
                    'store_ids' => json_encode([0]),
                    'slider_setting' => null,
                    'slider_styles' => null,
                    'slider_script' => null,
                    'slider_template' => null,
                ]);
                $slider->save();
            }

            // Ensure we have three basic slides
            $sliderId = (int)$slider->getId();
            if ($sliderId <= 0) {
                // If still no ID, nothing to do
                return;
            }

            // Delete existing slides to refresh with real ones
            $existingSlides = $this->slideFactory->create()->getCollection();
            $existingSlides->addFieldToFilter('slider_id', $sliderId);
            foreach ($existingSlides as $existingSlide) {
                $existingSlide->delete();
            }

            // Slides reais com links para categorias existentes
            $slidesData = [
                [
                    'text' => 'Retrovisores Premium - A partir de R$ 49,90',
                    'image' => 'slidebanner/real/slide1.svg',
                    'link' => '{{store url="retrovisores"}}',
                ],
                [
                    'text' => 'Bauletos e Bagageiros - Frete Grátis',
                    'image' => 'slidebanner/real/slide2.svg',
                    'link' => '{{store url="bauletos"}}',
                ],
                [
                    'text' => 'Guidões e Manetes Esportivos - Até 40% OFF',
                    'image' => 'slidebanner/real/slide3.svg',
                    'link' => '{{store url="linha-esportiva"}}',
                ],
            ];

            foreach ($slidesData as $i => $slideData) {
                $slide = $this->slideFactory->create();
                $slide->setData([
                    'slider_id' => $sliderId,
                    'slide_type' => 1,
                    'slide_text' => $slideData['text'],
                    'slide_image' => $slideData['image'],
                    'slide_image_mobile' => $slideData['image'],
                    'slide_link' => $slideData['link'],
                    'slide_status' => 1,
                    'slide_position' => $i + 1,
                ]);
                $slide->save();
            }
            $output->writeln(sprintf(' - Slider %s %s com 3 slides reais', $identifier, $wasExisting ? 'atualizado' : 'criado'));
        } catch (\Throwable $e) {
            $output->writeln('<error>   ✗ Falha ao semear slider: ' . $e->getMessage() . '</error>');
        }
    }
}
