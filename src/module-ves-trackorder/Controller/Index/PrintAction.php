<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ves\Trackorder\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;  

class PrintAction extends \Magento\Framework\App\Action\Action
{    
	/**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
	protected $_fileFactory; 
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    
    protected $_file;

    protected $resultForwardFactory;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
    	\Magento\Backend\App\Action\Context $context,
    	\Magento\Framework\App\Response\Http\FileFactory $fileFactory,
    	\Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
    	\Magento\Framework\Filesystem\Driver\File $file, 
    	\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    	$this->_fileFactory = $fileFactory;
    	parent::__construct($context);
    	$this->resultForwardFactory = $resultForwardFactory;  
    	$this->_file = $file;
		$this->_storeManager = $storeManager;
    }

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {     
    	$invoiceId = $this->getRequest()->getParam('invoiceId');
    	if ($invoiceId) {
    		$invoice = $this->_objectManager->create(
    			\Magento\Sales\Api\InvoiceRepositoryInterface::class
    		)->get($invoiceId); 
    		if ($invoice) { 
    			$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
    			$mediaRootDir = $mediaDirectory->getAbsolutePath();
                $invoice_increment = $invoice->getIncrementId();
    			$fileName = 'Invoice_#'.$invoice_increment.'.pdf';
    			$pdfFilePath = $mediaRootDir . $fileName;
    			if ($this->_file->isExists($pdfFilePath))  { 
    				$this->_file->deleteFile($pdfFilePath);
    			} 
				$invoice->setStore($this->_storeManager->getStore());
				$invoice->setStoreId($this->_storeManager->getStore()->getId());
    			$pdf = $this->_objectManager->create(\Magento\Sales\Model\Order\Pdf\Invoice::class)->getPdf([$invoice]);
				$fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];
				$date = $this->_objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
				return $this->_fileFactory->create(
                    $fileName,
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
        	return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
