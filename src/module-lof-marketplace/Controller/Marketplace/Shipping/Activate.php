<?php
namespace Lof\MarketPlace\Controller\Marketplace\Shipping;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;

class Activate extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $layoutFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $method = $this->getRequest()->getParam('method');
        $resultJson = $this->resultJsonFactory->create();

        if (!$method) {
            return $resultJson->setData(['success' => false]);
        }

        // Load dynamic form HTML block
        $layout = $this->layoutFactory->create();
        $block = $layout
            ->createBlock(\Lof\MarketPlace\Block\Shipping\ActivateForm::class)
            ->setTemplate('Lof_MarketPlace::shipping/activate_form.phtml')
            ->setMethod($method);

        $html = $block->toHtml();

        return $resultJson->setData([
            'success' => true,
            'html' => $html
        ]);
    }
}
