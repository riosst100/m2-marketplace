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

namespace Ves\Trackorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Checkstatus implements ObserverInterface
{

    protected $_request;
    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_resource;
    private static $_handleTrackLinkCounter = 1;
    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorizenet\Model\Directpost $payment
     * @param \Magento\Authorizenet\Model\Directpost\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resource
        ) {
        $this->_request = $context->getRequest();
        $this->_resource = $resource;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $params = $this->_getRequest()->getParam('groups');
        $configValue = $params['trackorder_general']['fields']['enabled']['value'];
        if ($configValue == 0) {
            $scope_type = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scope_id = 0;
            //Need save option for current store id
            $this->_resource->saveConfig('trackorder/trackorder_general/toplinks', "0", $scope_type, $scope_id);
            $this->_resource->saveConfig('trackorder/trackorder_general/topmenu', "0", $scope_type, $scope_id);
            $this->_resource->saveConfig('trackorder/trackorder_general/sendtrack_link', "0", $scope_type, $scope_id);
        }
        
    }

    /**
     * Shortcut to _getRequest
     *
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

}

?>