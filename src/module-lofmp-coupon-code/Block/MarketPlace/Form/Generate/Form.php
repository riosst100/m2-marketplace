<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lofmp_CouponCode
 * @copyright  Copyright (c) 2021 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lofmp\CouponCode\Block\MarketPlace\Form\Generate;

/**
 * Adminhtml report filter form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Report type options
     *
     * @var array
     */
    protected $_reportTypeOptions = [];

    /**
     * Report field visibility
     *
     * @var array
     */
    protected $_fieldVisibility = [];

    /**
     * Report field opions
     *
     * @var array
     */
    protected $_fieldOptions = [];

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Lofmp\CouponCode\Helper\Data $helper,
        array $data = []
        ) {
        
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_helper = $helper;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        // die($this->_helper->getAllRule());
        $actionUrl = $this->getUrl('*/generate/generate');
        $ajaxUrl   = $this->getUrl('*/generate/customers'); // AJAX endpoint to create (controller below)

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'generate_form',
                    'action' => $actionUrl,
                    'method' => 'post'
                ]
            ]
        );

        $htmlIdPrefix = 'lofmp_couponcode_generate_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Generate Code for Visitor/Customer email')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField('store_ids', 'hidden', ['name' => 'store_ids']);

        // $fieldset->addField(
        //     'email_visitor',
        //     'text',
        //     [
        //         'name' => 'email_visitor',
        //         'class' => 'validate-email',
        //         'label' => __('Visitor/Customer Email'),
        //         'title' => __('Visitor/Customer Email'),
        //         'note' => __('Required to innput the email address when rule enabled option "Enable check email address when use coupon code".')
        //     ]
        // );

        // 1) dropdown: All Customers / Specific Customer
        // Include inline JS with after_element_html that will:
        //  - toggle the specific customer field visibility
        //  - when needed, call AJAX to populate the specific-customer select
        $afterElementHtml = <<<HTML
        <script type="text/javascript">
        require(['jquery'], function($) {
            var prefix = '{$htmlIdPrefix}';
            var ajaxUrl = '{$ajaxUrl}';

            window.lofmpToggleCustomerField = function(value) {
                var \$cust = $('#' + prefix + 'email_visitor');
                var \$wrapper = \$cust.closest('.admin__field');

                if (value === 'specific') {
                    // show wrapper
                    if (\$wrapper.length) { \$wrapper.show(); }

                    // if not loaded yet -> load via ajax
                    if (\$cust.data('loaded') !== true) {
                        \$cust.prop('disabled', true);
                        \$.ajax({
                            url: ajaxUrl,
                            type: 'GET',
                            dataType: 'json'
                        }).done(function(res) {
                            if (!res) {
                                alert('No response while loading customers');
                                return;
                            }
                            if (res.error) {
                                alert(res.message || 'Error loading customers');
                                return;
                            }
                            \$cust.empty();
                            \$cust.append(\$('<option>').val('').text('-- Please Select --'));
                            \$.each(res, function(i, item) {
                                // item: { value: 'email', label: 'email (Name)' }
                                \$cust.append(\$('<option>').val(item.value).text(item.label));
                            });
                            \$cust.data('loaded', true);
                            \$cust.prop('disabled', false);
                        }).fail(function() {
                            alert('Failed to load customers (ajax error)');
                            \$cust.prop('disabled', false);
                        });
                    } else {
                        \$cust.prop('disabled', false);
                    }
                } else {
                    // hide wrapper and reset
                    if (\$wrapper.length) { \$wrapper.hide(); }
                    \$cust.val('').prop('disabled', true);
                }
            };

            \$(document).ready(function() {
                var \$type = $('#' + prefix + 'customer_type');
                // bind change
                \$type.on('change', function() { lofmpToggleCustomerField(this.value); });
                // initialize based on current value
                lofmpToggleCustomerField(\$type.val());
            });
        });
        </script>
        HTML;

        $fieldset->addField(
            'customer_type',
            'select',
            [
                'name' => 'customer_type',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'values' => [
                    ['value' => 'all', 'label' => __('All Customers')],
                    ['value' => 'specific', 'label' => __('Specific Customer')]
                ],
                'note' => __('Choose whether coupon is for all customers or for a specific customer.'),
                'after_element_html' => $afterElementHtml
            ]
        );

        // Keep the same field name: email_visitor
        $fieldset->addField(
            'email_visitor',
            'select',
            [
                'name'  => 'email_visitor',
                'label' => __('Select Customer Email'),
                'title' => __('Select Customer Email'),
                'values' => [
                    ['value' => '', 'label' => __('-- Please Select --')]
                ],
                'note' => __('This dropdown will be filled when "Specific Customer" is selected.'),
                'disabled' => true,
            ]
        );

        $fieldset->addField(
            'coupon_rule_id',
            'select',
            [
                'label'    => __('Choose Coupon Rule'),
                'title'    => __('Choose Coupon Rule'),
                'name'     => 'coupon_rule_id',
                'options'  => $this->_helper->getAllRule()
            ]
        ); 
        $fieldset->addField(
            'generate_coupon',
            'submit',
            [
                'label'    => '',
                'title'    => '',
                'class'    => 'action-secondary' ,
                'name'     => 'generate_coupon',
                'checked' => false,
                'onchange' => "",
                'value' => __('Generate Coupon'),
            ]

        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

   
}
