<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ves\Trackorder\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Send extends \Magento\Framework\App\Action\Action
{
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */

    protected $_file;
    protected $_helper;
    protected $messageManager;
    protected $resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Ves\Trackorder\Model\Mail\UploadTransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ves\Trackorder\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Ves\Trackorder\Model\Mail\UploadTransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ves\Trackorder\Helper\Data $helper
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->messageManager = $messageManager;
        $this->_file = $file;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $invoiceId = $this->getRequest()->getPost('invoiceId');
        $email_recipient = $this->getRequest()->getPost('email_recipient');
        $name_recipient  = $this->getRequest()->getPost('name');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        if($order) {
            $invoice = $this->_objectManager->create(
                    \Magento\Sales\Api\InvoiceRepositoryInterface::class
                )->get($invoiceId);

            $invoice_increment = $invoice->getIncrementId();
            $fileName = 'Invoice_#'.$invoice_increment.'.pdf';
            // $emailTemplateVariables['message'] = 'This is a test message.';
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($order->getData());
            $track_url_orig = $order->getTrackLink();
            $track_url = str_replace(array(" ",":","=","&","?"), array("+","%3A","%3D","%26","%3F"), $track_url_orig);
            $qrlink = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=".$track_url."&choe=UTF-8";
            $link_trackorder = $this->_storeManager->getStore()->getBaseUrl().'vestrackorder/track/'.$track_url_orig;
            try
            {
                $orderData = [
                    'status' => $order->getStatusLabel(),
                    'created_at' => $order->getCreatedAtFormatted(2)
                ];
                $orderDataObject = new \Magento\Framework\DataObject();
                $orderDataObject->setData($orderData);
                $this->_inlineTranslation->suspend();
                $transport = $this->_transportBuilder->setTemplateIdentifier($this->_helper->getConfig('email_settings/order_template'))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject, 'order' => $orderDataObject, 'order_id' => $order->getId(), 'qrlink' => $qrlink, 'track_url' => $link_trackorder, 'name' => $name_recipient, 'email'=> $email_recipient])
                ->setFrom($this->_helper->getConfig('email_settings/sender_email_identity'))
                ->addTo($email_recipient, $name_recipient);

                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
                $mediaRootDir = $mediaDirectory->getAbsolutePath();
                $pdfFilePath = $mediaRootDir . $fileName;

                if($this->_helper->getConfig('email_settings/allow_attach_file')){
                    if ($this->_file->isExists($pdfFilePath))  {
                        $transport->addAttachmentFile($pdfFilePath, $fileName);
                        $this->_file->deleteFile($pdfFilePath);
                    }
                }
                $transport = $transport->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();

                $this->messageManager->addSuccess(__('Send email successful !'));

                if ($this->_file->isExists($pdfFilePath))  {
                    $this->_file->deleteFile($pdfFilePath);
                }
                die("ok");

            } catch (\Exception $e) {
                $error = true;
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.')
                );
            }
        } else {
            $error = true;
            $this->messageManager->addError(
                    __('We can\'t found the order info to send.')
                );
        }


    }
}
