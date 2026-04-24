<?php


namespace Lof\Faq\Api;

interface TagsInterface
{
    /**
     * Save Tag.
     *
     * @param \Lof\Faq\Api\Data\TagInterface $tag
     * @return \Lof\Faq\Api\Data\TagInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\Faq\Api\Data\TagInterface $tag);

}
