<?php
/**
 * Quick Order Link Block - Only shows for logged-in customers
 */
declare(strict_types=1);

namespace GrupoAwamotos\B2B\Block\Link;

use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Customer\Block\Account\SortLinkInterface;

class QuickOrder extends Current implements SortLinkInterface
{
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int) $this->getData('sortOrder') ?: 55;
    }

    /**
     * Render block HTML only for logged-in users
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        // Only show for logged in users
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        
        return parent::_toHtml();
    }
}
