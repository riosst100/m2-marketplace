<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Trackorder
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Trackorder\Controller\Index;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Track extends \Magento\Framework\App\Action\Action {

     /**
     * @var \Magento\Framework\App\RequestInterface
     */
     protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Ves\Trackorder\Helper\Data
     */
    protected $_trackorderHelper;

    /**
     * @var \Ves\Trackorder\Helper\Guest
     */
    protected $_trackorderGuestHelper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_orderModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    protected $_layout;

    /**
     * @param Context                                             $context              
     * @param \Magento\Store\Model\StoreManager                   $storeManager         
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory    
     * @param \Ves\Trackorder\Helper\Data                              $trackorderHelper
     * @param \Magento\Sales\Model\Order                              $order     
     * @param \Magento\Framework\Registry                              $registry           
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory 
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ves\Trackorder\Helper\Data $trackorderHelper,
        \Ves\Trackorder\Helper\Guest $trackorderGuestHelper,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
        ) {
        $this->resultPageFactory = $resultPageFactory; 
        $this->_trackorderHelper = $trackorderHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_orderModel = $order;
        $this->_coreRegistry = $registry;
        $this->_layout = $layout;
        parent::__construct($context);
                $this->_trackorderGuestHelper = $trackorderGuestHelper;
    }

    public function initOrder($data = array(), $trackcode = "") {
        $current_order = false;
        if ($data || $trackcode) {
            $orderId = isset($data["order_id"])?$data["order_id"]:'';
            $email = isset($data["email_address"])?$data["email_address"]:'';
            $empty_order = $this->_orderModel;
            $order = false;
            if($trackcode) {
                $order = $this->_orderModel->loadByAttribute('track_link', $trackcode);
                $this->_coreRegistry->register('current_order', $order);
            } else {
                $order = $this->_orderModel->loadByIncrementId($orderId);
                $cEmail = $order->getCustomerEmail();
                if ($cEmail == trim($email)) {
                    $this->_coreRegistry->register('current_order', $order);
                } else {
                    if($data = $order->getData()){
                        foreach($data as $key=>$val) {
                            $order->setData($key, "");
                        }
                    }
                    $order->setId(0);
                    $order->setIncrementId(0);
                    $this->_coreRegistry->register('current_order', $order);
                }
            }
            $current_order = $order;
        }
        return $current_order;
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        if(!$this->_trackorderHelper->getConfig('trackorder_general/enabled')){
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $post = $this->getRequest()->getPost();
        $trackcode = $this->getRequest()->getParam('code'); 
        $request = $this->getRequest();
        $route = $this->_trackorderHelper->getConfig('trackorder_general/route');
        $route = $route?$route:'vestrackorder';
        if ($post || $trackcode) {
            $is_ajax = isset($post['ajax'])?$post['ajax']:false;
            try {
                $order = $this->initOrder($post, $trackcode);
                if ($order && $order->getId()) {
                    $result = $this->_trackorderGuestHelper->loadValidOrder($order->getIncrementId(), $trackcode);
                    $this->_view->loadLayout();
                    if(!$trackcode || $is_ajax){
                        $html = $this->_layout->getBlock('order.details')->toHtml();
                        $this->messageManager->addSuccess(__('We found a order detail #').$order->getIncrementId());
                        $this->getResponse()->setBody($html);
                        return;
                    } else {
                        $this->_coreRegistry->register('load_full_detail', 1);
                    }
                } else {
                    $customMessage = $this->_trackorderHelper->getConfig('trackorder_general/custom_message');
                    if($customMessage){
                        $this->messageManager->addError($customMessage);
                    } else {
                        $this->messageManager->addError(__('Order Not Found. Please try again later'));
                    }
                    $this->getResponse()->setBody($this->_layout->getMessagesBlock()->getGroupedHtml());
                    if($is_ajax){
                        return;
                    }else {
                        $this->_redirect($route);
                        return;
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Please Enter Order Detail.')
                    );
                $this->getResponse()->setBody($this->_layout->getMessagesBlock()->getGroupedHtml());
                if($is_ajax){
                    return;
                }else {
                    $this->_redirect($route);
                    return;
                }
            }
        } else {
            $this->_redirect('*/*/');
            return;
        }
        $page = $this->resultPageFactory->create();
        return $page;
    } 

}
