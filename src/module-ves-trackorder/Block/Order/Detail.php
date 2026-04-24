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

class Detail extends \Magento\Framework\View\Element\Template
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

	/**
     * Render block HTML
     *
     * @return string
     */
	protected function _toHtml()
	{
		$load_full_detail = $this->_coreRegistry->registry('load_full_detail');
		if($load_full_detail) {
			$this->setTemplate("Ves_Trackorder::details_full.phtml");
		}
		return parent::_toHtml();
	}
}