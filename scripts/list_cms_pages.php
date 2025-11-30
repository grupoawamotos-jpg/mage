<?php
require __DIR__ . '/../app/bootstrap.php';
use Magento\Framework\App\Bootstrap;

try {
    $bootstrap = Bootstrap::create(BP, $_SERVER);
    $om = $bootstrap->getObjectManager();
    $state = $om->get(\Magento\Framework\App\State::class);
    try { $state->setAreaCode('adminhtml'); } catch (\Magento\Framework\Exception\LocalizedException $e) {}
    $res = $om->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
    $pages = $res->fetchAll('SELECT page_id, title, identifier, is_active FROM cms_page ORDER BY page_id DESC LIMIT 25');
    foreach ($pages as $p) {
        printf("%d | %s | %s | active=%s\n", $p['page_id'], $p['identifier'], $p['title'], $p['is_active']);
    }
    $homeId = $res->fetchOne("SELECT page_id FROM cms_page WHERE identifier='home' LIMIT 1");
    echo "home_page_id=" . ($homeId ?: 'none') . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}