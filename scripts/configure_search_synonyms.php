#!/usr/bin/env php
<?php
/**
 * Configure Search Synonyms and Attribute Weights for Magento 2
 * Usage: php scripts/configure_search_synonyms.php
 * 
 * Este script configura:
 * 1. Pesos de atributos de busca (name > sku > short_description > description)
 * 2. Sinônimos focados no mercado de motos brasileiro
 */

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
} catch (\Exception $e) {
    // Já definido
}

echo "============================================\n";
echo "CONFIGURAÇÃO DE BUSCA - SINÔNIMOS E PESOS\n";
echo "============================================\n\n";

// ============================================
// 1. CONFIGURAR PESOS DE ATRIBUTOS
// ============================================
echo ">> Configurando pesos de atributos de busca...\n";

$config = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);
$cacheManager = $objectManager->get(\Magento\Framework\App\Cache\Manager::class);

// Pesos recomendados para loja de motos
$searchWeights = [
    'name' => 10,           // Nome é mais importante
    'sku' => 8,             // SKU também é muito buscado
    'short_description' => 3,
    'description' => 1,
    'meta_keywords' => 5,
    'meta_title' => 4,
];

// Atributos que devem ser pesquisáveis
$searchableAttributes = ['name', 'sku', 'short_description', 'description', 'meta_keywords', 'meta_title', 'manufacturer'];

$eavSetup = $objectManager->get(\Magento\Eav\Setup\EavSetup::class);
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);

foreach ($searchWeights as $attrCode => $weight) {
    try {
        $attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attrCode);
        if ($attribute && $attribute->getId()) {
            $attribute->setData('search_weight', $weight);
            $attribute->setData('is_searchable', 1);
            $attribute->save();
            echo "   [OK] $attrCode => peso $weight\n";
        }
    } catch (\Exception $e) {
        echo "   [SKIP] $attrCode: " . $e->getMessage() . "\n";
    }
}

echo "\n>> Pesos configurados com sucesso!\n\n";

// ============================================
// 2. GERAR LISTA DE SINÔNIMOS
// ============================================
echo ">> Gerando sinônimos para mercado de motos brasileiro...\n";

