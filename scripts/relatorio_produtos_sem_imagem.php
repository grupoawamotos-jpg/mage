<?php
/**
 * Relatório de Produtos sem Imagem
 * 
 * Gera lista de produtos sem imagem atribuída e sugere arquivos no diretório import.
 * 
 * Uso:
 *   php scripts/relatorio_produtos_sem_imagem.php [--output=json|csv|txt]
 */

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get(\Magento\Framework\App\State::class);
$state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

$productRepository = $obj->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$searchCriteriaBuilder = $obj->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
$directoryList = $obj->get(\Magento\Framework\App\Filesystem\DirectoryList::class);
$importDir = $directoryList->getRoot() . '/pub/media/import/catalog/products';

// Obter formato de output
$output = 'txt';
foreach ($argv as $arg) {
    if (strpos($arg, '--output=') === 0) {
        $output = substr($arg, 9);
    }
}

// Buscar todos os produtos
$searchCriteria = $searchCriteriaBuilder->create();
$products = $productRepository->getList($searchCriteria)->getItems();

$semImagem = [];
$comImagem = 0;
$total = count($products);

echo "🔍 Analisando {$total} produtos...\n\n";

foreach ($products as $product) {
    $sku = $product->getSku();
    $name = $product->getName();
    $image = $product->getImage();
    
    // Verificar se tem imagem atribuída
    if (empty($image) || $image === 'no_selection') {
        // Buscar arquivos candidatos no diretório import
        $candidatos = [];
        
        // Padrões de busca baseados no SKU
        $patterns = [
            $sku . '.jpg',
            $sku . '.jpeg',
            $sku . '.png',
            strtolower($sku) . '.jpg',
            strtolower($sku) . '.jpeg',
            strtolower($sku) . '.png',
            str_replace(' ', '_', $sku) . '.jpg',
            str_replace(' ', '-', $sku) . '.jpg',
        ];
        
        foreach ($patterns as $pattern) {
            $filePath = $importDir . '/' . $pattern;
            if (file_exists($filePath)) {
                $candidatos[] = $pattern;
            }
        }
        
        $semImagem[] = [
            'sku' => $sku,
            'name' => $name,
            'id' => $product->getId(),
            'enabled' => $product->getStatus() == 1 ? 'Sim' : 'Não',
            'candidatos' => $candidatos
        ];
    } else {
        $comImagem++;
    }
}

// Estatísticas
$semImagemCount = count($semImagem);
$percentualComImagem = $total > 0 ? round(($comImagem / $total) * 100, 2) : 0;

echo "📊 Estatísticas:\n";
echo "   Total de produtos: {$total}\n";
echo "   Com imagem: {$comImagem} ({$percentualComImagem}%)\n";
echo "   Sem imagem: {$semImagemCount}\n\n";

// Output do relatório
switch ($output) {
    case 'json':
        echo json_encode([
            'total' => $total,
            'com_imagem' => $comImagem,
            'sem_imagem' => $semImagemCount,
            'percentual_com_imagem' => $percentualComImagem,
            'produtos_sem_imagem' => $semImagem
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;
        
    case 'csv':
        echo "sku,nome,id,habilitado,arquivo_candidato\n";
        foreach ($semImagem as $item) {
            $candidato = !empty($item['candidatos']) ? implode('|', $item['candidatos']) : 'Nenhum';
            echo "\"{$item['sku']}\",\"{$item['name']}\",{$item['id']},\"{$item['enabled']}\",\"{$candidato}\"\n";
        }
        break;
        
    default: // txt
        if ($semImagemCount > 0) {
            echo "📋 Produtos sem imagem:\n";
            echo str_repeat('=', 80) . "\n\n";
            
            foreach ($semImagem as $item) {
                echo "SKU: {$item['sku']}\n";
                echo "Nome: {$item['name']}\n";
                echo "ID: {$item['id']}\n";
                echo "Habilitado: {$item['enabled']}\n";
                
                if (!empty($item['candidatos'])) {
                    echo "Arquivos candidatos encontrados:\n";
                    foreach ($item['candidatos'] as $candidato) {
                        echo "  ✓ pub/media/import/catalog/products/{$candidato}\n";
                    }
                } else {
                    echo "⚠️  Nenhum arquivo candidato encontrado em pub/media/import/\n";
                    echo "   Sugestão: adicionar arquivo como {$item['sku']}.jpg\n";
                }
                
                echo "\n" . str_repeat('-', 80) . "\n\n";
            }
            
            echo "💡 Dica: Execute novamente 'php scripts/atribuir_imagens_por_sku.php' após adicionar os arquivos.\n";
        } else {
            echo "✅ Todos os produtos possuem imagens atribuídas!\n";
        }
        break;
}

echo "\n✅ Relatório concluído.\n";
