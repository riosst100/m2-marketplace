<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\Erase;

use InvalidArgumentException;
use Lof\Gdpr\Api\Data\ActionContextInterface;
use Lof\Gdpr\Api\Data\ActionResultInterface;
use Lof\Gdpr\Api\EraseEntityManagementInterface;
use Lof\Gdpr\Model\Action\AbstractAction;
use Lof\Gdpr\Model\Action\ResultBuilder;

final class ExecuteAction extends AbstractAction
{
    /**
     * @var EraseEntityManagementInterface
     */
    private $eraseEntityManagement;

    public function __construct(
        ResultBuilder $resultBuilder,
        EraseEntityManagementInterface $eraseEntityManagement
    ) {
        $this->eraseEntityManagement = $eraseEntityManagement;
        parent::__construct($resultBuilder);
    }

    public function execute(ActionContextInterface $actionContext): ActionResultInterface
    {
        $eraseEntity = ArgumentReader::getEntity($actionContext);

        if ($eraseEntity === null) {
            throw new InvalidArgumentException('Argument "entity" is required.');
        }

        return $this->createActionResult(
            [ArgumentReader::ERASE_ENTITY => $this->eraseEntityManagement->process($eraseEntity)]
        );
    }
}
