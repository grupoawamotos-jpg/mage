<?php
namespace GrupoAwamotos\Header\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\ScopeInterface;

class AddRtlBodyClass implements ObserverInterface
{
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private PageConfig $pageConfig;

    // Locales onde a direcao de escrita eh RTL
    private array $rtlLocales = [
        'ar_SA','ar_AE','ar_EG','he_IL','fa_IR','ur_PK'
    ];

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        PageConfig $pageConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->pageConfig = $pageConfig;
    }

    public function execute(Observer $observer): void
    {
        $locale = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE);
        if (in_array($locale, $this->rtlLocales, true)) {
            $this->pageConfig->addBodyClass('rtl');
        }
    }
}
