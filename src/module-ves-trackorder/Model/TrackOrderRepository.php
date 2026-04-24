<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ves\Trackorder\Model;

use Ves\Trackorder\Api\TrackOrderRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\OrderRepositoryFactory;

class TrackOrderRepository implements TrackOrderRepositoryInterface
{
    protected $registryOrderId = [];
    protected $registryOrderCode = [];

    protected $orderFactory;

    protected $dataObjectHelper;


    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $resource;

    protected $orderRepositoryFactory;

    private $storeManager;

    protected $invoiceRepositoryInterfaceFactory;

    /**
     * @var \Ves\Trackorder\Helper\Data
     */
    protected $_trackorderHelper;

    protected $_inlineTranslation; 
    protected $_transportBuilder;
    protected $_file;


    /**
     * @param OrderFactory $orderFactory
     * @param \Ves\Trackorder\Helper\Data $data
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\InvoiceRepositoryInterfaceFactory $invoiceRepositoryInterfaceFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Ves\Trackorder\Model\Mail\UploadTransportBuilder $transportBuilder
     * @param OrderRepositoryFactory $orderRepositoryFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Ves\Trackorder\Helper\Data $data,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magento\Sales\Api\InvoiceRepositoryInterfaceFactory $invoiceRepositoryInterfaceFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
        \Ves\Trackorder\Model\Mail\UploadTransportBuilder $transportBuilder,
        OrderRepositoryFactory $orderRepositoryFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->_trackorderHelper = $data;
        $this->orderRepositoryFactory = $orderRepositoryFactory;
        $this->storeManager = $storeManager;
        $this->invoiceRepositoryInterfaceFactory = $invoiceRepositoryInterfaceFactory;
        $this->fileSystem = $fileSystem;
        $this->_file = $file;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function track(
        string $order_id = "", 
        string $email_address = "", 
        $code = ""
    )
    {
        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled')){
            throw new NoSuchEntityException(__('The function is not available.'));
        }
        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled_guest_api')){
            throw new NoSuchEntityException(__('The function is not available for anonymous.'));
        }
        if((!$order_id || !$email_address) && !$code) {
            throw new NoSuchEntityException(__('Required input order_id, email_address or tracking code.'));
        }
        $post = [
            "order_id" => $order_id,
            "email_address" => $email_address
        ];
        $order = $this->initOrder($post, $code);
        if ($order && $order->getId()) {
            return $order;
        }else {
            $customMessage = $this->_trackorderHelper->getConfig('trackorder_general/custom_message');
            if(!$customMessage){
                $customMessage = 'Order Not Found. Please try again later';
            }
            throw new NoSuchEntityException(__($customMessage));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function trackMyOrder(
        int $customerId,
        string $order_id = "",
        $code = ""
    )
    {
        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled')){
            throw new NoSuchEntityException(__('The function is not available.'));
        }
        if(!$order_id && !$code) {
            throw new NoSuchEntityException(__('Required input order_id or tracking code.'));
        }
        $post = [
            "order_id" => $order_id,
            "customer_id" => $customerId
        ];
        $order = $this->initOrder($post, $code);
        if ($order && $order->getId()) {
            return $order;
        }else {
            $customMessage = $this->_trackorderHelper->getConfig('trackorder_general/custom_message');
            if(!$customMessage){
                $customMessage = 'Order Not Found. Please try again later';
            }
            throw new NoSuchEntityException(__($customMessage));
        }
    }


    /**
     * {@inheritdoc}
     */
    public function send(
        string $invoiceId,
        string $email_recipient,
        string $name = "",
        string $order_id = "",
        string $email_address = "",
        $code = ""
    )
    {
        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled')){
            throw new NoSuchEntityException(__('The function is not available.'));
        }
        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled_guest_api')){
            throw new NoSuchEntityException(__('The function is not available for anonymous.'));
        }
        if((!$order_id || !$email_address) && !$code) {
            throw new NoSuchEntityException(__('Required input order_id, email_address or tracking code.'));
        }
        if(!$invoiceId || !$email_recipient) {
            throw new NoSuchEntityException(__('Required input invoiceId and email_receipient.'));
        }
        
        $post = [
            "order_id" => $order_id,
            "email_address" => $email_address
        ];
        $order = $this->initOrder($post, $code);

        if ($order && $order->getId()) {
            $invoice = $this->invoiceRepositoryInterfaceFactory->create()->get($invoiceId);
            $invoice_increment = $invoice->getIncrementId();
            $fileName = 'Invoice_#'.$invoice_increment.'.pdf';
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($order->getData());     
            $track_url_orig = $order->getTrackLink();
            $track_url = str_replace(array(" ",":","=","&","?"), array("+","%3A","%3D","%26","%3F"), $track_url_orig); 
            $qrlink = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=".$track_url."&choe=UTF-8";  
            $link_trackorder = $this->storeManager->getStore()->getBaseUrl().'vestrackorder/track/'.$track_url_orig;

            $this->_inlineTranslation->suspend(); 
            $transport = $this->_transportBuilder->setTemplateIdentifier($this->_trackorderHelper->getConfig('email_settings/order_template'))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['data' => $postObject, 'order' => $order, 'qrlink' => $qrlink, 'track_url' => $link_trackorder, 'name' => $name, 'email'=> $email_recipient]) 
            ->setFrom($this->_trackorderHelper->getConfig('email_settings/sender_email_identity'))
            ->addTo($email_recipient, $name); 

            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $mediaRootDir = $mediaDirectory->getAbsolutePath(); 
            $pdfFilePath = $mediaRootDir . $fileName;
            
            if($this->_trackorderHelper->getConfig('email_settings/allow_attach_file')){
                if ($this->_file->isExists($pdfFilePath))  {
                    $transport->addAttachmentFile($pdfFilePath, $fileName);
                    $this->_file->deleteFile($pdfFilePath);
                }
            }
            $transport = $transport->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();  

            if ($this->_file->isExists($pdfFilePath))  {
                $this->_file->deleteFile($pdfFilePath);
            }
            return true;
        }else {
            $customMessage = $this->_trackorderHelper->getConfig('trackorder_general/custom_message');
            if(!$customMessage){
                $customMessage = 'Order Not Found. Please try again later';
            }
            throw new NoSuchEntityException(__($customMessage));
        }
    }

    protected function initOrder($data = array(), $trackcode = "") {
        $current_order = false;
        if ($data || $trackcode) {
            $orderId = isset($data["order_id"])?$data["order_id"]:'';
            $email = isset($data["email_address"])?$data["email_address"]:'';
            $customer_id = isset($data["customer_id"])?$data["customer_id"]:'';
            $order = false;
            if($trackcode) {
                if (!isset($this->registryOrderCode[$trackcode])) {
                    $this->registryOrderCode[$trackcode] = $this->orderFactory->create()->loadByAttribute('track_link', $trackcode);
                }
                if($this->registryOrderCode[$trackcode] && $this->registryOrderCode[$trackcode]->getId()){
                    $customerEmail = $this->registryOrderCode[$trackcode]->getCustomerEmail();
                    $customerId = $this->registryOrderCode[$trackcode]->getCustomerId();
                    if(($customer_id && $customer_id == $customerId) || !$customer_id) {
                        $order = $this->orderRepositoryFactory->create()->get($this->registryOrderCode[$trackcode]->getId());
                    }
                }
            } else if($orderId) {
                if (!isset($this->registryOrderId[$orderId])) {
                    $this->registryOrderId[$orderId] = $this->orderFactory->create()->loadByIncrementId($orderId);
                }
                if($this->registryOrderId[$orderId] && $this->registryOrderId[$orderId]->getId()){
                    $customerEmail = $this->registryOrderId[$orderId]->getCustomerEmail();
                    $customerId = $this->registryOrderId[$orderId]->getCustomerId();
                    if(($customer_id && $customer_id == $customerId) || ($email && $email == $customerEmail)) {
                        $order = $this->orderRepositoryFactory->create()->get($this->registryOrderId[$orderId]->getId());
                    }
                }
            }
            $current_order = $order;
        }
        return $current_order;
    }
}

