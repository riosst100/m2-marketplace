<?php
namespace Lofmp\Rma\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class RmaActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source: inject actions for each row
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item['rma_id'])) {
                continue;
            }

            $actions = [];

            // View action
            $actions['view'] = [
                'href' => $this->urlBuilder->getUrl('catalog/rma/view', ['id' => $item['rma_id']]),
                'label' => __('View'),
                'hidden' => false
            ];

            // (Optional) Add more actions, e.g. delete/edit:
            // $actions['delete'] = [
            //     'href' => $this->urlBuilder->getUrl('catalog/rma/delete', ['id' => $item['rma_id']]),
            //     'label' => __('Delete'),
            //     'confirm' => [
            //         'title' => __('Delete RMA'),
            //         'message' => __('Are you sure you want to delete this RMA?')
            //     ]
            // ];

            // Assign to column name (UI expects actions under column's name)
            $item[$this->getData('name')] = $actions;
        }

        return $dataSource;
    }
}
