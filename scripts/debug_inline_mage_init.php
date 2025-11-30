<?php
/**
 * Debug data-mage-init inline com contexto HTML
 */

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
} catch (\Exception $e) {
    // Área já definida
}

// Simula request da home
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

$page = $objectManager->create(\Magento\Cms\Model\Page::class);
$page->load('home', 'identifier');

if (!$page->getId()) {
    echo "❌ Página home não encontrada\n";
    exit(1);
}

// Renderiza a página
$resultPageFactory = $objectManager->get(\Magento\Framework\View\Result\PageFactory::class);
$resultPage = $resultPageFactory->create();

// Obtém HTML renderizado
$layout = $resultPage->getLayout();
$output = $layout->getOutput();

// Busca elementos com data-mage-init com contexto
preg_match_all('/<([a-z0-9-]+)([^>]*)data-mage-init=(["\'])([^\3]*?)\3([^>]*)>/is', $output, $matches, PREG_SET_ORDER);

echo "\n🔍 Total de elementos com data-mage-init: " . count($matches) . "\n\n";

foreach ($matches as $index => $match) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "📦 ELEMENTO #" . ($index + 1) . "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    $tagName = $match[1];
    $attrsBefore = $match[2];
    $quote = $match[3];
    $jsonContent = $match[4];
    $attrsAfter = $match[5];
    
    echo "Tag: <{$tagName}>\n";
    echo "Quote: {$quote}\n\n";
    
    // Mostra elemento completo
    echo "Elemento completo:\n";
    $fullElement = $match[0];
    if (strlen($fullElement) > 400) {
        echo substr($fullElement, 0, 400) . "...\n\n";
    } else {
        echo $fullElement . "\n\n";
    }
    
    // Decodifica JSON
    $jsonDecoded = html_entity_decode($jsonContent, ENT_QUOTES | ENT_HTML5);
    
    echo "JSON (decodificado):\n";
    echo $jsonDecoded . "\n\n";
    
    // Valida JSON
    $decoded = json_decode($jsonDecoded, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ ERRO JSON: " . json_last_error_msg() . "\n";
        echo "   Código de erro: " . json_last_error() . "\n\n";
        
        // Análise de padrões problemáticos
        $issues = [];
        
        // Aspas simples em chaves
        if (preg_match_all("/'(\w+)'\s*:/", $jsonDecoded, $singleQuotes)) {
            $issues[] = "Chaves com aspas simples: " . implode(', ', array_map(function($k) {
                return "'{$k}'";
            }, $singleQuotes[1]));
        }
        
        // Trailing commas
        if (preg_match('/,\s*[}\]]/', $jsonDecoded)) {
            $issues[] = "Vírgulas trailing detectadas";
        }
        
        // Valores sem aspas (que deveriam ter)
        if (preg_match('/:[\s]*([a-zA-Z_][a-zA-Z0-9_-]*)[\s]*[,}\]]/', $jsonDecoded, $unquoted)) {
            if (!in_array($unquoted[1], ['true', 'false', 'null'])) {
                $issues[] = "Valor possivelmente sem aspas: {$unquoted[1]}";
            }
        }
        
        if (!empty($issues)) {
            echo "🔎 Problemas detectados:\n";
            foreach ($issues as $issue) {
                echo "   - {$issue}\n";
            }
            echo "\n";
        }
        
        // Mostra bytes ao redor da posição 422 se aplicável
        if (strlen($jsonDecoded) >= 422) {
            echo "🔎 Contexto posição 422 (±50 chars):\n";
            $start = max(0, 372);
            $context = substr($jsonDecoded, $start, 100);
            echo "   [pos {$start}-" . ($start + 100) . "]: " . $context . "\n\n";
        }
        
    } else {
        echo "✅ JSON válido\n\n";
    }
}

echo "\n✅ Análise concluída\n";
