<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ves\Trackorder\Block\Adminhtml\Order\View; 

/**
 * Adminhtml order abstract block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{ 
    public function getTrackLinkUrl()
    { 
        if($this->getOrder()){ 
            if ($this->getOrder()->getTrackLink()) {
                $label = $this->getOrder()->getTrackLink();
                // $url = $this->getBaseUrl('vestrackorder/track/'.$label);
                return '<b>' . $label . '</b>';
            } 
              
        }
        return '';
    }

}
