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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * Group Collection
     */
    protected $_groupCollection;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * BaseWidget config node per website
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

     /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
     protected $_coreRegistry;

     /** @var UrlBuilder */
     protected $actionUrlBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

     /**
     * @var \Ves\Trackorder\Helper\Trackcode
     */
    protected $_trackcode;

    /**
     * @var array
     */
    protected $_orders = [];

    /**
     * @var bool
     */
    protected $_flag_trackcode = false;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Framework\Registry                $registry
     * @param Url                                        $actionUrlBuilder
     * @param \Ves\Trackorder\Helper\Trackcode           $trackcode
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Url $actionUrlBuilder,
        \Ves\Trackorder\Helper\Trackcode $trackcode
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_filterProvider = $filterProvider;
        $this->_coreRegistry = $registry;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->_objectManager = $objectManager;
        $this->_trackcode = $trackcode;
    }

	 /**
     * Return brand config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
     public function getConfig($key, $default="", $group = "vestrackorder", $store = null)
     {
        $store = $this->_storeManager->getStore($store);
        //$websiteId = $store->getWebsiteId();

        $result = $this->scopeConfig->getValue(
            $group.'/'.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        if($result == "") {
            $result = $default;
        }
        return $result;
    }

    /**
     * filter string
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
    	$html = $this->_filterProvider->getPageFilter()->filter($str);
    	return $html;
    }

    /**
     * get track order url
     *
     * @return string
     */
    public function getTrackorderUrl()
    {
        return $this->actionUrlBuilder->getDirectUrl( "trackorder/index/index" );
    }

    /**
     * get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        $storeMediaUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $storeMediaUrl;
    }

    /**
     * Generate track code
     *
     * @return string
     */
    public function generateTrackcode()
    {
        if (!$this->_flag_trackcode) {
            $chunks = $this->getConfig("trackorder_general/chunks",1);
            $letters = $this->getConfig("trackorder_general/letters",9);
            $separate_text = $this->getConfig("trackorder_general/separate_text","-");

            $this->_trackcode->numberChunks = (int)$chunks;
            $this->_trackcode->numberLettersPerChunk = (int)$letters;
            $this->_trackcode->separateChunkText = (int)$separate_text;
        }
        $serial_number = $this->_trackcode->generate();
        return $serial_number;
    }

    /**
     * get print order url
     *
     * @param \Magento\Sales\Api\Data\OrderInterface|mixed|object $order
     * @return string
     */
    public function getPrintOrderUrl($order)
    {
        return $this->actionUrlBuilder->getDirectUrl('vestrackorder/print', ['_query' =>['order_id' => $order->getId()]]);
    }

    /**
     * Get current order by increment id
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface|mixed|object
     */
    public function getCurrentOrder($incrementId)
    {
        if(!isset($this->_orders[$incrementId])) {
            $this->_orders[$incrementId] = $this->getOrderRecord($incrementId);
        }
        return $this->_orders[$incrementId];
    }

    /**
     * Get order track code
     *
     * @param string $incrementId
     * @return string
     */
    public function getTrackcode($incrementId)
    {
        if($order = $this->getCurrentOrder($incrementId)){
            return $order->getTrackLink();
        }
        return "";
    }

    /**
     * Get track link
     *
     * @param string $incrementId
     * @return string
     */
    public function getTracklink($incrementId)
    {
        if($order = $this->getCurrentOrder($incrementId)){
            $track_url_orig = $order->getTrackLink();
            $link_trackorder = $this->_storeManager->getStore()->getBaseUrl().'vestrackorder/track/'.$track_url_orig;
            return $link_trackorder;
        }
        return "";
    }

    /**
     * Get qr code
     *
     * @param string $incrementId
     * @return string
     */
    public function getQrcode($incrementId)
    {
        if($order = $this->getCurrentOrder($incrementId)){
            $track_url_orig = $order->getTrackLink();
            $track_url = str_replace(array(" ",":","=","&","?"), array("+","%3A","%3D","%26","%3F"), $track_url_orig);
            $qrlink = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=".$track_url."&choe=UTF-8";
            return $qrlink;
        }
        return "";
    }

    /**
     * Get qr code track link
     *
     * @param string $incrementId
     * @return string
     */
    public function getQrcodeTracklink($incrementId)
    {
        if($order = $this->getCurrentOrder($incrementId)){
            $track_url_orig = $order->getTrackLink();
            $link_trackorder = $this->_storeManager->getStore()->getBaseUrl().'vestrackorder/track/'.$track_url_orig;
            $track_url = str_replace(array(" ",":","=","&","?"), array("+","%3A","%3D","%26","%3F"), $link_trackorder);
            $qrlink = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=".$link_trackorder."&choe=UTF-8";
            return $qrlink;
        }
        return "";
    }

    /**
     * Get order by increment_id and store_id
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws InputException
     */
    private function getOrderRecord($incrementId)
    {
        $records = $this->orderRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId)
                ->create()
        );
        if ($records->getTotalCount() < 1) {
            throw new InputException(__($this->inputExceptionMessage));
        }
        $items = $records->getItems();
        return array_shift($items);
    }
}
