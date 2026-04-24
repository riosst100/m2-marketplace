<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Controller\Adminhtml\Privacy;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Lof\Gdpr\Api\ActionInterface;
use Lof\Gdpr\Api\Data\ExportEntityInterface;
use Lof\Gdpr\Controller\Adminhtml\AbstractAction;
use Lof\Gdpr\Model\Action\ArgumentReader;
use Lof\Gdpr\Model\Action\ContextBuilder;
use Lof\Gdpr\Model\Action\Export\ArgumentReader as ExportArgumentReader;
use Lof\Gdpr\Model\Config;

class Export extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Lof_Gdpr::customer_export';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ActionInterface
     */
    private $action;

    /**
     * @var ContextBuilder
     */
    private $actionContextBuilder;

    public function __construct(
        Context $context,
        Config $config,
        FileFactory $fileFactory,
        ActionInterface $action,
        ContextBuilder $actionContextBuilder
    ) {
        $this->fileFactory = $fileFactory;
        $this->action = $action;
        $this->actionContextBuilder = $actionContextBuilder;
        parent::__construct($context, $config);
    }

    protected function executeAction()
    {
        $customerId = (int) $this->getRequest()->getParam('id');

        $this->actionContextBuilder->setParameters([
            ArgumentReader::ENTITY_ID => $customerId,
            ArgumentReader::ENTITY_TYPE => 'customer'
        ]);

        try {
            $result = $this->action->execute($this->actionContextBuilder->create())->getResult();
            /** @var ExportEntityInterface $exportEntity */
            $exportEntity = $result[ExportArgumentReader::EXPORT_ENTITY];

            return $this->fileFactory->create(
                'customer_privacy_data_' . $customerId . '.zip',
                [
                    'type' => 'filename',
                    'value' => $exportEntity->getFilePath(),
                    'rm' => true,
                ],
                DirectoryList::TMP
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, new Phrase('An error occurred on the server.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
