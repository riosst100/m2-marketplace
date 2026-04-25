<?php

namespace Lof\Quickrfq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lof\Quickrfq\Helper\Data;
use Lof\Quickrfq\Helper\ConvertQuote;
use Lof\Quickrfq\Model\QuickrfqFactory;
use Lof\Quickrfq\Model\Quickrfq;
use Lof\Quickrfq\Model\MessageFactory;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class ConvertQuickrfqToCart implements ResolverInterface
{
    protected $getCustomer;
    protected $helper;
    protected $quickrfqFactory;
    protected $convertQuoteHelper;
    protected $cart;
    protected $checkoutSession;
    protected $messageFactory;
    protected $eventManager;
    protected $scopeConfig;
    protected $quoteIdMaskFactory;

    public function __construct(
        GetCustomer $getCustomer,
        Data $helper,
        QuickrfqFactory $quickrfqFactory,
        ConvertQuote $convertQuoteHelper,
        Cart $cart,
        CheckoutSession $checkoutSession,
        MessageFactory $messageFactory,
        EventManager $eventManager,
        ScopeConfigInterface $scopeConfig,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->getCustomer = $getCustomer;
        $this->helper = $helper;
        $this->quickrfqFactory = $quickrfqFactory;
        $this->convertQuoteHelper = $convertQuoteHelper;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->messageFactory = $messageFactory;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!$context->getUserId()) {
            throw new GraphQlInputException(__('You must be logged in.'));
        }

        $customer = $this->getCustomer->execute($context);
        $quickrfqId = $args['quickrfq_id'] ?? null;

        if (!$quickrfqId) {
            throw new GraphQlInputException(__('quickrfq_id is required.'));
        }

        $quoteModel = $this->quickrfqFactory->create()->load($quickrfqId);

        if (!$quoteModel->getId()) {
            throw new GraphQlNoSuchEntityException(__('RFQ not found.'));
        }

        if ($this->helper->isExpiryQuote($quoteModel)) {
            throw new GraphQlInputException(__('This quote has expired.'));
        }

        // OPTIONALLY clear cart
        $mageQuote = $this->cart->getQuote();
        if (!$this->helper->getConfig("quote_process/keep_cart_item")) {
            foreach ($mageQuote->getAllItems() as $item) {
                $mageQuote->removeItem($item->getId());
            }
        }

        try {
            // dd($quoteModel->getData());
            $cartId = $this->convertQuoteHelper->processCreateCart($quoteModel);
            // Generate masked cart ID if not exists
            $maskModel = $this->quoteIdMaskFactory->create();
            $maskModel->load($cartId, 'quote_id');

            if (!$maskModel->getId()) {
                $maskModel->setQuoteId($cartId);
                $maskModel->save();
            }

            $maskedId = $maskModel->getMaskedId();


            if ($cartId) {
                // Update RFQ
                $quoteModel->setData("status", Quickrfq::STATUS_DONE);
                $quoteModel->setData("expiry", null);
                $quoteModel->save();

                // Create internal message
                $msg = $this->messageFactory->create();
                $msg->setData([
                    'quickrfq_id' => $quickrfqId,
                    'customer_id' => $customer->getId(),
                    'message' => __('The quote was converted to shopping cart sucessfully!')
                ]);
                $msg->save();

                // Dispatch same event as controller
                $this->eventManager->dispatch(
                    'frontend_quickrfq_on_checkoutcart',
                    [
                        'quote_id' => $quickrfqId,
                        'cart_id' => $cartId,                        
                        'data' => $msg->getData(),
                        'model' => $quoteModel,
                        'status' => 'success'
                    ]
                );

                return [
                    'status' => 'SUCCESS',
                    'message' => 'Quote converted successfully',
                    'cart_id' => (int)$cartId,
                    'masked_cart_id' => $maskedId,
                    'quickrfq_id' => (int)$quickrfqId,
                    'rfq_status' => Quickrfq::STATUS_DONE
                ];
            }

            // FAIL
            $this->eventManager->dispatch(
                'frontend_quickrfq_on_checkoutcart',
                ['quote_id' => $quickrfqId, 'cart_id' => null, 'data' => [], 'model' => null, 'status' => 'fail']
            );

            return [
                'status' => 'FAIL',
                'message' => 'Cannot convert quote to cart',
                'cart_id' => null,
                'masked_cart_id' => null,
                'quickrfq_id' => (int)$quickrfqId,
                'rfq_status' => $quoteModel->getStatus()
            ];

        } catch (\Exception $e) {

            $this->eventManager->dispatch(
                'frontend_quickrfq_on_checkoutcart',
                ['quote_id' => $quickrfqId, 'cart_id' => null, 'data' => [], 'model' => null, 'status' => 'fail']
            );

            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
