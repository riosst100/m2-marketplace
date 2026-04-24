<?php

namespace Lof\Faq\Model\Data;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Lof\Faq\Model\Category;
use Lof\Faq\Api\Data;
use Magento\Store\Model\StoreManagerInterface;
use Lof\Faq\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Exception\CouldNotSaveException;

class Categories implements \Lof\Faq\Api\CategoriesInterface
{
    protected $_resource;
    protected $_categoryFactory;
    protected $_filesystem;
    protected $jsHelper;
    protected $_category;
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ResourceCategory
     */
    protected $resource;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource,
                                \Lof\Faq\Model\CategoryFactory $categoryFactory,
                                \Magento\Framework\Filesystem $filesystem,
                                \Magento\Backend\Helper\Js $jsHelper,
                                Category $category,
                                StoreManagerInterface $storeManager,
                                Data\CategorySearchResultsInterfaceFactory $searchResultsFactory,
                                ResourceCategory $resourceCategory)

    {
        $this->_resource = $resource;
        $this->_categoryFactory = $categoryFactory;
        $this->_filesystem = $filesystem;
        $this->jsHelper = $jsHelper;
        $this->_category = $category;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->resource = $resourceCategory;
    }

    /**
     * get list in backend
     *
     * @return Data\CategorySearchResultsInterface
     */
    public function getListInBackend()
    {
        $categoryCollection = $this->_category->getCollection()
            ->setCurPage(1)
            ->setOrder('category_id', 'DESC');

        /** @var Data\CategorySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($categoryCollection->getItems());
        $searchResults->setTotalCount($categoryCollection->getSize());
        return $searchResults;
    }

    /**
     * get list in frontend
     *
     * @return Data\CategorySearchResultsInterface
     */
    public function getListInFrontend()
    {
        $categoryCollection = $this->_category->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setCurPage(1)
            ->setOrder('category_id', 'DESC');

        /** @var Data\CategorySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($categoryCollection->getItems());
        $searchResults->setTotalCount($categoryCollection->getSize());
        return $searchResults;
    }

    /**
     * Save Category data
     *
     * @param \Lof\Faq\Api\Data\CategoryInterface $category
     * @return Category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\Faq\Api\Data\CategoryInterface $category)
    {
        if ($category['title'] && $category['identifier'] && $category['stores']) {
            try {
                $this->resource->save($category);
            } catch (\Exception $exception) {
                throw new CouldNotSaveException(
                    __('Could not save the category: %1', $exception->getMessage()),
                    $exception
                );
            }
            return $category;
        } else {
            return false;
        }

    }

    public function uploadImage($fieldId = 'image')
    {

        if (isset($_FILES[$fieldId]) && $_FILES[$fieldId]['name'] != '') {
            $uploader = $this->_objectManager->create(
                'Magento\Framework\File\Uploader',
                ['fileId' => $fieldId]
            );

            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $mediaFolder = 'lof/faq/';
            try {
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($mediaDirectory->getAbsolutePath($mediaFolder)
                );
                return $mediaFolder . $result['name'];
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError($e->getMessage());
            }
        }
        return;
    }

}
