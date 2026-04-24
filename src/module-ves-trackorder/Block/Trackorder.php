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

namespace Ves\Trackorder\Block;

class Trackorder extends \Magento\Framework\View\Element\Template
{   
    /**
     * Helper data
     * @var \Ves\Trackorder\Helper\Data
     */
    protected $helper;
    /**
     * @var Url
     */
    protected $customerUrl;

 
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Block $blockModel,
        \Ves\Trackorder\Helper\Data $dataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->customerUrl = $customerUrl;
        $this->helper      = $dataHelper;

    }

	/**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page_title = __("Track Your Order");
        $order_id = $this->getRequest()->getParam('order_id');
        $email = $this->getRequest()->getParam('email');
        $this->assign("default_order_id", $order_id);
        $this->assign("default_email", $email);
        $this->pageConfig->getTitle()->set($page_title);
        
        return parent::_prepareLayout();
    }
    public function getTrackOrder()     
    { 
        if (!$this->hasData('trackorder')) {
            $this->setData('trackorder', $this->_coreRegistry->registry('current_order'));
        }
        return $this->getData('trackorder');
        
    }
    public function getTrackInfo($order)
    {
        $shipTrack = array();
        if ($order) {
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment){
                $increment_id = $shipment->getIncrementId();
                $tracks = $shipment->getTracksCollection();

                $trackingInfos=array();
                foreach ($tracks as $track){
                    $trackingInfos[] = $track->getNumberDetail();
                }
                $shipTrack[$increment_id] = $trackingInfos;
            }
        }
        return $shipTrack;
    }
    public function formatDeliveryDateTime($date, $time)
    {
        return $this->formatDeliveryDate($date) . ' ' . $this->formatDeliveryTime($time);
    }

    /**
     * Format given date in current locale without changing timezone
     *
     * @param string $date
     * @return string
     */
    public function formatDeliveryDate($date)
    {
        /* @var $locale Mage_Core_Model_Locale */
        $locale = $this->_localeResolver->getLocale();
        $format = $this->getDateFormat(\IntlDateFormatter::MEDIUM);
        return $locale->date(strtotime($date), \Zend_Date::TIMESTAMP, null, false)
        ->toString($format);
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getMediaUrl($image_path = "") {
        return $this->getBaseMediaUrl().$image_path;
    }

     /**
     * {@inheritdoc}
     */
     public function getDateFormat($type = \IntlDateFormatter::SHORT)
     {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            $type,
            \IntlDateFormatter::NONE
            ))->getPattern();
    }

    /**
     * Format given time [+ date] in current locale without changing timezone
     *
     * @param string $time
     * @param string $date
     * @return string
     */
    public function formatDeliveryTime($time, $date = null)
    {
        if (!empty($date)) {
            $time = $date . ' ' . $time;
        }
        
        /* @var $locale Mage_Core_Model_Locale */
        $locale = $this->_localeResolver->getLocale();
        
        $format = $this->getDateFormat(\IntlDateFormatter::SHORT);
        return $locale->date(strtotime($time), \Zend_Date::TIMESTAMP, null, false)
        ->toString($format);
    }

    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    public function getTrackOrderUrl(){
        $route = $this->helper->getConfig("trackorder_general/route");
        $route = $route?$route:'vestrackorder';
        return $this->getUrl($route.'/index/track');
    }
}