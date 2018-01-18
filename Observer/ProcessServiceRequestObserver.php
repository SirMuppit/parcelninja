<?php
/**
 * Fontera Parcelninja
 *
 * NOTICE OF LICENSE
 *
 * Private Proprietary Software (http://fontera.co.za/legal)
 *
 * @copyright  Copyright (c) 2016 Fontera (http://www.fontera.com)
 * @license    http://fontera.co.za/legal  Private Proprietary Software
 * @author     Shaughn Le Grange - Hatlen <support@fontera.com>
 */

namespace Fontera\Parcelninja\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;
use Fontera\Parcelninja\Model\ServiceFactory;

/**
 * Class ProcessServiceRequestObserver
 * @package Fontera\Parcelninja\Observer
 */
class ProcessServiceRequestObserver implements ObserverInterface
{
    /**
     * Service factory
     *
     * @var \Fontera\Parcelninja\Model\Service
     */
    protected $serviceFactory;

    /**
     * Construct
     *
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * Execute
     *
     * Process request
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $method = $observer->getData('method');
        $params = $observer->getData('params');
        $result = $observer->getData('result');

        $response = $this->serviceFactory->create()->processRequest($method, $params);

        // Set result. Can be fetched if result object was passed by reference
        if ($result instanceof DataObject) {
            $result->setData('result', $response);
        }
    }
}