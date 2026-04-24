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

namespace Ves\Trackorder\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Url;

class Generate extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_helperData;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Ves\Trackorder\Helper\Data $helperData
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ves\Trackorder\Helper\Data $helperData,
        \Magento\Sales\Model\OrderFactory $orderFactory
        ) {
        parent::__construct($context);
        $this->_helperData = $helperData;
        $this->_orderFactory = $orderFactory;
    }

    public function runGenerate($orderStatus = ""){
        $limit = $this->_helperData->getConfig('generate_track_code/limit');
        $limit = $limit ? (int)$limit: 50;
        $secret_key = $this->_helperData->getConfig('trackorder_general/secret_key');
        $use_longcode = $this->_helperData->getConfig('trackorder_general/use_longcode');
        $collection = $this->_orderFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter("track_link", array('null' => true))
                                            ->setPageSize((int)$limit)
                                            ->setCurpage(1)
                                            ->setOrder("entity_id", "ASC");
        if($orderStatus && $orderStatus !== "all"){
            $collection->addFieldToFilter("status", $orderStatus);
        }
        //return $collection->getSelect();
        if($collection->getSize()){
            foreach($collection as $order){
                $trackLink = substr(md5(microtime()), rand(0, 26), 6);
                if($use_longcode) {
                    $trackLink = sha1($trackLink.$secret_key);
                } else {
                    $trackLink = $this->_helperData->generateTrackcode();
                }
                $order->setTrackLink($trackLink);
                $order->getResource()->saveAttribute($order, "track_link");
                $order->save();
            }
        }
    }
}