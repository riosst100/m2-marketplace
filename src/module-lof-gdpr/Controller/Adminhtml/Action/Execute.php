<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Controller\Adminhtml\Action;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Lof\Gdpr\Api\Data\ActionResultInterface;
use Lof\Gdpr\Model\Action\ActionFactory;
use Lof\Gdpr\Model\Action\ContextBuilder;
use Lof\Gdpr\Model\Config\Source\ActionStates;

class Execute extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Lof_Gdpr::gdpr_actions_execute';

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var ActionStates
     */
    private $actionStates;

    public function __construct(
        Context $context,
        ActionFactory $actionFactory,
        ContextBuilder $contextBuilder,
        ActionStates $actionStates
    ) {
        $this->actionFactory = $actionFactory;
        $this->contextBuilder = $contextBuilder;
        $this->actionStates = $actionStates;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/new');

        try {
            $result = $this->proceed($this->getRequest()->getParam('type'));
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addSuccessMessage(
                new Phrase('The action state is now: %1.', [$this->actionStates->getOptionText($result->getState())])
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, new Phrase('An error occurred on the server.'));
        }

        return $resultRedirect;
    }

    /**
     * @param string $actionType
     * @return ActionResultInterface
     * @throws LocalizedException
     */
    private function proceed(string $actionType): ActionResultInterface
    {
        $parameters = [];
        /** @var array $param */
        foreach ((array) $this->getRequest()->getParam('parameters', []) as $param) {
            $parameters[$param['name']] = $param['value'];
        }
        $this->contextBuilder->setParameters($parameters);

        return $this->actionFactory->get($actionType)->execute($this->contextBuilder->create());
    }
}
