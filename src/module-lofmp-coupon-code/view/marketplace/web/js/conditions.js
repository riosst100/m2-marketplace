define(['jquery'], function ($) {
    'use strict';
    return function (config, element) {
        // Wait for DOM ready and for UI form fields to render
        $(function () {
            var tryInit = 0;
            var maxTries = 20;
            var delay = 150;

            function findElements() {
                var $applicableCategory = $('select[name="data[rule][applicable_category]"]');
                var $specificCategory = $('select[name="data[rule][specific_category]"]');
                var $applicableProduct = $('select[name="data[rule][applicable_product]"]');
                var $addProductsButton = $('#add_products_button'); // element id set in XML config

                return {
                    $applicableCategory: $applicableCategory.length ? $applicableCategory : null,
                    $specificCategory: $specificCategory.length ? $specificCategory : null,
                    $applicableProduct: $applicableProduct.length ? $applicableProduct : null,
                    $addProductsButton: $addProductsButton.length ? $addProductsButton : null
                };
            }

            function updateVisibility(found) {
                var $ac = found.$applicableCategory;
                var $sc = found.$specificCategory;
                var $ap = found.$applicableProduct;
                var $btn = found.$addProductsButton;

                if (!$ac) return;

                var val = $ac.val();

                if (val === 'specific') {
                    if ($sc) $sc.closest('.admin__field').show();
                    if ($ap) {
                        $ap.prop('disabled', false);
                        $ap.closest('.admin__field').show();
                    }
                    if ($btn) $btn.closest('.admin__field').show();
                } else {
                    // all categories selected
                    if ($sc) $sc.closest('.admin__field').hide();
                    if ($ap) {
                        $ap.prop('disabled', true);
                        $ap.closest('.admin__field').hide();
                    }
                    if ($btn) $btn.closest('.admin__field').hide();
                }
            }

            function init() {
                var found = findElements();
                if (!found.$applicableCategory || !found.$applicableProduct) {
                    tryInit++;
                    if (tryInit < maxTries) {
                        setTimeout(init, delay);
                        return;
                    }
                    // abort after retries
                    return;
                }

                // initial update
                updateVisibility(found);

                // watch changes
                $(document).on('change', 'select[name="data[rule][applicable_category]"]', function () {
                    updateVisibility(findElements());
                });

                // Add Products click handler — adjust behavior to your needs.
                // Here we build a URL and redirect. You can replace this to open a modal/grid instead.
                $(document).on('click', '#add_products_button', function (e) {
                    e.preventDefault();
                    var baseUrl = window.BASE_URL || '/';
                    if (baseUrl.slice(-1) !== '/') baseUrl += '/';
                    var category = $('select[name="data[rule][specific_category]"]').val() || '';
                    // change the path below to the controller that will render the chooser for your marketplace
                    var url = baseUrl + 'marketplace/lofmpcouponcode/rule/addProducts' + (category ? '?category=' + encodeURIComponent(category) : '');
                    window.location.href = url;
                });
            }

            init();
        });
    };
});