$synonymGroups = [
    // Modelos de moto - variações de escrita
    ['titan', 'titam', 'tita', 'cg titan', 'cg125', 'cg150', 'cg160'],
    ['fan', 'cg fan', 'fan125', 'fan150', 'fan160'],
    ['twister', 'cb twister', 'cbx twister', 'twistter'],
    ['fazer', 'yamaha fazer', 'fz25', 'fz250', 'fazzer'],
    ['biz', 'honda biz', 'biz100', 'biz110', 'biz125'],
    ['cb300', 'cb 300', 'cb300r'],
    ['cb500', 'cb 500', 'cb500f', 'cb500x'],
    ['xre', 'xre300', 'xre 300', 'xre190', 'xre 190'],
    ['bros', 'nxr bros', 'bros150', 'bros160', 'nxr150', 'nxr160'],
    ['pop', 'pop100', 'pop 100', 'pop110', 'pop 110', 'honda pop'],
    ['pcx', 'pcx150', 'pcx 150', 'honda pcx'],
    ['lead', 'lead110', 'lead 110', 'honda lead'],
    ['xtz', 'xtz125', 'xtz 125', 'xtz150', 'xtz 150', 'crosser'],
    ['lander', 'xtz250', 'xtz 250', 'yamaha lander'],
    ['factor', 'factor125', 'factor 125', 'ybr factor', 'yamaha factor'],
    ['ybr', 'ybr125', 'ybr 125', 'yamaha ybr'],
    ['crypton', 'cripton', 'yamaha crypton', 't115'],
    ['neo', 'yamaha neo', 'neo125', 'neo 125', 'neo115'],
    ['crf', 'crf230', 'crf 230', 'crf250', 'crf 250', 'honda crf'],
    ['xr', 'xr250', 'xr 250', 'xr200', 'xr 200', 'honda xr'],
    ['tornado', 'xr250 tornado', 'xr 250 tornado', 'honda tornado'],
    ['hornet', 'cb600', 'cb 600', 'honda hornet', 'hornet600'],
    ['cbx', 'cbx250', 'cbx 250'],
    ['cbr', 'cbr600', 'cbr 600', 'cbr1000', 'cbr 1000'],
    ['crf150', 'crf 150'],
    ['dt', 'dt200', 'dt 200', 'yamaha dt'],
    ['xl', 'xl125', 'xl 125', 'xl250', 'xl 250'],
    
    // Tipos de peças
    ['retrovisor', 'retrovisores', 'espelho retrovisor', 'espelho'],
    ['pisca', 'piscas', 'seta', 'setas', 'pisca pisca', 'piscaalerta', 'indicador de direção'],
    ['bauleto', 'bauletos', 'bau', 'baú', 'baus', 'baús', 'bagageiro', 'caixa traseira'],
    ['guidao', 'guidão', 'guidon', 'manete', 'manetes'],
    ['carenagem', 'carenagens', 'carcaca', 'carcaça', 'carcaças', 'carenage'],
    ['paralama', 'para-lama', 'paralamas', 'para-lamas', 'para lama'],
    ['escapamento', 'escapamentos', 'descarga', 'ponteira', 'ponteiras'],
    ['farol', 'farois', 'faróis', 'lanterna', 'lanternas'],
    ['banco', 'selim', 'assento'],
    ['pneu', 'pneus', 'pneumatico', 'pneumático', 'borracha'],
    ['roda', 'rodas', 'aro', 'aros'],
    ['corrente', 'correntes', 'transmissao', 'transmissão', 'kit relação', 'kit relacao'],
    ['pastilha', 'pastilhas', 'pastilha de freio', 'lona', 'lonas'],
    ['disco', 'discos', 'disco de freio'],
    ['suspensao', 'suspensão', 'amortecedor', 'amortecedores', 'garfo'],
    ['cilindro', 'cilindros', 'kit cilindro', 'camisa'],
    ['pistao', 'pistão', 'pistões', 'pistoes'],
    ['vela', 'velas', 'vela de ignição', 'vela de ignicao'],
    ['filtro', 'filtros', 'filtro de ar', 'filtro de oleo', 'filtro de óleo'],
    ['oleo', 'óleo', 'lubrificante'],
    ['bateria', 'baterias', 'acumulador'],
    ['cdi', 'ignição', 'ignicao', 'modulo de ignição'],
    ['cabo', 'cabos', 'chicote', 'chicotes'],
    ['embreagem', 'embreagens', 'disco de embreagem'],
    ['freio', 'freios', 'sistema de freio'],
    ['pedal', 'pedais', 'pedaleira', 'pedaleiras'],
    ['estribo', 'estribos', 'apoio de pé', 'apoio de pe'],
    ['chave', 'chaves', 'fechadura', 'ignição', 'contato'],
    ['capacete', 'capacetes', 'casco'],
    ['luva', 'luvas'],
    ['jaqueta', 'jaquetas', 'japona'],
    ['capa', 'capas', 'capa de chuva', 'capa de moto'],
    
    // Posições e lados
    ['direito', 'dir', 'ld', 'lado direito', 'esquerdo', 'esq', 'le', 'lado esquerdo'],
    ['dir/esq', 'd/e', 'direito esquerdo', 'par'],
    ['dianteiro', 'dianteira', 'frente', 'frontal'],
    ['traseiro', 'traseira', 'tras', 'trás', 'posterior'],
    
    // Cores
    ['preto', 'preta', 'black', 'negro'],
    ['cromado', 'cromada', 'cromo', 'chrome', 'espelhado'],
    ['prata', 'silver', 'cinza'],
    ['vermelho', 'vermelha', 'red'],
    ['azul', 'blue'],
    ['branco', 'branca', 'white'],
    
    // Termos genéricos
    ['universal', 'generico', 'genérico', 'adaptavel', 'adaptável'],
    ['original', 'oem', 'genuino', 'genuíno', 'padrao', 'padrão'],
    ['kit', 'conjunto', 'jogo'],
    ['completo', 'completa', 'full'],
    ['par', 'duplo', 'dupla', 'dois', '2'],
    
    // Marcas fabricantes
    ['honda', 'hda'],
    ['yamaha', 'yam', 'ymh'],
    ['suzuki', 'suz'],
    ['kawasaki', 'kawa', 'kws'],
    ['dafra', 'dfr'],
    ['shineray', 'shine'],
    ['kasinski', 'kas'],
];

