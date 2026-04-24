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
use Lof\Gdpr\Model\Action\Export\ArgumentReader as ExportArgumentReader;
use Lof\Gdpr\Model\Action\ResultBuilder;

final class ExportAction extends AbstractAction
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
        $exportEntity = ArgumentReader::getEntity($actionContext);

        if ($exportEntity === null) {
            throw new InvalidArgumentException('Argument "entity" is required.');
        }

        return $this->createActionResult(
            [ExportArgumentReader::EXPORT_ENTITY => $this->exportEntityManagement->export($exportEntity)]
        );
    }
}
