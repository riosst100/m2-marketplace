/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'mageUtils',
    'uiRegistry',
    'underscore',
    'mage/translate'
], function (Component, $, ko, utils, uiRegistry, _) {
    'use strict';

    var initNewAttributeListener = function (provider) {
        $('[data-role=product-variations-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        attributesLabels: {},
        attributesCodes: null,
        attributesIds: null,
        attributesCodesArr: {},
        stepInitialized: false,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            attributes: [],
            selectedAttributes: []
        },
        initialize: function () {
            this._super();
            this.selected = [];

            // initNewAttributeListener(this.attributeProvider);
        },
        initObservable: function () {
            this._super().observe(['selectedAttributes','attributes']);

            return this;
        },
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            var productSource = uiRegistry.get('product_form.product_form_data_source');
            var card_game = productSource ? productSource.data.product['card_game'] : '';
            var category_ids = productSource ? productSource.data.product['category_ids'] : '';
            var card_product_type = productSource ? productSource.data.product['card_product_type'] : '';
            var product = productSource ? productSource.data.product : '';

            var setsGrid = this.attributeProvider();
            setsGrid.set('params.product', product);
            setsGrid.set('params.category_ids', category_ids);
            setsGrid.set('params.card_product_type', card_product_type);
            setsGrid.set('params.card_game', card_game);
            setsGrid.reload();
        },
        setNotificationMessage: function () {
            // if (this.mode === 'edit') {
                // this.wizard.setNotificationMessage($.mage.__('When you remove or add an attribute, we automatically ' +
                // 'update all configurations and you will need to recreate current configurations manually.'));
            // }
        },
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.attributesLabels[attribute.id] = attribute.label;
                    this.attributeCodes[attribute.id] = attribute.attribute_code;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            this.selected = selected;
            _.each(selected, function (attributeId) {
                var attribute;

                if (!this.attributesLabels[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        attribute_id: attributeId
                    });

                    if (attribute) {
                        this.attributesLabels[attribute.attribute_id] = attribute.frontend_label;
                        this.attributesCodesArr[attribute.attribute_id] = attribute.attribute_code;
                        this.attributesCodes = attribute.attribute_code;
                        this.attributesIds = attribute.attribute_id;
                    }
                }

                if (!this.attributesCodesArr[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        attribute_id: attributeId
                    });

                    if (attribute) {
                        this.attributesCodesArr[attribute.attribute_id] = attribute.attribute_code;
                        this.attributesCodes = attribute.attribute_code;
                        this.attributesIds = attribute.attribute_id;
                    }
                }
                labels.push(this.attributesLabels[attributeId]);
            }.bind(this));
            this.selectedAttributes(labels.join(', '));
        },
        createAttribute: function (attribute, index) {
            attribute.chosenOptions = _.pluck(attribute.options, 'value');
            attribute.options = ko.observableArray(_.map(attribute.options, function (option) {
                option.id = utils.uniqueid();

                return option;
            }));
        
            return attribute;
        },
        requestAttributes: function (attributeIds) {
            $.ajax({
                type: 'POST',
                url: this.optionsUrl,
                data: {
                    attributes: attributeIds
                },
                showLoader: true
            }).done(function (attributes) {
                attributes = _.sortBy(attributes, function (attribute) {
                    return this.wizard.data.attributesIds.indexOf(attribute.id);
                }.bind(this));
                
                this.attributes(_.map(attributes, this.createAttribute));
            }.bind(this));
        },
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;
            wizard.data.attributesCodes = this.attributesCodesArr;

            this.requestAttributes(wizard.data.attributesIds());

            wizard.data.attributes = this.attributes;

            var source = uiRegistry.get('product_form.product_form_data_source');
            source.set('data.attribute_codes', [this.attributesCodes]);
            source.set('data.attributes', [this.attributesIds]);

            if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
                throw new Error($.mage.__('Please select at least 1 variation(s).'));
            }
            this.setNotificationMessage();

            var stepWizard = this.wizard.getStep(1);
            stepWizard.showGridAssignProduct();
        },
        back: function () {
        }
    });
});
