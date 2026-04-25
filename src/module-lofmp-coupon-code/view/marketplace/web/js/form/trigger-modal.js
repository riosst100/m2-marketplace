define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function () {
            let popup = $('<div id="add-products-modal"/>').modal({
                type: 'slide',
                title: 'Select Products',
                modalClass: 'add-products-modal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'action-secondary',
                    click: function () {
                        this.closeModal();
                    }
                }]
            });

            // Load modal content from your controller
            $.ajax({
                url: config.url, // e.g. controller URL
                type: 'GET',
                showLoader: true
            }).done(function (response) {
                popup.html(response).trigger('contentUpdated');
                popup.modal('openModal');
            });
        });
    };
});
