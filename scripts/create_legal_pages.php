<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');

$pageFactory = $obj->get('Magento\Cms\Model\PageFactory');
$pageRepository = $obj->get('Magento\Cms\Api\PageRepositoryInterface');
$searchCriteriaBuilder = $obj->get('Magento\Framework\Api\SearchCriteriaBuilder');

$pagesToCreate = [
    [
        'title' => 'Termos e Condições',
        'identifier' => 'termos-e-condicoes',
        'content' => '<h1>Termos e Condições</h1><p>Conteúdo a ser definido.</p>',
        'is_active' => 1,
        'stores' => [0]
    ],
    [
        'title' => 'Política de Trocas e Devoluções',
        'identifier' => 'trocas-e-devolucoes',
        'content' => '<h1>Política de Trocas e Devoluções</h1><p>Conteúdo a ser definido.</p>',
        'is_active' => 1,
        'stores' => [0]
    ],
    [
        'title' => 'Política de Privacidade',
        'identifier' => 'politica-de-privacidade',
        'content' => '<h1>Política de Privacidade</h1><p>Conteúdo a ser definido.</p>',
        'is_active' => 1,
        'stores' => [0]
    ],
    [
        'title' => 'Quem Somos',
        'identifier' => 'quem-somos',
        'content' => '<h1>Quem Somos</h1><p>Conteúdo a ser definido.</p>',
        'is_active' => 1,
        'stores' => [0]
    ]
];

foreach ($pagesToCreate as $pageData) {
    $searchCriteria = $searchCriteriaBuilder->addFilter('identifier', $pageData['identifier'], 'eq')->create();
    $pages = $pageRepository->getList($searchCriteria)->getItems();

    if (empty($pages)) {
        echo "Creating page: " . $pageData['title'] . "\n";
        $page = $pageFactory->create();
        $page->setTitle($pageData['title']);
        $page->setIdentifier($pageData['identifier']);
        $page->setContent($pageData['content']);
        $page->setIsActive($pageData['is_active']);
        $page->setStoreId($pageData['stores']);
        $page->setPageLayout('1column');
        $pageRepository->save($page);
        echo "Page created.\n";
    } else {
        echo "Page already exists: " . $pageData['title'] . "\n";
    }
}

echo "Done.\n";
