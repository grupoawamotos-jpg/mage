<?php
declare(strict_types=1);

use Magento\Framework\App\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Gallery\Processor as GalleryProcessor;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Uploader as FileUploader;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();

/** @var State $state */
$state = $objectManager->get(State::class);
try { $state->setAreaCode('adminhtml'); } catch (LocalizedException $e) {}

/** @var ProductRepositoryInterface $productRepo */
$productRepo = $objectManager->get(ProductRepositoryInterface::class);
/** @var MediaConfig $mediaConfig */
$mediaConfig = $objectManager->get(MediaConfig::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var GalleryProcessor $galleryProcessor */
$galleryProcessor = $objectManager->get(GalleryProcessor::class);
/** @var Filesystem $filesystem */
$filesystem = $objectManager->get(Filesystem::class);

$root = BP;
$importDir = $root . '/pub/media/import';
$csvPath = $root . '/_csv/catalog_product_sanitizado.csv';

function usage()
{
    echo "\nUso: php scripts/atribuir_imagens_por_sku.php [--csv _csv/catalog_product_sanitizado.csv] [--dry-run 1]\n";
    echo "- Procura imagens em pub/media/import por SKU (ex.: SKU.jpg|png) ou mapeadas via CSV.\n";
    echo "- Atribui image/small_image/thumbnail e adiciona na galeria.\n\n";
}

// Args
$dryRun = false;
for ($i = 1; $i < $argc; $i++) {
    if ($argv[$i] === '--csv' && isset($argv[$i+1])) { $csvPath = $argv[$i+1]; $i++; }
    elseif ($argv[$i] === '--dry-run' && isset($argv[$i+1])) { $dryRun = ($argv[$i+1] === '1'); $i++; }
    elseif ($argv[$i] === '--help') { usage(); exit(0); }
}

// Carrega mapeamento CSV (opcional)
$map = [];
if (is_file($csvPath)) {
    if (($h = fopen($csvPath, 'r')) !== false) {
        // Ler cabeçalho com delimitador, enclosure e escape explícitos para evitar avisos deprecatados
        $header = fgetcsv($h, 0, ',', '"', '\\');
        $skuIdx = null; $imgIdx = null;
        if (is_array($header)) {
            foreach ($header as $idx => $col) {
                $k = strtolower(trim((string)$col));
                if ($k === 'sku') $skuIdx = $idx;
                if ($k === 'image' || $k === 'imagem' || $k === 'image_file') $imgIdx = $idx;
            }
        }
        if ($skuIdx === null) { /* header desconhecido */ }
        while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false) {
            if ($skuIdx !== null) {
                $sku = trim((string)($row[$skuIdx] ?? ''));
                $img = trim((string)($row[$imgIdx] ?? ''));
                if ($sku !== '') { $map[$sku] = $img; }
            }
        }
        fclose($h);
    }
}

// Util: encontra arquivo por SKU
function findImageFileForSku(string $importDir, string $sku, ?string $hint = null): ?string
{
    $cands = [];
    $base = rtrim($importDir, '/');
    $try = [];
    if ($hint && $hint !== '') { $try[] = $hint; }
    $try[] = $sku . '.jpg';
    $try[] = $sku . '.jpeg';
    $try[] = $sku . '.png';
    $try[] = $sku . '.webp';
    foreach ($try as $rel) {
        $p = $base . '/' . $rel;
        if (is_file($p)) return $p;
    }
    // procura por prefixo SKU-
    foreach (glob($base . '/' . $sku . '*.{jpg,jpeg,png,webp}', GLOB_BRACE) as $g) {
        if (is_file($g)) return $g;
    }
    return null;
}

// Carrega SKUs existentes (somente habilitados e visíveis)
$resource = $objectManager->get(Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$catalogProductEntity = $connection->getTableName('catalog_product_entity');
$products = $connection->fetchAll("SELECT sku, entity_id FROM {$catalogProductEntity}");

$updated = 0; $missing = 0; $errors = 0; $total = count($products);

echo "\n➡️  Iniciando atribuição de imagens por SKU (total produtos: {$total})\n";
foreach ($products as $row) {
    $sku = (string)$row['sku'];
    if ($sku === '') continue;
    $hint = $map[$sku] ?? null;
    $file = findImageFileForSku($importDir, $sku, $hint);

    try {
        $product = $productRepo->get($sku, false, null, true);
        $hasImage = (string)$product->getData('image') !== '';
        $hasSmall = (string)$product->getData('small_image') !== '';
        $hasThumb = (string)$product->getData('thumbnail') !== '';

        if ($file === null && ($hasImage || $hasSmall || $hasThumb)) {
            // Já possui imagem, sem arquivo novo
            continue;
        }

        if ($file === null) {
            $missing++;
            echo "⚠️  SKU {$sku}: arquivo não encontrado em import/.\n";
            continue;
        }

        echo "🔧 SKU {$sku}: anexando imagem {$file}" . ($dryRun ? " (dry-run)" : "") . "\n";
        if ($dryRun) { $updated++; continue; }

        // Adiciona à galeria e define como image/small_image/thumbnail
        $product->addImageToMediaGallery(
            $file,
            ['image', 'small_image', 'thumbnail'],
            false,
            false
        );
        $productRepo->save($product);
        $updated++;
    } catch (\Throwable $e) {
        $errors++;
        echo "✗ Erro SKU {$sku}: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Conclusão: atualizados {$updated}, ausentes {$missing}, erros {$errors}.\n";
echo "Sugestão: executar 'php bin/magento indexer:reindex' e 'php bin/magento cache:flush'.\n";
