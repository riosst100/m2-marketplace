<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\PerformedBy;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Lof\Gdpr\Model\Action\PerformedByInterface;

final class Customer implements PerformedByInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var string
     */
    private $attributeName;

    public function __construct(
        Session $customerSession,
        string $attributeName = CustomerInterface::EMAIL
    ) {
        $this->customerSession = $customerSession;
        $this->attributeName = $attributeName;
    }

    public function get(): string
    {
        return $this->customerSession->isLoggedIn()
            ? $this->customerSession->getCustomer()->getData($this->attributeName)
            : '';
    }
}
