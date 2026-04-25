<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterface;
use Lofmp\Rma\Model\RmaFactory;
use Lofmp\Rma\Helper\Data as RmaHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Lofmp\Rma\Helper\Help as HelpHelper;
use Lofmp\Rma\Api\Repository\CustomerRmaRepositoryInterface;

class RmaDetail implements ResolverInterface
{
    protected $rmaFactory;
    protected $rmaHelper;
    protected $helpHelper;
    protected $orderRepository;
    protected $urlBuilder;
    protected $resource;
    private $customerRmaRepository;
    protected $imageHelper;
    protected $status;

    public function __construct(
        RmaFactory $rmaFactory,
        RmaHelper $rmaHelper,
        HelpHelper $helpHelper,
        OrderRepositoryInterface $orderRepository,
        UrlInterface $urlBuilder,
        ResourceConnection $resource,
        CustomerRmaRepositoryInterface $customerRmaRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Lofmp\Rma\Model\Status $statusFactory
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->rmaHelper = $rmaHelper;
        $this->helpHelper = $helpHelper;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = $urlBuilder;
        $this->resource = $resource;
        $this->customerRmaRepository = $customerRmaRepository;
        $this->imageHelper = $imageHelper;
        $this->status = $statusFactory;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = $context->getUserId();

        if (empty($args['rmaId'])) {
            throw new GraphQlInputException(__('RMA ID is required'));
        }

        $rma = $this->customerRmaRepository->getById($customerId, $args['rmaId']);
        if (!$rma->getId()) {
            throw new LocalizedException(__('RMA not found'));
        }

        $order = $this->orderRepository->get($rma->getOrderId());
        $seller = $rma->getSellerId() ? $this->rmaHelper->getSellerById($rma->getSellerId()) : null;

        // RMA items
        $rmaItems = [];
        foreach ($order->getAllItems() as $item) {
            $itemData = $this->rmaHelper->getRmaItemData($item, $rma->getId());
            if (empty($itemData) || ($itemData['qty_requested'] == 0)) {
                continue;
            }

            $options = [];
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['attributes_info'])) {
                foreach ($productOptions['attributes_info'] as $opt) {
                    $options[] = [
                        'label' => $opt['label'],
                        'value' => $opt['value']
                    ];
                }
            }

