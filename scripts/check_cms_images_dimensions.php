<?php
/**
 * Auditoria de CMS Blocks: imagens sem width/height.
 * Uso: php scripts/check_cms_images_dimensions.php
 */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();
$configState = $om->get('Magento\Framework\App\State');
try { $configState->setAreaCode('adminhtml'); } catch (\Exception $e) {}

$blockCollectionFactory = $om->get('Magento\Cms\Model\ResourceModel\Block\CollectionFactory');
$collection = $blockCollectionFactory->create();
$collection->addFieldToFilter('is_active', 1);

$patternImg = '/<img[^>]*>/i';
$patternWidth = '/width\s*=\s*"[0-9]+"/i';
$patternHeight = '/height\s*=\s*"[0-9]+"/i';

$report = [];
foreach ($collection as $block) {
    $content = $block->getContent();
    if (!preg_match($patternImg, $content)) {
        continue; // sem imagens
    }
    preg_match_all($patternImg, $content, $imgs);
    $missing = [];
    foreach ($imgs[0] as $tag) {
        $hasW = preg_match($patternWidth, $tag);
        $hasH = preg_match($patternHeight, $tag);
        if (!$hasW || !$hasH) {
            $missing[] = $tag;
        }
    }
    if ($missing) {
        $report[] = [
            'identifier' => $block->getIdentifier(),
            'title' => $block->getTitle(),
            'count' => count($missing),
            'examples' => array_slice($missing, 0, 3)
        ];
    }
}

if (!$report) {
    echo "✔ Todas as imagens em CMS Blocks possuem width e height.\n";
    exit(0);
}

echo "Imagens sem dimensões detectadas:\n";
foreach ($report as $row) {
    echo "- {$row['identifier']} ({$row['title']}): {$row['count']} img(s) sem width/height\n";
    foreach ($row['examples'] as $ex) {
        echo "    Ex: " . trim($ex) . "\n";
    }
}

echo "\nSugestão: padronizar adicionando width/height nas definições em StoreConfigurator ou editar bloco via admin.\n";
exit(1);
