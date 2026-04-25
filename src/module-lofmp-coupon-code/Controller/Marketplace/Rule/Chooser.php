<?php
namespace Lofmp\CouponCode\Controller\Marketplace\Rule;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\ConditionInterface;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;
use Lofmp\CouponCode\Controller\Marketplace\Rule as BaseRule;
use Magento\Backend\Block\Widget\Grid\Serializer;

/**
 * Controller class NewConditionHtml. Returns condition html
 */
class Chooser extends BaseRule
{
    /**
     * Execute method to render product chooser grid
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/coupon.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);            
        $logger->info('Chooser called');

        $attribute = $this->getRequest()->getParam('attribute');
        $formName  = $this->getRequest()->getParam('form');
        $uniqId    = $this->getRequest()->getParam('uniq_id');
        $chooserId = $this->getRequest()->getParam('element_value');
        $useMassaction = true;

        if ($attribute === 'sku') {
            $logger->info('Rendering chooser block with attribute: ' . $attribute);
            /** @var \Lofmp\CouponCode\Block\MarketPlace\Rule\Promo\Widget\Chooser $block */
            $block = $this->_view->getLayout()->createBlock(
                \Lofmp\CouponCode\Block\MarketPlace\Rule\Promo\Widget\Chooser::class,
                '',
                [
                    'data' => [
                        'id' => $uniqId,
                        'use_massaction' => $useMassaction,
                        'product_type_id' => $attribute,
                        'form' => $formName,
                        'element_value' => $chooserId,
                    ]
                ]
            );
            // $block = $this->_view->getLayout()->createBlock(
            //     \Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku::class,
            //     'promo_widget_chooser_sku',
            //     ['data' => ['js_form_object' => $formName]]
            // );
            
            try {
                $logger->info('Block created: ' . get_class($block));
                $this->getResponse()->setBody($block->toHtml());
                $logger->info('Block HTML rendered successfully.');
            } catch (\Exception $e) {
                $logger->info('Error rendering block HTML: ' . $e->getMessage());
            }

            $logger->info('Response body set with block HTML.');
        } else {
            $this->getResponse()->setBody(__('Invalid attribute.'));
        }
    }
}
