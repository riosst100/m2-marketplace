<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Trackorder
 * @copyright  Copyright (c) 2020 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Trackorder\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;


    /**
     * track order Helper
     */
    protected $_trackHelper;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ActionFactory          $actionFactory   
     * @param ResponseInterface      $response        
     * @param ManagerInterface       $eventManager    
     * @param \Ves\Trackorder\Helper\Data $trackHelper     
     * @param StoreManagerInterface  $storeManager    
     */
    public function __construct(
    	ActionFactory $actionFactory,
    	ResponseInterface $response,
        ManagerInterface $eventManager,
        \Ves\Trackorder\Helper\Data $trackHelper,
        StoreManagerInterface $storeManager
        )
    {
    	$this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->response = $response;
        $this->_trackHelper = $trackHelper;
        $this->storeManager = $storeManager;
    }
    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        $_trackHelper = $this->_trackHelper;
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'ves_trackorder_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
                );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                    );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $route = $_trackHelper->getConfig('trackorder_general/route');
            $route = $route?$route:'vestrackorder';
            if( $route !='' && $urlKey == $route )
            {
                $request->setModuleName('vestrackorder')
                ->setControllerName('index')
                ->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                    );
            }
           
            $identifiers = explode('/',$urlKey); 

            if( count($identifiers) >=2 && ($identifiers[0] == $route || $identifiers[0] == 'vestrackorder' )){
                if(($identifiers[1] == 'print') || ($identifiers[1] == 'index' && isset($identifiers[2]) && $identifiers[2] == 'print')) {

                    if($identifiers[1] == 'print') {
                        $order_id = isset($identifiers[2])?$identifiers[2]:"";
                    } else {
                        $order_id = isset($identifiers[3])?$identifiers[3]:"";
                    }
                    
                    $request->setModuleName('vestrackorder')
                    ->setControllerName('index')
                    ->setActionName('printAction')
                    ->setParam('order_id', $order_id);

                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
                if(($identifiers[1] == 'send') || ($identifiers[1] == 'index' && isset($identifiers[2]) && $identifiers[2] == 'send')) {
                    if($identifiers[1] == 'send') {
                        $order_id = isset($identifiers[2])?$identifiers[2]:"";
                    } else {
                        $order_id = isset($identifiers[3])?$identifiers[3]:"";
                    }
                    
                    $request->setModuleName('vestrackorder')
                    ->setControllerName('index')
                    ->setActionName('send')
                    ->setParam('order_id', $order_id);

                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
                
            }
            //Check tracking track Url
            if( count($identifiers) >=2 && ($identifiers[0] == $route || $identifiers[0] == 'vestrackorder' )){
                $code = "";
                if($identifiers[1] == 'track'){
                    $code = isset($identifiers[2])?$identifiers[2]:"";
                }elseif($identifiers[1] == 'index' && isset($identifiers[2]) && $identifiers[2] == 'track'){
                    $code = isset($identifiers[3])?$identifiers[3]:"";
                }


                $request->setModuleName('vestrackorder')
                    ->setControllerName('index')
                    ->setActionName('track')
                    ->setParam('code', $code);
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
            }
        }
    }
}