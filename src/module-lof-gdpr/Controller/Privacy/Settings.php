<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Controller\Privacy;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Lof\Gdpr\Controller\AbstractPrivacy;

class Settings extends AbstractPrivacy implements HttpGetActionInterface
{
    protected function executeAction()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
