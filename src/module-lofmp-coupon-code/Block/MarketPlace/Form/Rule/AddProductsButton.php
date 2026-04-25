<?php

namespace Lofmp\CouponCode\Block\MarketPlace\Form\Rule;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddProductsButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Add Products'),
            'class' => 'add-products-button',
            'on_click' => 'jQuery("#add-products-modal").modal("openModal");',
            'sort_order' => 90,
        ];
    }
}
