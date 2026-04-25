<?php
namespace Lof\Quickrfq\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\Quickrfq\Model\QuickrfqRepository;
use Lof\Quickrfq\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Lof\Quickrfq\Model\ResourceModel\Attachment\CollectionFactory as AttachmentCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;

class QuickrfqDetail implements ResolverInterface
{
    protected $quickrfqRepository;
    protected $messageCollectionFactory;
    protected $attachmentCollectionFactory;
    protected $imageHelper;
    protected $productFactory;

    public function __construct(
        QuickrfqRepository $quickrfqRepository,
        MessageCollectionFactory $messageCollectionFactory,
        AttachmentCollectionFactory $attachmentCollectionFactory,
        Image $imageHelper,
        ProductFactory $productFactory
    ) {
        $this->quickrfqRepository = $quickrfqRepository;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->imageHelper    = $imageHelper;
        $this->productFactory = $productFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = $context->getUserId();

        $quickrfqId = (int)($args['quickrfq_id'] ?? 0);
        if (!$quickrfqId) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(__('Missing quickrfq_id'));
        }

        try {
            $quickrfq = $this->quickrfqRepository->get($quickrfqId);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        // Get messages
        $messageCollection = $this->messageCollectionFactory->create()
            ->addFieldToFilter('quickrfq_id', $quickrfqId)
            ->setOrder('created_at', 'ASC');

        $messages = [];
        foreach ($messageCollection as $message) {
            $messages[] = [
                'entity_id' => (int)$message->getId(),
                'quickrfq_id' => (int)$message->getQuickrfqId(),
                'customer_id' => (int)$message->getCustomerId(),
                'message' => $message->getMessage(),
                'is_main' => (int)$message->getIsMain(),
                'created_at' => $message->getCreatedAt()
            ];
        }

        // Get attachments
        $attachmentCollection = $this->attachmentCollectionFactory->create()
            ->addFieldToFilter('quickrfq_id', $quickrfqId)
            ->setOrder('created_at', 'ASC');

        $attachments = [];
        foreach ($attachmentCollection as $attachment) {
            $attachments[] = [
                'entity_id' => (int)$attachment->getId(),
                'quickrfq_id' => (int)$attachment->getQuickrfqId(),
                'message_id' => (int)$attachment->getMessageId(),
                'file_name' => $attachment->getFileName(),
                'file_path' => $attachment->getFilePath(),
                'file_type' => $attachment->getFileType(),
                'created_at' => $attachment->getCreatedAt()
            ];
        }
        // dd($quickrfq->getData());
        return [
            'quickrfq_id' => (int)$quickrfq->getId(),
            'contact_name' => $quickrfq->getContactName(),
            'email' => $quickrfq->getEmail(),
            'phone' => $quickrfq->getPhone(),            
            'product_name' => $quickrfq->getProductName(),
            'product_sku' => $quickrfq->getProductSku(),
            'image_url' => $this->getProductImageUrl($quickrfq->getProductId()),
            'quantity' => $quickrfq->getQuantity(),
            'price_per_product' => [
                'value' => (float)$quickrfq->getPricePerProduct(),
                'currency' => $quickrfq->getStoreCurrencyCode()
            ],
            'status' => $quickrfq->getStatus(),
            'comment' => $quickrfq->getComment(),
            'date_need_quote' => $quickrfq->getDateNeedQuote(),
            'create_date' => $quickrfq->getCreateDate(),
            'update_date' => $quickrfq->getUpdateDate(),
            'coupon_code' => $quickrfq->getCouponCode(),
            'attributes' => $quickrfq->getAttributes(),
            'info_buy_request' => $quickrfq->getInfoBuyRequest(),
            'store_currency_code' => $quickrfq->getStoreCurrencyCode(),
            'admin_quantity' => $quickrfq->getAdminQuantity(),
            'admin_price' => [
                'value' => (float)$quickrfq->getAdminPrice(),
                'currency' => $quickrfq->getStoreCurrencyCode()
            ],
            'store_id' => $quickrfq->getStoreId(),
            'user_id' => $quickrfq->getUserId(),
            'user_name' => $quickrfq->getUserName(),
            'expiry' => $quickrfq->getExpiry(),
            'seller_id' => $quickrfq->getSellerId(),
            'seller_name' => $quickrfq->getSellerName(),
            'website_id' => $quickrfq->getebsiteId(),
            'attachments' => $attachments,
            'messages' => $messages
        ];
    }

    public function getProductImageUrl(int $id): ?string
    {
        try {
            $product = $this->productFactory->create()->load($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->imageHelper
            // ->init($product, 'product_thumbnail_image')
            ->init($product, 'product_page_image_small', ['type' => 'small_image'])
            ->getUrl();
    }
}
