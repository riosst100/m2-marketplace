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

namespace Lof\AgeVerification\Model\Config\Source;

class ConfigData
{
    /**
     * Module General Settings config path
     */
    const XML_PATH_MODULE_STATUS = 'lofageverification/general_settings/enable';

    const XML_PATH_STORE_VIEW = 'lofageverification/general_settings/store_view';

    const XML_PATH_REQUIRED_LOGIN = 'lofageverification/general_settings/required_login';

    const XML_PATH_LOGIN_NOTICE = 'lofageverification/general_settings/login_notice';

    const XML_PATH_PREVENT_NOTICE = 'lofageverification/general_settings/prevent_notice';

    const XML_PATH_COOKIE_LIFETIME = 'lofageverification/general_settings/cookie_lifetime';

    const XML_PATH_VERIFY_TYPE = 'lofageverification/general_settings/verify_type';

    const XML_PATH_VERIFY_AGE = 'lofageverification/general_settings/verify_age';

    /**
     * Module Verification Configuration config path
     */
    const XML_PATH_PRODUCT_CONDITIONS = 'lofageverification/verification_configuration/product_detail/conditions';

    const XML_PATH_PURCHASE_CONDITIONS = 'lofageverification/verification_configuration/product_purchase/purchase_conditions';

    const XML_PATH_APPLY_TO_CATEGORY = 'lofageverification/verification_configuration/category_page/apply_to_category';

    const XML_PATH_APPLY_TO_CMS_PAGES = 'lofageverification/verification_configuration/cms_pages/apply_to_cms_pages';

    const XML_PATH_ENABLE_CATEGORY_PAGES = 'lofageverification/verification_configuration/category_page/enable_pages';

    const XML_PATH_ENABLE_CMS_PAGES = 'lofageverification/verification_configuration/cms_pages/enable_pages';

    const XML_PATH_ENABLE_PRODUCT_DETAIL_CONDITIONS = 'lofageverification/verification_configuration/product_detail/enable_conditions';

    const XML_PATH_ENABLE_PRODUCT_PURCHASE_CONDITIONS = 'lofageverification/verification_configuration/product_purchase/enable_conditions';

    const XML_PATH_PURCHASE_NOTICE = 'lofageverification/verification_configuration/product_purchase/purchase_notice';

    const XML_PATH_PURCHASE_MESSAGE = 'lofageverification/verification_configuration/product_purchase/purchase_message';


    /**
     * Module Design config path
     */
    const XML_PATH_POPUP_TITLE = 'lofageverification/design/popup_title';

    const XML_PATH_POPUP_DESCRIPTION = 'lofageverification/design/popup_description';

    const XML_PATH_BUTTON_CANCEL_TEXT = 'lofageverification/design/button_cancel_text';

    const XML_PATH_BUTTON_CONFIRM_TEXT = 'lofageverification/design/button_confirm_text';

    const XML_PATH_REDIRECT_URL = 'lofageverification/design/redirect_url';

    const XML_PATH_POPUP_ICON = 'lofageverification/design/popup_icon';

    const XML_PATH_BACKGROUND_COLOR = 'lofageverification/design/background_color';

    const XML_PATH_TEXT_COLOR = 'lofageverification/design/text_color';

    const XML_PATH_OVERLAY_COLOR = 'lofageverification/design/overlay_color';

    const XML_PATH_BUTTON_CANCEL_TEXT_COLOR = 'lofageverification/design/button_cancel_text_color';

    const XML_PATH_BUTTON_CANCEL_BACKGROUND_COLOR = 'lofageverification/design/button_cancel_background_color';

    const XML_PATH_BUTTON_CONFIRM_TEXT_COLOR = 'lofageverification/design/button_cancel_text_color';

    const XML_PATH_BUTTON_CONFIRM_BACKGROUND_COLOR = 'lofageverification/design/button_cancel_background_color';

    /**
     * Module Developer config path
     */
    const XML_PATH_PRODUCT_ITEM_SELECTOR = 'lofageverification/developer/product_item_selector';

    const XML_PATH_ADDTOCART_SELECTOR = 'lofageverification/developer/addtocart_selector';
}