echo "   Total de grupos de sinônimos: " . count($synonymGroups) . "\n";

// Salvar sinônimos em arquivo para referência
$synonymsFile = BP . '/var/search_synonyms_moto.txt';
$synonymsContent = "# Sinônimos de Busca - Loja de Motos\n";
$synonymsContent .= "# Gerado em: " . date('Y-m-d H:i:s') . "\n";
$synonymsContent .= "# Formato: termo1, termo2, termo3 (equivalentes)\n\n";

foreach ($synonymGroups as $group) {
    $synonymsContent .= implode(', ', $group) . "\n";
}

file_put_contents($synonymsFile, $synonymsContent);
echo "   [OK] Sinônimos salvos em: var/search_synonyms_moto.txt\n";

// ============================================
// 3. CONFIGURAR SINÔNIMOS NO ELASTICSEARCH (se disponível)
// ============================================
echo "\n>> Tentando aplicar sinônimos no Elasticsearch...\n";

// Verificar se há módulo de sinônimos do Magento
try {
    $synonymRepository = $objectManager->get(\Magento\Search\Api\SynonymGroupRepositoryInterface::class);
    
    // Limpar sinônimos existentes (opcional - comentar se quiser manter)
    // $searchCriteria = $objectManager->get(\Magento\Framework\Api\SearchCriteriaBuilder::class)->create();
    // $existingSynonyms = $synonymRepository->getList($searchCriteria);
    
    $synonymGroupFactory = $objectManager->get(\Magento\Search\Api\Data\SynonymGroupInterfaceFactory::class);
    
    $count = 0;
    foreach ($synonymGroups as $group) {
        if (count($group) < 2) continue;
        
        try {
            $synonymGroup = $synonymGroupFactory->create();
            $synonymGroup->setStoreId(0); // All stores
            $synonymGroup->setWebsiteId(0);
            $synonymGroup->setSynonymGroup(implode(',', $group));
            $synonymRepository->save($synonymGroup);
            $count++;
        } catch (\Exception $e) {
            // Pode já existir ou erro de duplicata
        }
    }
    
    echo "   [OK] $count grupos de sinônimos aplicados via Magento Search API\n";
    
} catch (\Exception $e) {
    echo "   [INFO] Módulo de sinônimos não disponível ou erro: " . $e->getMessage() . "\n";
    echo "   [INFO] Sinônimos salvos em arquivo para importação manual.\n";
}

// ============================================
// 4. CONFIGURAR OPÇÕES DE BUSCA
// ============================================
echo "\n>> Configurando opções gerais de busca...\n";

$searchConfigs = [
    'catalog/search/min_query_length' => 2,      // Mínimo 2 caracteres
    'catalog/search/max_query_length' => 128,
    'catalog/search/autocomplete_limit' => 8,    // 8 sugestões no autocomplete
];

foreach ($searchConfigs as $path => $value) {
    try {
        $config->save($path, $value);
        echo "   [OK] $path = $value\n";
    } catch (\Exception $e) {
        echo "   [SKIP] $path: " . $e->getMessage() . "\n";
    }
}

// Limpar cache de configuração
$cacheManager->flush(['config']);
echo "\n   [OK] Cache de configuração limpo.\n";

echo "\n============================================\n";
echo "CONFIGURAÇÃO CONCLUÍDA!\n";
echo "============================================\n\n";
echo "PRÓXIMOS PASSOS:\n";
echo "1. Reindexar busca:\n";
echo "   php -d memory_limit=2G bin/magento indexer:reindex catalogsearch_fulltext\n\n";
echo "2. Limpar caches:\n";
echo "   php bin/magento cache:flush\n\n";
echo "3. Testar buscas no frontend:\n";
echo "   - 'titan 150' deve encontrar CG Titan 150\n";
echo "   - 'retrovisor biz' deve encontrar retrovisores para Biz\n";
echo "   - 'pisca cb300' deve encontrar piscas para CB 300\n";
echo "   - 'bauleto 45l' deve encontrar bauletos de 45 litros\n";
echo "   - 'cromado universal' deve encontrar peças cromadas universais\n\n";
