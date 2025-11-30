<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');

$templateFactory = $obj->get('Magento\Email\Model\TemplateFactory');
$templateCollectionFactory = $obj->get('Magento\Email\Model\ResourceModel\Template\CollectionFactory');
$ruleFactory = $obj->get('Amasty\Acart\Model\RuleFactory');
$ruleCollectionFactory = $obj->get('Amasty\Acart\Model\ResourceModel\Rule\CollectionFactory');
$storeManager = $obj->get('Magento\Store\Model\StoreManagerInterface');
$groupCollectionFactory = $obj->get('Magento\Customer\Model\ResourceModel\Group\CollectionFactory');

// 1. Find or Create Email Template
$templateCode = 'amasty_acart_template';
$templateLabel = 'Modelo Padrão Recuperação de Carrinho';

$templateCollection = $templateCollectionFactory->create();
$templateCollection->addFieldToFilter('orig_template_code', $templateCode);
$template = $templateCollection->getFirstItem();

if (!$template->getId()) {
    echo "Creating email template...\n";
    $template = $templateFactory->create();
    $template->setTemplateCode($templateCode); // This is actually the orig_template_code in the loadDefault logic
    
    // Load default content
    $template->loadDefault($templateCode);
    $template->setTemplateCode($templateLabel); // Set the user-facing name
    $template->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML);
    $template->save();
    echo "Template created with ID: " . $template->getId() . "\n";
} else {
    echo "Using existing template ID: " . $template->getId() . "\n";
}

$templateId = $template->getId();

// 2. Find or Create Rule
$ruleName = 'Recuperação de Carrinho Padrão';
$ruleCollection = $ruleCollectionFactory->create();
$ruleCollection->addFieldToFilter('name', $ruleName);
$rule = $ruleCollection->getFirstItem();

if ($rule->getId()) {
    echo "Rule '$ruleName' already exists (ID: " . $rule->getId() . "). Skipping creation.\n";
} else {
    echo "Creating rule '$ruleName'...\n";
    $rule = $ruleFactory->create();
    $rule->setName($ruleName);
    $rule->setIsActive(1);
    $rule->setPriority(1);
    
    // Get all store IDs
    $stores = $storeManager->getStores(true);
    $storeIds = array_keys($stores);
    $rule->setStoreIds(implode(',', $storeIds));
    
    // Get all customer group IDs
    $groups = $groupCollectionFactory->create();
    $groupIds = [];
    foreach ($groups as $group) {
        $groupIds[] = $group->getId();
    }
    $rule->setCustomerGroupIds(implode(',', $groupIds));
    
    // Default condition (always true)
    // This is a serialized string of the condition model. 
    // It's safer to let Magento handle it or copy a simple "All" condition.
    // For now, we'll try to save it without specific conditions, which usually implies "All".
    // But Amasty might require it.
    // Let's try to set a basic condition structure.
    $conditions = [
        'type' => 'Amasty\Acart\Model\SalesRule\Rule\Condition\Combine',
        'attribute' => null,
        'operator' => null,
        'value' => '1',
        'is_value_processed' => null,
        'aggregator' => 'all'
    ];
    // We need to serialize this. Amasty uses Amasty\Base\Model\Serializer
    $serializer = $obj->get('Amasty\Base\Model\Serializer');
    $rule->setConditionsSerialized($serializer->serialize($conditions));

    $rule->save();
    echo "Rule created with ID: " . $rule->getId() . "\n";

    // 3. Set Schedule
    echo "Setting schedule...\n";
    $schedules = [
        [
            'days' => 0,
            'hours' => 1,
            'minutes' => 0,
            'template_id' => $templateId,
            'discount_amount' => 0,
            'discount_step' => 0
        ],
        [
            'days' => 1,
            'hours' => 0,
            'minutes' => 0,
            'template_id' => $templateId,
            'discount_amount' => 0,
            'discount_step' => 0
        ],
        [
            'days' => 3,
            'hours' => 0,
            'minutes' => 0,
            'template_id' => $templateId,
            'discount_amount' => 5, // 5% discount on the last email? Maybe just 0 for now.
            'simple_action' => 'by_percent',
            'discount_step' => 0
        ]
    ];

    $rule->setSchedule($schedules);
    $rule->saveSchedule();
    echo "Schedule saved.\n";
}

echo "Done.\n";
