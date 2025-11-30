<?php
/**
 * Quick Order Form Block
 */
declare(strict_types=1);

namespace GrupoAwamotos\B2B\Block\QuickOrder;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;

class Form extends Template
{
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * Number of default empty rows
     */
    private const DEFAULT_ROWS = 5;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->getUrl('b2b/quickorder/add');
    }

    /**
     * Get number of default empty rows
     *
     * @return int
     */
    public function getDefaultRows(): int
    {
        return self::DEFAULT_ROWS;
    }

    /**
     * Get cart URL
     *
     * @return string
     */
    public function getCartUrl(): string
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Get continue shopping URL
     *
     * @return string
     */
    public function getContinueShoppingUrl(): string
    {
        return $this->getUrl('/');
    }

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCustomerName(): string
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getName();
        }
        return '';
    }
}
