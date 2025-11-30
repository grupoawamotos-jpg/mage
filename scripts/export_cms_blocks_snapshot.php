#!/usr/bin/env php
<?php
/**
 * Gera snapshot JSON dos blocos CMS críticos com metadados para auditoria.
 * Uso:
 *   php scripts/export_cms_blocks_snapshot.php
 * Saída:
 *   relatorios/cms_blocks_snapshot_YYYY-MM-DD_HH-MM-SS.json
 */
declare(strict_types=1);

use Magento\Framework\App\Bootstrap;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();

/** @var BlockRepositoryInterface $blockRepo */
$blockRepo = $om->get(BlockRepositoryInterface::class);

$blockIds = [
    'head_contact',
    'footer_info',
    'footer_static',
    'fixed_right',
    'social_block',
    'hotline_header',
    'top-left-static',
    'top-contact'
];

$snapshot = [
    'generated_at' => date('c'),
    'blocks' => []
];

foreach ($blockIds as $id) {
    try {
        /** @var BlockInterface $block */
        $block = $blockRepo->getById($id);
        $content = (string)$block->getContent();
        $snapshot['blocks'][$id] = [
            'length' => strlen($content),
            'sha256' => hash('sha256', $content),
            'preview' => substr(preg_replace('/\s+/', ' ', strip_tags($content)), 0, 160),
        ];
    } catch (Throwable $e) {
        $snapshot['blocks'][$id] = [
            'error' => $e->getMessage()
        ];
    }
}

$dir = __DIR__ . '/../relatorios';
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
}
$file = $dir . '/cms_blocks_snapshot_' . date('Y-m-d_H-i-s') . '.json';
file_put_contents($file, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Snapshot salvo em: $file\n";
