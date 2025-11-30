<?php
/**
 * Verifica status da Home: produtos habilitados, imagens, e widgets
 */

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

$resource = $obj->get(\Magento\Framework\App\ResourceConnection::class);
$conn = $resource->getConnection();
$storeManager = $obj->get(\Magento\Store\Model\StoreManagerInterface::class);
$cmsBlockRepo = $obj->get(\Magento\Cms\Api\BlockRepositoryInterface::class);
$searchCriteriaBuilder = $obj->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);

echo "🏠 STATUS DA HOME - Grupo Awamotos\n";
echo str_repeat('=', 80) . "\n\n";

// 1. Produtos
echo "📦 PRODUTOS:\n";
$totalProdutos = $conn->fetchOne('SELECT COUNT(*) FROM catalog_product_entity');
$produtosHabilitados = $conn->fetchOne('
    SELECT COUNT(DISTINCT e.entity_id) 
    FROM catalog_product_entity e 
    JOIN catalog_product_entity_int i ON e.entity_id=i.entity_id 
    JOIN eav_attribute a ON i.attribute_id=a.attribute_id 
    WHERE a.attribute_code="status" AND i.value=1
');
$comImagem = $conn->fetchOne('
    SELECT COUNT(DISTINCT e.entity_id)
    FROM catalog_product_entity e
    JOIN catalog_product_entity_varchar v ON e.entity_id=v.entity_id
    JOIN eav_attribute a ON v.attribute_id=a.attribute_id
    WHERE a.attribute_code="image" AND v.value IS NOT NULL AND v.value != "no_selection" AND v.value != ""
');

$percentHabilitados = round(($produtosHabilitados / $totalProdutos) * 100, 2);
$percentComImagem = round(($comImagem / $produtosHabilitados) * 100, 2);

echo "   Total: {$totalProdutos}\n";
echo "   Habilitados: {$produtosHabilitados} ({$percentHabilitados}%)\n";
echo "   Com imagem: {$comImagem} ({$percentComImagem}%)\n";
echo "   Sem imagem: " . ($produtosHabilitados - $comImagem) . "\n\n";

// 2. Blocos CMS da Home
echo "📄 BLOCOS CMS:\n";
$blocosHome = [
    'home_slider' => 'Slider Principal',
    'home_fitment' => 'Busca por Compatibilidade',
    'home_featured' => 'Produtos em Destaque',
    'home_new_products' => 'Produtos Novos',
    'home_banner_promo' => 'Banner Promocional',
    'featured_categories' => 'Categorias em Destaque'
];

foreach ($blocosHome as $identifier => $nome) {
    try {
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('identifier', $identifier)
            ->create();
        $blocks = $cmsBlockRepo->getList($searchCriteria)->getItems();
        if (count($blocks) > 0) {
            $block = array_shift($blocks);
            $status = $block->isActive() ? '✓ Ativo' : '✗ Inativo';
            echo "   {$nome}: {$status}\n";
        } else {
            echo "   {$nome}: ⚠️  Não encontrado\n";
        }
    } catch (\Exception $e) {
        echo "   {$nome}: ✗ Erro\n";
    }
}

// 3. Página Home
echo "\n🏡 PÁGINA HOME:\n";
$homePageId = $conn->fetchOne('SELECT value FROM core_config_data WHERE path="web/default/cms_home_page"');
if ($homePageId) {
    try {
        $pageRepo = $obj->get(\Magento\Cms\Api\PageRepositoryInterface::class);
        $homePage = $pageRepo->getById($homePageId);
        echo "   Identifier: {$homePage->getIdentifier()}\n";
        echo "   Título: {$homePage->getTitle()}\n";
        echo "   Status: " . ($homePage->isActive() ? '✓ Ativa' : '✗ Inativa') . "\n";
    } catch (\Exception $e) {
        echo "   ⚠️  Erro ao carregar página: {$e->getMessage()}\n";
    }
} else {
    echo "   ⚠️  Nenhuma home page configurada\n";
}

// 4. Configurações do Tema
echo "\n🎨 CONFIGURAÇÕES DO TEMA:\n";
$themeConfigs = [
    'themeoption/header/header_type' => 'Tipo de Header',
    'themeoption/general/layout' => 'Layout Geral',
    'producttab/new_status/enabled' => 'Tab Produtos Novos',
    'rokanthemes_quickview/general/enable' => 'Quick View',
    'rokanthemes_ajaxsuite/general/ajaxcart_enable' => 'Ajax Cart'
];

foreach ($themeConfigs as $path => $label) {
    $value = $conn->fetchOne("SELECT value FROM core_config_data WHERE path=? LIMIT 1", [$path]);
    $status = $value ? "✓ {$value}" : '✗ Não configurado';
    echo "   {$label}: {$status}\n";
}

// 5. Static Content
echo "\n📦 STATIC CONTENT:\n";
$staticDirs = [
    'pub/static/frontend/Ayo',
    'pub/static/adminhtml',
    'var/view_preprocessed'
];

foreach ($staticDirs as $dir) {
    $fullPath = BP . '/' . $dir;
    if (is_dir($fullPath)) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $count = iterator_count($files);
        echo "   {$dir}: ✓ {$count} arquivos\n";
    } else {
        echo "   {$dir}: ⚠️  Diretório não existe\n";
    }
}

// 7. Modo de Deploy
echo "\n⚙️  AMBIENTE:\n";
$deployMode = $obj->get(\Magento\Framework\App\State::class)->getMode();
echo "   Modo: " . strtoupper($deployMode) . "\n";

// Resumo Final
echo "\n" . str_repeat('=', 80) . "\n";
echo "📊 RESUMO:\n";
$issues = [];

if ($percentComImagem < 95) {
    $issues[] = "Menos de 95% dos produtos têm imagens";
}

if ($deployMode !== 'production') {
    $issues[] = "Modo não está em production";
}

if (empty($issues)) {
    echo "   ✅ Tudo OK! Home configurada corretamente.\n";
} else {
    echo "   ⚠️  Pontos de atenção:\n";
    foreach ($issues as $issue) {
        echo "      - {$issue}\n";
    }
}

echo "\n💡 Próximo passo: Validar visualmente em https://grupoawamotos.com.br\n";
