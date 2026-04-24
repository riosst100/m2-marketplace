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

var config = {
    map: {
        '*': {
            dobPicker: 'Lof_AgeVerification/js/dob-picker',
            lofAgeVerificationPopup: 'Lof_AgeVerification/js/lofavpopup',
            lofAgeVerificationPopupInit: 'Lof_AgeVerification/js/lofavpopup-init'
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Lof_AgeVerification/js/mixin/swatch-renderer-mixin': true
            }
        }
    },
    paths: {
        dobPicker: 'Lof_AgeVerification/js/dob-picker'
    },
    shim: {
        dobPicker: {
            deps: ['jquery'],
        },
    }
};
