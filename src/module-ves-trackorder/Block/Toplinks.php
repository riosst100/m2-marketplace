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

class Toplinks extends \Magento\Framework\View\Element\Template
{
	/**
	 * @param \Magento\Framework\View\Element\Template\Context
	 * @param \Ves\Trackorder\Helper\Data
	 * @param array
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Trackorder\Helper\Data $helper,
		array $data = []
		) {
		parent::__construct($context, $data);
		$this->_helper       = $helper;
	}

	/**
     * Render block HTML
     *
     * @return string
     */
	protected function _toHtml()
	{	
		if(!$this->_helper->getConfig('trackorder_general/enabled')) return;
		if(!$this->_helper->getConfig('trackorder_general/toplinks')) return;
		$route = $this->_helper->getConfig("trackorder_general/route");
        $route = $route?$route:'vestrackorder';
		$link = '';
		$link_title = $this->getData("label");
		$link_title = $link_title?$link_title:__("Trackorder");
		if($route){
			$link .= '<li><a href="' . $this->getUrl($route) . '" title="'.$link_title.'">'.$link_title.'</a></li>';
		}
		return $link;
	}
}