            $store = $context->getExtensionAttributes()->getStore();
            $currency = $store->getCurrentCurrencyCode();
            // dd($item->getData());
            $rmaItems[] = [
                'id' => $item->getItemId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'price' => [
                    'value' => (float)$item->getPrice(),
                    'currency' => $store->getCurrentCurrencyCode() // or base currency
                ],
                'price_incl_tax' => [
                    'value' => (float)$item->getPriceInclTax(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'amount_refunded' => [
                    'value' => (float)$item->getAmountRefunded(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                'discount_amount' => [
                    'value' => (float)$item->getDiscountAmount(),
                    'currency' => $store->getCurrentCurrencyCode()
                ],
                // 'price' => $item->getPrice(),
                // 'price_incl_tax' => $item->getPriceInclTax(),
                // 'amount_refunded' => $item->getAmountRefunded(),
                // 'discount_amount' => $item->getDiscountAmount(),
                'qty_requested' => $itemData['qty_requested'] ?? 0,
                'reason' => $itemData['reason_name'] ?? '',
                'condition' => $itemData['condition_name'] ?? '',
                'resolution' => $itemData['resolution_name'] ?? '',
                'image_url' => $this->initImage($item)->resize(150)->getUrl(),
                'product_options' => $options,
                'seller_name' => $this->rmaHelper->getSellerName($rma->getSellerId())
            ];
        }

        // Messages
        $messages = [];
        foreach ($this->rmaHelper->getMessages($rma, true) as $message) {
            if ($message->getInternal() != 0) {
                continue;
            }

            $attachments = [];
            foreach ($this->rmaHelper->getAttachments('message', $message->getId()) as $attachment) {
                $attachments[] = [
                    'id' => $attachment->getId(),
                    'name' => $attachment->getName(),
                    'url' => $this->getAttachmentUrl($attachment->getUid())
                ];
            }

            $messages[] = [
                'id' => $message->getId(),
                'sender_name' => $message->getCustomerName() ?: $this->rmaHelper->getUserName($message->getUserId()),
                'sender_email' => $this->rmaHelper->getCustomerEmail($message->getCustomerId()),
                'text' => $message->getText(),
                'created_at' => $message->getCreatedAt(),
                'attachments' => $attachments
            ];
        }
        // dd($order->getShippingAddress()->getData());
        return [
            'rma_id' => $rma->getId(),
            'increment_id' => $rma->getIncrementId(),
            'status' => $rma->getStatusId(),
            'status_label' => $this->getStatusname($rma->getStatusId()),
            'rma_date' => $rma->getCreatedAt(),
            'order' => [
                'id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'status' => $order->getStatus(),
                'status_label' => $order->getStatusLabel(),
            ],
            'seller' => $seller ? [
                'id' => $seller->getId(),
                'name' => $seller->getName(),
                'url' => $seller->getUrl()
            ] : null,
            'shipping_address' => $order->getShippingAddress()->getData() ? [
                'entity_id' => $order->getShippingAddress()->getEntityId(),
                'parent_id' => $order->getShippingAddress()->getParentId(),
                'customer_address_id' => $order->getShippingAddress()->getCustomerAddressId(),
                'quote_address_id' => $order->getShippingAddress()->getQuoteAddressId(),
                'region_id' => $order->getShippingAddress()->getRegionId(),
                'customer_id' => $order->getShippingAddress()->getCustomerId(),
                'fax' => $order->getShippingAddress()->getFax(),
                'region' => $order->getShippingAddress()->getRegion(),
                'postcode' => $order->getShippingAddress()->getPostcode(),
                'lastname' => $order->getShippingAddress()->getLastname(),
                'street' => $order->getShippingAddress()->getStreet(),
                'city' => $order->getShippingAddress()->getCity(),
                'email' => $order->getShippingAddress()->getEmail(),
                'telephone' => $order->getShippingAddress()->getTelephone(),
                'country_id' => $order->getShippingAddress()->getCountryId(),
                'firstname' => $order->getShippingAddress()->getFirstname(),
                'address_type' => $order->getShippingAddress()->getAddressType(),
                'prefix' => $order->getShippingAddress()->getPrefix(),
                'middlename' => $order->getShippingAddress()->getMiddlename(),
                'suffix' => $order->getShippingAddress()->getSuffix(),
                'company' => $order->getShippingAddress()->getCompany(),
                'vat_id' => $order->getShippingAddress()->getVatId(),
                'vat_is_valid' => $order->getShippingAddress()->getVatIsValid(),
                'vat_request_id' => $order->getShippingAddress()->getVatRequestId(),
                'vat_request_date' => $order->getShippingAddress()->getVatRequestDate(),
                'vat_request_success' => $order->getShippingAddress()->getVatRequestSuccess(),
                'cash_on_delivery' => $order->getShippingAddress()->getCashOnDelivery(),
                'base_cash_on_delivery' => $order->getShippingAddress()->getBaseCashOnDelivery()
            ] : null,
            'items' => $rmaItems,
            'messages' => $messages
        ];
    }

    public function initImage($item)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku', $item->getData('sku'));
        return $this->imageHelper->init($product, 'product_page_image_small', ['type' => 'small_image']);
    }

    public function getAttachmentUrl($Uid)
    {
        return $this->urlBuilder->getUrl('rma/attachment/download', ['uid' => $Uid]);
    }

    public function getStatusname($id)
    {
         $status =  $this->status->load($id);
         return $status->getName();
    }
}
