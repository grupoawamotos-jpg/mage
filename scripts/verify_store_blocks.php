<?php
/**
 * Script de verificação dos blocos CMS que devem conter dados reais e não placeholders.
 * Executar a partir da raiz do projeto:
 *   php scripts/verify_store_blocks.php
 * Retorno exit code 0 se tudo ok, 1 se encontrar placeholders.
 */

use Magento\Framework\App\Bootstrap;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

/** @var BlockRepositoryInterface $blockRepo */
$blockRepo = $objectManager->get(BlockRepositoryInterface::class);
/** @var ScopeConfigInterface $scopeConfig */
$scopeConfig = $objectManager->get(ScopeConfigInterface::class);

$blocksToCheck = [
    'head_contact'    => 'Contato Cabeçalho',
    'footer_info'     => 'Footer Info',
    'footer_static'   => 'Footer Static',
    'fixed_right'     => 'Botões Fixos',
    'social_block'    => 'Redes Sociais',
    'hotline_header'  => 'Hotline Header',
    'top-left-static' => 'Top Left',
    'top-contact'     => 'Top Contact',
];

// Dados reais esperados (derivados das configs)
$realPhone   = (string)$scopeConfig->getValue('general/store_information/phone') ?: '(16) 3301-1890';
$realEmail   = (string)$scopeConfig->getValue('trans_email/ident_support/email') ?: 'awamotos.mkt@gmail.com';
$realCity    = (string)$scopeConfig->getValue('general/store_information/city') ?: 'Araraquara';
$realStreet1 = (string)$scopeConfig->getValue('general/store_information/street_line1') ?: 'R. Lavineo de Arruda Falcão, 1272';
$realWhatsapp= (string)$scopeConfig->getValue('grupoawamotos_store/contact/whatsapp') ?: '5516992451890';

$placeholdersPatterns = [
    '/4002-8922/i',
    '/Av\. Paulista/i',
    '/grupoawamotos\.com\.br/i',
    '/Rua Exemplo/i',
    '/\(11\)/'
];

$realPatterns = [
    preg_quote($realPhone, '/'),
    preg_quote($realEmail, '/'),
    preg_quote($realCity, '/'),
    preg_quote($realStreet1, '/'),
    preg_quote($realWhatsapp, '/'),
];

$foundPlaceholders = [];
$missingReal = [];

echo "\n╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                VERIFICAÇÃO DE BLOCOS CMS - DADOS REAIS / PLACEHOLDER        ║\n";
echo "╠══════════════════════════════════════════════════════════════════════════════╣\n";

foreach ($blocksToCheck as $id => $desc) {
    try {
        $block = $blockRepo->getById($id);
        $content = $block->getContent();
        $hasPlaceholder = false;
        $matchedPlaceholders = [];
        foreach ($placeholdersPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $hasPlaceholder = true;
                $matchedPlaceholders[] = trim($pattern, '/i');
            }
        }
        $hasReal = false;
        foreach ($realPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $content)) {
                $hasReal = true; break;
            }
        }
        $status = $hasPlaceholder ? '❌ PLACEHOLDER' : ($hasReal ? '✅ DADOS REAIS' : '⚠️ VERIFICAR');
        $extra = '';
        if ($hasPlaceholder) {
            $extra = ' (' . implode(', ', $matchedPlaceholders) . ')';
            $foundPlaceholders[$id] = $matchedPlaceholders;
        } elseif (!$hasReal) {
            $missingReal[] = $id;
        }
        printf("║ %-20s: %-15s%s\n", $desc, $status, $extra);
    } catch (Throwable $e) {
        printf("║ %-20s: ⚠️ ERRO AO CARREGAR (%s)\n", $desc, $e->getMessage());
        $missingReal[] = $id;
    }
}

echo "╠══════════════════════════════════════════════════════════════════════════════╣\n";
if ($foundPlaceholders) {
    echo "║ Placeholders detectados em: " . implode(', ', array_keys($foundPlaceholders)) . str_repeat(' ', 14) . "║\n";
}
if ($missingReal) {
    echo "║ Blocos sem dados reais claros: " . implode(', ', $missingReal) . str_repeat(' ', 8) . "║\n";
}
if (!$foundPlaceholders && !$missingReal) {
    echo "║ Todos os blocos verificados com dados reais." . str_repeat(' ', 26) . "║\n";
}

echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

exit($foundPlaceholders ? 1 : 0);
