<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_AgeVerification
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\AgeVerification\Plugin\Model\App\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;

class ContextPlugin
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(Session $customerSession, HttpContext $httpContext)
    {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Set customer dob and customer session id to HTTP context
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            'customer_id',
            $this->customerSession->getCustomerId(),
            false
        );

        $this->httpContext->setValue(
            'dob',
            $this->customerSession->getCustomer()->getDob(),
            false
        );

        return $proceed($request);
    }
}
