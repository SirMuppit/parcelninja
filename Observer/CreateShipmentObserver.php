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
use Fontera\Parcelninja\Helper\Data as Helper;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Shipment;
use Fontera\Parcelninja\Model\Carrier as Parcelninja;
use Fontera\Parcelninja\Model\Process as ParcelninjaProcess;
use Fontera\Parcelninja\Model\ResourceModel\Config;

/**
 * Class CreateShipmentObserver
 * @package Fontera\Parcelninja\Observer
 */
class CreateShipmentObserver implements ObserverInterface
{
    /**
     * Carrier factory
     *
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * Shipment factory
     *
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * Parcelninja process
     *
     * @var ParcelninjaProcess
     */
    protected $parcelninjaProcess;

    /**
     * Scope resolver
     *
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * Parcelninja helper
     *
     * @var Helper
     */
    protected $helper;

    /**
     * Order
     *
     * @var Order
     */
    protected $order;

    /**
     * Shipment
     *
     * @var Shipment
     */
    protected $shipment;

    /**
     * Dropship pending order
     *
     * @var string[]
     */
    protected $dropshipPendingOrder = [];

    /**
     * Construct
     *
     * @param CarrierFactory $carrierFactory
     * @param ShipmentFactory $shipmentFactory
     * @param ParcelninjaProcess $parcelninjaProcess
     * @param Helper $helper
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        ShipmentFactory $shipmentFactory,
        ParcelninjaProcess $parcelninjaProcess,
        Helper $helper
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->parcelninjaProcess = $parcelninjaProcess;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * Auto create shipment if carrier config allows it
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getData('invoice');

        if ($invoice->getState() == Invoice::STATE_PAID) {
            try {
                $this->order = $invoice->getOrder();

                $shippingMethod = $this->order->getShippingMethod(true);

                $carrier = $this->carrierFactory->create($shippingMethod->getData('carrier_code'));

                // We only want to create the shipment if this is not the admin store
                if ($carrier instanceof Parcelninja && $carrier->canAutoCreateShipment() && !$this->isAdminStore()) {
                    // Prepare shipment
                    try {
                        $this->prepareShipment();

                        if ($this->shipment && $this->shipment->getId()) {
                            $this->order->addStatusHistoryComment(
                                __('The shipment was auto generated for Parcelninja successfully.')
                            );

                            $this->parcelninjaProcess->shipmentRequest($this->shipment, false);
                        }
                    } catch (\Exception $e) {
                        $this->order->addStatusHistoryComment(
                            __('The shipment could not be auto generated for Parcelninja. Error: %1', $e->getMessage())
                        );
                    }

                    // Order to dropship
                    try {
                        if ($this->helper->isOrderToDropshipActive()) {

                            $dropshipPendingOrder = $this->parcelninjaProcess->prepareOrderToDropship($this->order);

                            if (!empty($dropshipPendingOrder['id'])) {
                                $this->order->addStatusHistoryComment(__(
                                    'The order was sent to Dropship successfully. Pending Order ID: %1.',
                                    $dropshipPendingOrder['id']
                                ));
                                $this->order->setData(Config::KEY_ORDER_DROPSHIP_ORDER_ID, $dropshipPendingOrder['id']);
                            } else {
                                $this->order->addStatusHistoryComment(
                                    __('The order could not be sent to Dropship.')
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        $this->order->addStatusHistoryComment(
                            __('The order could not be sent to Dropship. Error: %1', $e->getMessage())
                        );
                    }

                    // Save order
                    $this->order->getResource()->save($this->order);
                }
            } catch (\Exception $e) {
                $this->helper->handleException($e);
            }
        }
    }

    /**
     * Prepare shipment
     *
     * @return CreateShipmentObserver $this
     */
    protected function prepareShipment()
    {
        // This is set to true when invoicing via admin
        $this->order->setForcedShipmentWithInvoice(true);

        $items = $this->order->getAllItems();

        $shipmentItems = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            $shipmentItems[$item->getId()] = $item->getQtyInvoiced();
        }

        $this->shipment = $this->shipmentFactory->create(
            $this->order,
            $shipmentItems,
            null // Tracking
        );

        if ($this->shipment->getTotalQty()) {
            $this->shipment->register();

            $this->shipment->getResource()->save($this->shipment);
        } else {
            $this->shipment = null;
        }

        return $this;
    }

    /**
     * Check is request use default scope
     *
     * @return bool
     */
    private function isAdminStore()
    {
        return $this->getScopeResolver()->getScope()->getCode() == Store::ADMIN_CODE;
    }

    /**
     * Get store manager for operations with admin code
     *
     * @return ScopeResolverInterface
     */
    private function getScopeResolver()
    {
        if ($this->scopeResolver == null) {
            $this->scopeResolver = ObjectManager::getInstance()->get(ScopeResolverInterface::class);
        }

        return $this->scopeResolver;
    }
}
