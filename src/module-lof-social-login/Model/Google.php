<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SocialLogin
 *
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\SocialLogin\Model;

use Lof\SocialLogin\Helper\Github\Data as DataHelper;

class Google
{
    protected $dataHelper;
    protected $scopeConfig;
    protected $storeManagerInterface; 
    
    public function __construct(
        DataHelper $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->dataHelper = $dataHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->scopeConfig = $scopeConfig;
    }

    public function getBaseUrl()
    {
        $baseurl = $this->storeManagerInterface
            ->getStore()
            ->getBaseUrl();

        return $baseurl.'lofsociallogin/google/callback';
    }

    public function getPwaBaseUrl()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $pwaBaseurl = $this->scopeConfig->getValue('pwa/pwa_settings/pwa_base_url', $storeScope);;
        $websiteCode = $this->storeManagerInterface
            ->getStore()->getWebsite()->getCode();
        return $pwaBaseurl.'/lofsociallogin/google/callback';
    }
}
