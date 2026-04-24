<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Action\Export;

use InvalidArgumentException;
use Lof\Gdpr\Api\Data\ActionContextInterface;
use Lof\Gdpr\Api\Data\ActionResultInterface;
use Lof\Gdpr\Api\ExportEntityManagementInterface;
use Lof\Gdpr\Model\Action\AbstractAction;
use Lof\Gdpr\Model\Action\ArgumentReader;
use Lof\Gdpr\Model\Action\Export\ArgumentReader as ExportArgumentReader;
use Lof\Gdpr\Model\Action\ResultBuilder;

final class CreateAction extends AbstractAction
{
    /**
     * @var ExportEntityManagementInterface
     */
    private $exportEntityManagement;

    public function __construct(
        ResultBuilder $resultBuilder,
        ExportEntityManagementInterface $exportEntityManagement
    ) {
        $this->exportEntityManagement = $exportEntityManagement;
        parent::__construct($resultBuilder);
    }

    public function execute(ActionContextInterface $actionContext): ActionResultInterface
    {
        $entityId = ArgumentReader::getEntityId($actionContext);
        $entityType = ArgumentReader::getEntityType($actionContext);

        if ($entityId === null || $entityType === null) {
            throw new InvalidArgumentException('Arguments "entity_id" and "entity_type" are required.');
        }

        return $this->createActionResult(
            [
                ArgumentReader::ENTITY_TYPE => $this->exportEntityManagement->create(
                    $entityId,
                    $entityType,
                    ExportArgumentReader::getFileName($actionContext)
                )
            ]
        );
    }
}
