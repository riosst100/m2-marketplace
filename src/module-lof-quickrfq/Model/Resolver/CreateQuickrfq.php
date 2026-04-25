<?php
namespace Lof\Quickrfq\Model\Resolver;

use Lof\Quickrfq\Model\QuickrfqFactory;
use Lof\Quickrfq\Model\MessageFactory;
use Lof\Quickrfq\Controller\FileProcessor;
use Lof\Quickrfq\Model\Attachment\UploadHandler;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Lof\Quickrfq\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class CreateQuickrfq implements ResolverInterface
{
    const XML_PATH_EMAIL_RECIPIENT = 'quickrfq/email/recipient';
    const XML_PATH_EMAIL_TEMPLATE_CUSTOMER = 'quickrfq/email/template_customer';
    const XML_PATH_EMAIL_TEMPLATE_ADMIN = 'quickrfq/email/template';

    protected $quickrfqFactory;
    protected $messageFactory;
    protected $fileProcessor;
    protected $uploadHandler;
    protected $productRepository;
    protected $storeManager;
    protected $helper;
    protected $customerSession;
    protected $customerModel;
    protected $_eventManager;
    protected $scopeConfig;

    public function __construct(
        QuickrfqFactory $quickrfqFactory,
        MessageFactory $messageFactory,
        FileProcessor $fileProcessor,
        UploadHandler $uploadHandler,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Data $helper,
        Session $customerSession,
        Customer $customerModel,
        ManagerInterface $_eventManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->quickrfqFactory = $quickrfqFactory;
        $this->messageFactory = $messageFactory;
        $this->fileProcessor = $fileProcessor;
        $this->uploadHandler = $uploadHandler;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->_eventManager = $_eventManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $input = $args['input'] ?? [];
        if (empty($input['product_id']) || empty($input['quantity'])) {
            throw new GraphQlInputException(__('Product ID and Quantity are required.'));
        }

        $customerId = 0;
        $customerEmail = $input['customer_email'] ?? '';
        $customerName = $input['customer_name'] ?? '';
        $customerPhone = $input['customer_phone'] ?? '';
        $comment = strip_tags($input['comment'] ?? '');
        $comment = $this->helper->xss_clean($comment);

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $customerName = $this->customerSession->getCustomer()->getName();
            $customerEmail = $this->customerSession->getCustomer()->getEmail();
        } else {
            if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                throw new GraphQlInputException(__('Please enter a valid email.'));
            }
            $customer = $this->customerModel->setWebsiteId(1)->loadByEmail($customerEmail);
            $customerId = $customer->getId() ?: 0;
        }

        try {
            $product = $this->productRepository->getById((int)$input['product_id']);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Product does not exist.'));
        }

        try {
            $model = $this->quickrfqFactory->create();
            $model->setData([
                'contact_name'      => $customerName,
                'email'             => $customerEmail,
                'phone'             => $customerPhone,
                'product_id'        => $input['product_id'],
                'quantity'          => $input['quantity'],
                'date_need_quote'   => $input['date_need_quote'] ?? '',
                'comment'           => $comment,
                'customer_id'       => $customerId,
                'price_per_product' => $input['price_per_product'] ?? null,
                'store_id'          => $this->storeManager->getStore()->getId(),
                'website_id'        => $this->storeManager->getStore()->getWebsiteId(),
                'store_currency_code' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
                'product_sku'       => $product->getSku(),
                'attributes'        => $input['attributes'] ?? null,
                'info_buy_request'  => $input['info_buy_request'] ?? null,
            ])->save();

            // Save message
            $message = $this->messageFactory->create();
            $message->setData([
                'quickrfq_id' => $model->getQuickrfqId(),
                'message'     => $comment,
                'customer_id' => $customerId,
                'is_main'     => 1,
            ])->save();

            // Handle attachments if any
            // if (!empty($input['files'])) {
            //     foreach ($input['files'] as $file) {
            //         $this->uploadHandler->process([
            //             'name' => $file['file_name'] ?? '',
            //             'tmp_name' => $file['file_path'] ?? '',
            //             'type' => $file['file_type'] ?? '',
            //         ], $model->getQuickrfqId());
            //     }
            // }
            if (!empty($input['files']) && is_array($input['files'])) {
                foreach ($input['files'] as $file) {
                    $tmpFile = $this->saveBase64ToTmp($file);
                    // dd($tmpFile);
                    $this->uploadHandler->processPwa(
                        $file,
                        $model->getQuickrfqId()
                    );

                    // cleanup
                    if (file_exists($tmpFile['tmp_name'])) {
                        @unlink($tmpFile['tmp_name']);
                    }
                }
            }

            /** 📨 Email & Event Dispatch */
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $templateForAdmin = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE_ADMIN, $storeScope);
            $templateForCustomer = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE_CUSTOMER, $storeScope);
            $emailRecipientAdmin = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

            $data = [
                'contact_name' => $customerName,
                'email' => $customerEmail,
                'customer_email' => $customerEmail,
                'phone' => $customerPhone,
                'product_id' => $input['product_id'],
                'quantity' => $input['quantity'],
                'comment' => $comment,
                'customer_id' => $customerId,
                'is_admin' => false,
                'product_name' => $product->getName(),
                'receiver_name' => $emailRecipientAdmin
            ];

            // Send customer email
            // $this->helper->sendEmail($data, $customerEmail, $templateForCustomer);

            // Send admin email
            $data['is_admin'] = true;
            $data['receiver_name'] = $customerName;
            // $this->helper->sendEmail($data, $emailRecipientAdmin, $templateForAdmin);

            // Dispatch event same as controller
            $this->_eventManager->dispatch(
                'lof_quickrfq_save_after',
                [
                    'data' => $data,
                    'controller' => $this,
                    'template_admin' => $templateForAdmin,
                    'template_customer' => $templateForCustomer,
                    'model' => $model
                ]
            );

            return [
                'success' => true,
                'message' => __('Your request has been sent!'),
                'quickrfq_id' => $model->getQuickrfqId()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'quickrfq_id' => null
            ];
        }
    }

    private function saveBase64ToTmp(array $file): array
    {
        if (empty($file['base64']) || empty($file['file_name'])) {
            throw new GraphQlInputException(__('Invalid file data.'));
        }

        $base64 = $file['base64'];

        // Remove base64 header if exists
        if (strpos($base64, 'base64,') !== false) {
            $base64 = substr($base64, strpos($base64, 'base64,') + 7);
        }

        $content = base64_decode($base64);
        if ($content === false) {
            throw new GraphQlInputException(__('Unable to decode file.'));
        }

        $tmpDir = BP . '/var/tmp/quickrfq/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $fileName = uniqid('rfq_') . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file['file_name']);
        $tmpPath = $tmpDir . $fileName;

        file_put_contents($tmpPath, $content);

        return [
            'name'     => $fileName,
            'tmp_name'=> $tmpPath,
            'type'     => $file['file_type'] ?? 'application/octet-stream',
            'size'     => filesize($tmpPath)
        ];
    }
}
