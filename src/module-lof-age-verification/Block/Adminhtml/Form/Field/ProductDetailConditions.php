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

class ProductDetailConditions extends \Magento\Config\Block\System\Config\Form\Field
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
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var \Lof\AgeVerification\Helper\Data
     */
    protected $helperData;

    /**
     * ProductDetailConditions constructor.
     * @param Context $context
     * @param \Lof\AgeVerification\Helper\Data $helperData
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Lof\AgeVerification\Helper\Data $helperData,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_formFactory = $formFactory;
        $this->helperData = $helperData;
        $this->catalogRuleFactory = $catalogRuleFactory;
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
        $formName = 'product_detail';
        /** @var \Magento\Framework\Data\Form $form */
        $model = $this->catalogRuleFactory->create();
        $serialized = $this->helperData->getProductConditions();
        if (isset($serialized)) {
            $model->setConditions([]);
            $model->setConditionsSerialized($serialized);
            $model->getConditions()->setJsFormObject($fieldsetId);
        }

        /** @var \Magento\CatalogRule\Model\Rule $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/' . $conditionsFieldSetId,
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
            'conditions',
            'text',
            [
                'name' => 'conditions',
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
