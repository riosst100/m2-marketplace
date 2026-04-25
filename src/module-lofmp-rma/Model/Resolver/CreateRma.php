<?php
namespace Lofmp\Rma\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lofmp\Rma\Helper\Data as DataHelper;
use Lofmp\Rma\Helper\Help as HelpHelper;
use Lofmp\Rma\Model\RmaFactory;
use Lofmp\Rma\Model\ItemFactory;
use Magento\Sales\Model\OrderFactory;
use Lofmp\Rma\Api\Repository\RmaRepositoryInterface;
use Lofmp\Rma\Api\Repository\MessageRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Registry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CreateRma implements ResolverInterface
{
    protected $dataHelper;
    protected $helper;
    protected $rmaFactory;
    protected $itemFactory;
    protected $orderFactory;
    protected $eventManager;
    protected $registry;
    protected $messageRepository;
    protected $rmaRepository;
    protected $customerRepository;
    protected $productRepository;

    protected $_orderItems = [];
    protected $_childRma = [];

    public function __construct(
        DataHelper $dataHelper,
        HelpHelper $helper,
        RmaFactory $rmaFactory,
        ItemFactory $itemFactory,
        OrderFactory $orderFactory,
        EventManager $eventManager,
        RmaRepositoryInterface $rmaRepository,
        MessageRepositoryInterface $messageRepository,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->helper = $helper;
        $this->rmaFactory = $rmaFactory;
        $this->itemFactory = $itemFactory;
        $this->orderFactory = $orderFactory;
        $this->eventManager = $eventManager;
        $this->registry = $registry;
        $this->rmaRepository = $rmaRepository;
        $this->messageRepository = $messageRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = (int)$context->getUserId();
        $customer = $this->customerRepository->getById($customerId);

        $input = $args['input'] ?? [];
        $orderIncrementId = $input['order_number'];
        $orderId = $this->getOrderIdByIncrementId($orderIncrementId);
        $input['order_id'] = $orderId;
        if (empty($orderId) || empty($input['items'])) {
            throw new GraphQlInputException(__('Invalid RMA input data.'));
        }

        if (!$this->dataHelper->validate($input)) {
            throw new GraphQlInputException(__('Invalid RMA form data.'));
        }

        $orderId = (int)$orderId;
        $order = $this->orderFactory->create()->load($orderId);

        if (!$order->getId() || $order->getCustomerId() != $customerId) {
            throw new GraphQlAuthorizationException(__('Order does not belong to this customer.'));
        }

        try {
            // Create RMA parent
            $rma = $this->rmaFactory->create();

            foreach ($input['items'] as $itemId => $itemData) {
                $itemData['order_item_id'] = base64_decode($itemData['order_item_id']);
                $input['items'][$itemId] = $itemData;
            }

            $rmaData = $input;

            if (!empty($rmaData['street2'])) {
                $rmaData['street'] .= "\n" . $rmaData['street2'];
                unset($rmaData['street2']);
            }

            $rma->setStoreId($order->getStoreId());
            $rma->setCustomerId($customerId);
            $rma->setStatusId($this->helper->getConfig($order->getStoreId(), 'rma/general/default_status'));
            $rma->setParentRmaId(0);
            $rma->addData($rmaData);
            // dd($rmaData);
            $rma->save();

            $this->registry->register('current_rma', $rma);
            $parentRmaId = $rma->getId();

            // Create RMA items
            foreach ($input['items'] as $itemId => $itemData) {
                // dd($itemData);
                $orderItem = $order->getItemById($itemData['order_item_id']);
                if (!$orderItem) {
                    continue;
                }
                $sellerId = $orderItem->getLofSellerId();
                $rma->setSellerId($sellerId);
                $this->rmaRepository->save($rma);

                $productId = $orderItem->getProductId() ?: $this->productRepository->get($orderItem->getSku())->getId();

                $itemData['rma_id'] = $parentRmaId;
                $itemData['order_id'] = $orderId;
                $itemData['order_item_id'] = $itemData['order_item_id'];
                $itemData['product_id'] = $productId;

                $item = $this->itemFactory->create();
                $item->addData($itemData);
                $item->save();
            }

            // Optional first message (reply)
            if (!empty($input['reply'])) {
                $message = $this->messageRepository->create();
                $message->setRmaId($parentRmaId)
                    ->setText($input['reply'])
                    ->setIsCustomerNotified(1)
                    ->setIsVisibleInFrontend(1)
                    ->setCustomerId($customerId)
                    ->setCustomerName($customer->getFirstname() . ' ' . $customer->getLastname());
                $this->messageRepository->save($message);

                $rma->setLastReplyName($customer->getFirstname() . ' ' . $customer->getLastname());
                $this->rmaRepository->save($rma);

                $this->eventManager->dispatch(
                    'rma_add_message_after',
                    ['rma' => $rma, 'message' => $message, 'user' => $customer, 'params' => $input]
                );
            }

            $this->eventManager->dispatch('rma_update_rma_after', ['rma' => $rma, 'user' => $customer]);

            return [
                'success' => true,
                'message' => __('RMA request created successfully.'),
                'rma_id' => $rma->getId(),
                'status_id' => $rma->getStatusId(),
            ];

        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    public function getOrderIdByIncrementId($incrementId)
    {
        // Create an order model instance
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        // Check if order exists
        if (!$order->getId()) {
            return null;
        }

        return $order->getId();
    }
}
