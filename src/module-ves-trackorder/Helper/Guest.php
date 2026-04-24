<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ves\Trackorder\Helper;

use Magento\Framework\App as App;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use \Magento\Sales\Model\Order;

/**
 * Sales module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Guest extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
 
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Cookie key for guest view
     */
    const COOKIE_NAME = 'guest-view';

    /**
     * Cookie path
     */
    const COOKIE_PATH = '/';

    /**
     * Cookie lifetime value
     */
    const COOKIE_LIFETIME = 600;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var string
     */
    private $inputExceptionMessage = 'You entered incorrect data. Please try again.';

    /**
     * @param App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager 
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria = null
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager; 
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderRepository = $orderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteria?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        parent::__construct(
            $context
        );
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @param App\RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     * @throws \RuntimeException
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function loadValidOrder($incrementId, $trackcode = '')
    { 
        if($trackcode){
            $post = $this->coreRegistry->registry('current_order');
            $incrementId = $post->getIncrementId();
        } 
        $fromCookie = $this->cookieManager->getCookie(self::COOKIE_NAME);  
        try {
            $order = (!empty($incrementId))
                ? $this->loadFromPost($incrementId) : $this->loadFromCookie($fromCookie);  
            return true;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            return;
        }
    }
 

    /**
     * Set guest-view cookie
     *
     * @param string $cookieValue
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    private function setGuestViewCookie($cookieValue)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath(self::COOKIE_PATH)
            ->setHttpOnly(true);
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $cookieValue, $metadata);
    }

    /**
     * Load order from cookie
     *
     * @param string $fromCookie
     * @return Order
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    private function loadFromCookie($fromCookie)
    {
        $cookieData = explode(':', base64_decode($fromCookie));
        $protectCode = isset($cookieData[0]) ? $cookieData[0] : null;
        $incrementId = isset($cookieData[1]) ? $cookieData[1] : null;
        if (!empty($protectCode) && !empty($incrementId)) {
            $order = $this->getOrderRecord($incrementId);
            if (hash_equals((string)$order->getProtectCode(), $protectCode)) {
                $this->setGuestViewCookie($fromCookie);
                return $order;
            }
        }
        throw new InputException(__($this->inputExceptionMessage));
    }

    /**
     * Load order data from post
     *
     * @param array $postData
     * @return Order
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    private function loadFromPost($incrementId)
    {  
        $order = $this->getOrderRecord($incrementId); 
        $toCookie = base64_encode($order->getProtectCode() . ':' . $incrementId);
        $this->setGuestViewCookie($toCookie);
        return $order;
    } 

    /**
     * Get order by increment_id and store_id
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws InputException
     */
    private function getOrderRecord($incrementId)
    {
        $records = $this->orderRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId)
                ->create()
        );
        if ($records->getTotalCount() < 1) {
            throw new InputException(__($this->inputExceptionMessage));
        }
        $items = $records->getItems();
        return array_shift($items);
    } 
}
