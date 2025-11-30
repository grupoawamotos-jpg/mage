<?php
/**
 * Quick Order Index Controller
 * Displays the quick order form for bulk ordering by SKU
 */
declare(strict_types=1);

namespace GrupoAwamotos\B2B\Controller\QuickOrder;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    public function __construct(
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Require login for quick order
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addNoticeMessage(
                __('Por favor, faça login para usar o Pedido Rápido.')
            );
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath('customer/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Pedido Rápido por SKU'));
        
        return $resultPage;
    }
}
