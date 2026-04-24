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

namespace Ves\Trackorder\Block\Order;

class Qrcode extends \Magento\Framework\View\Element\Template
{
	protected $_helper;
	protected $_coreRegistry;
	/**
	 * @param \Magento\Framework\View\Element\Template\Context
	 * @param \Ves\Trackorder\Helper\Data
	 * @param array
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Trackorder\Helper\Data $helper,
		\Magento\Framework\Registry $registry,
		array $data = []
		) {
		parent::__construct($context, $data);
		$this->_helper       = $helper;
		$this->_coreRegistry = $registry;
	}

	public function getConfig($key, $default = "") {
		$return = $this->_helper->getConfig($key);
		return $return?$return:$default;
	}
	public function getTrackLink() {
		$order = $this->_coreRegistry->registry('current_order');
		$track_code = $order->getTrackLink();
		$route = $this->_helper->getConfig("trackorder_general/route");
        $route = $route?$route:'vestrackorder';
        if($track_code) {
			return $this->getUrl($route."/track/".$track_code);
		}

		return "";
	}
	public function getTrackCode() {
		$order = $this->_coreRegistry->registry('current_order');
		return $order->getTrackLink();
	}
}