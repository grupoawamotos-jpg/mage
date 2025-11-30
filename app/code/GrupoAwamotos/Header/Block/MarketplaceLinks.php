<?php
namespace GrupoAwamotos\Header\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;

class MarketplaceLinks extends Template
{
    protected CustomerSession $customerSession;
    protected UrlInterface $urlBuilder;
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isLoggedIn(): bool
    {
        return (bool)$this->customerSession->isLoggedIn();
    }

    public function getAccountUrl(): string
    {
        return $this->urlBuilder->getUrl('customer/account');
    }

    public function getLoginUrl(): string
    {
        return $this->urlBuilder->getUrl('customer/account/login');
    }

    public function getRegisterUrl(): string
    {
        return $this->urlBuilder->getUrl('customer/account/create');
    }

    public function getVendorDashboardUrl(): string
    {
        $route = (string)($this->scopeConfig->getValue('grupoawamotos_header/links/vendor_route', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 'marketplace/vendor/dashboard');
        return $this->urlBuilder->getUrl($route);
    }

    public function showVendorLink(): bool
    {
        if (!$this->isLoggedIn()) { return false; }
        if (!$this->isFlagEnabled('vendor_enable')) { return false; }
        return $this->isInAllowedGroup('vendor_groups');
    }

    public function getB2BQuoteUrl(): string
    {
        $route = (string)($this->scopeConfig->getValue('grupoawamotos_header/links/b2b_route', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 'b2b/quote/request');
        return $this->urlBuilder->getUrl($route);
    }

    public function showB2BLinks(): bool
    {
        if (!$this->isLoggedIn()) { return false; }
        if (!$this->isFlagEnabled('b2b_enable')) { return false; }
        return $this->isInAllowedGroup('b2b_groups');
    }

    private function isFlagEnabled(string $field): bool
    {
        $path = 'grupoawamotos_header/links/' . $field;
        return (bool)$this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private function isInAllowedGroup(string $field): bool
    {
        $allowed = (string)$this->scopeConfig->getValue('grupoawamotos_header/links/' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($allowed === '') { return true; } // vazio => todos
        $ids = array_filter(array_map('trim', explode(',', $allowed)), 'strlen');
        $ids = array_map('intval', $ids);
        try {
            $customerGroupId = (int)$this->customerSession->getCustomerGroupId();
            return in_array($customerGroupId, $ids, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
