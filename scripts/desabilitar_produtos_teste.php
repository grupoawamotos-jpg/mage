<?php
/**
 * Desabilita produtos de teste/demo que não têm imagens
 */

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

$productRepository = $obj->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

// SKUs para desabilitar
$skusParaDesabilitar = [
    'DEMO-NOTEBOOK-001',
    'DEMO-MOUSE-001',
    'DEMO-TECLADO-001',
    'CAM-001',
    'CAM-002',
    'CAL-001'
];

echo "🔧 Desabilitando produtos de teste/demo...\n\n";

$desabilitados = 0;
$erros = 0;

foreach ($skusParaDesabilitar as $sku) {
    try {
        $product = $productRepository->get($sku);
        if ($product->getStatus() == 1) {
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $productRepository->save($product);
            echo "✓ Desabilitado: {$sku} - {$product->getName()}\n";
            $desabilitados++;
        } else {
            echo "⊝ Já desabilitado: {$sku}\n";
        }
    } catch (\Exception $e) {
        echo "✗ Erro ao desabilitar {$sku}: {$e->getMessage()}\n";
        $erros++;
    }
}

echo "\n📊 Resumo:\n";
echo "   Desabilitados: {$desabilitados}\n";
echo "   Erros: {$erros}\n";
echo "\n✅ Concluído. Execute 'php bin/magento cache:flush' e 'php bin/magento indexer:reindex'.\n";
