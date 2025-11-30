<?php
declare(strict_types=1);

namespace GrupoAwamotos\B2B\Controller\Adminhtml\Approval;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'GrupoAwamotos_B2B::approval';

    /** @var PageFactory */
    private $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Magento_Backend::system');
        $page->getConfig()->getTitle()->prepend(__('Aprovação B2B'));
        return $page;
    }
}
