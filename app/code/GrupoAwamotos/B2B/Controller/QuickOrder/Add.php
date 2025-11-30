<?php
/**
 * Quick Order Add Controller
 * Processes bulk add to cart by SKU
 */
declare(strict_types=1);

namespace GrupoAwamotos\B2B\Controller\QuickOrder;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Psr\Log\LoggerInterface;

class Add implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Cart
     */
    private Cart $cart;

    /**
     * @var FormKeyValidator
     */
    private FormKeyValidator $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        JsonFactory $jsonFactory,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        FormKeyValidator $formKeyValidator,
        LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $isAjax = $this->request->isAjax();
        
        // Validate login
        if (!$this->customerSession->isLoggedIn()) {
            return $this->returnError(__('Por favor, faça login para continuar.'), $isAjax);
        }

        // Validate form key
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->returnError(__('Sessão expirada. Por favor, atualize a página.'), $isAjax);
        }

        // Get items from request
        $items = $this->request->getParam('items', []);
        
        // Also support CSV/text input
        $bulkText = $this->request->getParam('bulk_input', '');
        if (!empty($bulkText)) {
            $items = array_merge($items, $this->parseBulkInput($bulkText));
        }

        if (empty($items)) {
            return $this->returnError(__('Nenhum item informado.'), $isAjax);
        }

        $addedCount = 0;
        $errors = [];
        $addedProducts = [];

        foreach ($items as $item) {
            $sku = trim($item['sku'] ?? '');
            $qty = (float)($item['qty'] ?? 1);

            if (empty($sku)) {
                continue;
            }

            if ($qty <= 0) {
                $qty = 1;
            }

            try {
                $product = $this->productRepository->get($sku);
                
                // Check if product is salable
                if (!$product->isSalable()) {
                    $errors[] = [
                        'sku' => $sku,
                        'message' => __('Produto indisponível.')
                    ];
                    continue;
                }

                // Add to cart
                $this->cart->addProduct($product, ['qty' => $qty]);
                $addedCount++;
                $addedProducts[] = [
                    'sku' => $sku,
                    'name' => $product->getName(),
                    'qty' => $qty
                ];

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $errors[] = [
                    'sku' => $sku,
                    'message' => __('SKU não encontrado.')
                ];
            } catch (\Exception $e) {
                $this->logger->error('Quick Order Error: ' . $e->getMessage(), [
                    'sku' => $sku,
                    'qty' => $qty
                ]);
                $errors[] = [
                    'sku' => $sku,
                    'message' => __('Erro ao adicionar: %1', $e->getMessage())
                ];
            }
        }

        // Save cart
        if ($addedCount > 0) {
            try {
                $this->cart->save();
            } catch (\Exception $e) {
                $this->logger->error('Quick Order Cart Save Error: ' . $e->getMessage());
                return $this->returnError(__('Erro ao salvar o carrinho.'), $isAjax);
            }
        }

        // Build response
        if ($isAjax) {
            $result = $this->jsonFactory->create();
            return $result->setData([
                'success' => $addedCount > 0,
                'added_count' => $addedCount,
                'added_products' => $addedProducts,
                'errors' => $errors,
                'message' => $addedCount > 0 
                    ? __('%1 produto(s) adicionado(s) ao carrinho.', $addedCount)
                    : __('Nenhum produto foi adicionado.'),
                'cart_url' => $this->cart->getQuote()->getStore()->getUrl('checkout/cart')
            ]);
        }

        // Non-AJAX response
        if ($addedCount > 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 produto(s) adicionado(s) ao carrinho.', $addedCount)
            );
        }

        foreach ($errors as $error) {
            $this->messageManager->addErrorMessage(
                __('SKU %1: %2', $error['sku'], $error['message'])
            );
        }

        $redirect = $this->redirectFactory->create();
        
        if ($addedCount > 0 && empty($errors)) {
            return $redirect->setPath('checkout/cart');
        }
        
        return $redirect->setPath('b2b/quickorder');
    }

    /**
     * Parse bulk text input (CSV format: SKU,QTY or SKU;QTY or SKU QTY)
     *
     * @param string $text
     * @return array
     */
    private function parseBulkInput(string $text): array
    {
        $items = [];
        $lines = preg_split('/[\r\n]+/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try different delimiters: comma, semicolon, tab, space
            $parts = preg_split('/[,;\t\s]+/', $line, 2);
            
            $sku = trim($parts[0] ?? '');
            $qty = (float)trim($parts[1] ?? 1);

            if (!empty($sku)) {
                $items[] = [
                    'sku' => $sku,
                    'qty' => $qty > 0 ? $qty : 1
                ];
            }
        }

        return $items;
    }

    /**
     * Return error response
     *
     * @param string|\Magento\Framework\Phrase $message
     * @param bool $isAjax
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    private function returnError($message, bool $isAjax)
    {
        if ($isAjax) {
            $result = $this->jsonFactory->create();
            return $result->setData([
                'success' => false,
                'message' => $message
            ]);
        }

        $this->messageManager->addErrorMessage($message);
        $redirect = $this->redirectFactory->create();
        return $redirect->setPath('b2b/quickorder');
    }
}
