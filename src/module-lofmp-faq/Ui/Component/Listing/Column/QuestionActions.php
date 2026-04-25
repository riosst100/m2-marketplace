<?php

namespace Lofmp\Faq\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class QuestionActions extends Column
{
    protected UrlInterface $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['question_id'])) {
                continue;
            }

            $item[$this->getData('name')] = [
                'edit' => [
                    'href'  => $this->urlBuilder->getUrl(
                        'catalog/faqquestion/edit',
                        ['id' => $item['question_id']]
                    ),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href'  => $this->urlBuilder->getUrl(
                        'catalog/faqquestion/delete',
                        ['id' => $item['question_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete Question'),
                        'message' => __('Are you sure you want to delete this question?')
                    ]
                ]
            ];
        }

        return $dataSource;
    }
}
