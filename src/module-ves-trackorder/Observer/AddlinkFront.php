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

class AddlinkFront implements ObserverInterface
{

    protected $_request;
    private $_myhelper;

    private static $_handleTrackLinkCounter = 1;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ves\Trackorder\Helper\Data $helper
        ) {
        $this->_request = $context->getRequest();
        $this->_myhelper = $helper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $module_name = $this->_request->getModuleName();
        if ($module_name != 'multishipping') {
            if (self::$_handleTrackLinkCounter > 1) {
                return $this;
            }
            $secret_key = $this->_myhelper->getConfig('trackorder_general/secret_key');
            $use_longcode = $this->_myhelper->getConfig('trackorder_general/use_longcode');
            $order = $observer->getEvent()->getOrder();
            self::$_handleTrackLinkCounter++;
            if ($order->getTrackLink() == NULL) {
                $trackLink = substr(md5(microtime()), rand(0, 26), 6);
                if($use_longcode) {
                    $trackLink = sha1($trackLink.$secret_key);
                } else {
                    $trackLink = $this->_myhelper->generateTrackcode();
                }
                $order->setTrackLink($trackLink);
                $order->getResource()->saveAttribute($order, "track_link");
            }
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