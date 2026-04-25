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
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

define([
    'Magento_Ui/js/form/element/textarea'
], function (Textarea) {
    'use strict';

    return Textarea.extend({
        defaults: {
            allowImport: true,
            autoImportIfEmpty: false,
            values: {
                'name': '',
                'description': '',
                'sku': '',
                'color': '',
                'country_of_manufacture': '',
                'gender': '',
                'material': '',
                'short_description': '',
                'size': ''
            },
            valueUpdate: 'input',
            mask: ''
        },

        /**
         * Handle name value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleNameChanges: function (newValue) {
            this.values.name = newValue;
            this.updateValue();
        },

        /**
         * Handle description value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleDescriptionChanges: function (newValue) {
            this.values.description = newValue;
            this.updateValue();
        },

        /**
         * Handle sku value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleSkuChanges: function (newValue) {
            if (this.code !== 'sku') {
                this.values.sku = newValue;
                this.updateValue();
            }
        },

        /**
         * Handle color value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleColorChanges: function (newValue) {
            this.values.color = newValue;
            this.updateValue();
        },

        /**
         * Handle country value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleCountryChanges: function (newValue) {
            this.values.country = newValue;
            this.updateValue();
        },

        /**
         * Handle gender value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleGenderChanges: function (newValue) {
            this.values.gender = newValue;
            this.updateValue();
        },

        /**
         * Handle material value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleMaterialChanges: function (newValue) {
            this.values.material = newValue;
            this.updateValue();
        },

        /**
         * Handle short description value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleShortDescriptionChanges: function (newValue) {
            this.values['short_description'] = newValue;
            this.updateValue();
        },

        /**
         * Handle size value changes, if it's allowed
         *
         * @param {String} newValue
         */
        handleSizeChanges: function (newValue) {
            this.values.size = newValue;
            this.updateValue();
        },

        /**
         * Update field value, if it's allowed
         */
        updateValue: function (placeholder, component) {
            var string = this.mask || '',
                nonEmptyValueFlag = false;

            if (placeholder) {
                this.values[placeholder] = component.getPreview() || '';
            }

            if (!this.allowImport) {
                return;
            }

            _.each(this.values, function (propertyValue, propertyName) {
                var formatted = propertyValue
                    .replace(/[^a-zA-Z0-9\s]/g, '') // Hapus karakter khusus
                    .replace(/\s+/g, '-')           // Ganti spasi dengan -
                    .toUpperCase();                 // Kapital semua

                string = string.replace('{{' + propertyName + '}}', formatted);
                nonEmptyValueFlag = nonEmptyValueFlag || !!propertyValue;
            });

            if (nonEmptyValueFlag) {
                string = string.replace(/(<([^>]+)>)/ig, ''); // Hapus tag HTML
                string = string.substring(0, 50);             // 💥 Batasi setelah final
                
                this.value(string);
            } else {
                this.value('');
            }
        },

        /**
         * Disallow import when initial value isn't empty string
         *
         * @returns {*}
         */
        setInitialValue: function () {
            this._super();

            if (this.initialValue !== '') {
                this.allowImport = false;
            }

            return this;
        },

        /**
         *  Callback when value is changed by user,
         *  and disallow/allow import value
         */
        userChanges: function () {
            this._super();

            if (this.value() === '') {
                this.allowImport = true;

                if (this.autoImportIfEmpty) {
                    this.updateValue();
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});
