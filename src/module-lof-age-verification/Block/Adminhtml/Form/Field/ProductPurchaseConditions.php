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

namespace Lof\AgeVerification\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Rule\Model\Condition\AbstractCondition;

class ProductPurchaseConditions extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * Fieldset
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Lof\AgeVerification\Model\ProductPurchaseFactory
     */
    protected $productPurchaseFactory;

    /**
     * @var \Lof\AgeVerification\Helper\Data
     */
    protected $helperData;

    /**
     * ProductPurchaseConditions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Lof\AgeVerification\Helper\Data $helperData
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Lof\AgeVerification\Helper\Data $helperData,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Lof\AgeVerification\Model\ProductPurchaseFactory $productPurchaseFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_formFactory = $formFactory;
        $this->helperData = $helperData;
        $this->productPurchaseFactory = $productPurchaseFactory;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $fieldsetId = 'conditions_fieldset';
        $formName = 'product_purchase';
        /** @var \Magento\Framework\Data\Form $form */
        $model = $this->productPurchaseFactory->create();
        $serialized = $this->helperData->getPurchaseConditions();

        if (isset($serialized)) {
            $model->setConditions([]);
            $model->setConditionsSerialized($serialized);
            $model->getConditions()->setJsFormObject($fieldsetId);
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'lof_ageverification/ageverification/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Lof_AgeVerification::condition/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => '']
        )->setRenderer($renderer);

        $fieldset->addField(
            'purchase_conditions',
            'text',
            [
                'name' => 'purchase_conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )
            ->setRule($model)
            ->setRenderer($this->_conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        $this->setForm($form);
        return $fieldset->getHtml();
    }

    /**
     * @param AbstractCondition $conditions
     * @param $formName
     * @param $jsFormName
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
