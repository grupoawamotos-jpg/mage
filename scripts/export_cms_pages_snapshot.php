<?php
/**
 * Exporta snapshot das páginas CMS com identificador começando por 'homepage'.
 * Uso: php scripts/export_cms_pages_snapshot.php > relatorios/cms_homepages_snapshot.json
 */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

if (!defined('BP')) {
    define('BP', realpath(__DIR__ . '/..'));
}

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('adminhtml');
} catch (\Exception $e) {
    // Area já definida
}

/** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
$collection = $objectManager->create(\Magento\Cms\Model\ResourceModel\Page\Collection::class);
$collection->addFieldToFilter('identifier', ['like' => 'homepage%']);

$result = [];
foreach ($collection as $page) {
    $result[] = [
        'page_id' => (int)$page->getId(),
        'identifier' => $page->getIdentifier(),
        'title' => $page->getTitle(),
        'is_active' => (int)$page->isActive(),
        'content_length' => strlen((string)$page->getContent()),
        'creation_time' => $page->getCreationTime(),
        'update_time' => $page->getUpdateTime(),
        'layout_update_xml_length' => strlen((string)$page->getLayoutUpdateXml()),
    ];
}

echo json_encode([
    'generated_at' => date('c'),
    'count' => count($result),
    'pages' => $result
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
