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

namespace Fontera\Parcelninja\Model;

use Fontera\Parcelninja\Helper\Data as Helper;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Magento\Sales\Model\Order\Shipment;
use Fontera\Parcelninja\Model\Carrier as Parcelninja;
use Magento\Framework\App\ObjectManager;
use Fontera\Parcelninja\Model\ResourceModel\Config;
use Magento\Sales\Api\Data\ShipmentInterface;

class Process
{
    /**
     * Carrier factory
     *
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * Track collection factory
     *
     * @var TrackCollectionFactory
     */
    protected $trackCollectionFactory;

    /**
     * Dropship service
     *
     * @var DropshipService
     */
    protected $dropshipService;

    /**
     * Parcelninja helper
     *
     * @var Helper
     */
    protected $helper;

    /**
     * Construct
     *
     * @param CarrierFactory $carrierFactory
     * @param TrackCollectionFactory $trackCollectionFactory
     * @param DropshipService $dropshipService
     * @param Helper $helper
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        TrackCollectionFactory $trackCollectionFactory,
        DropshipService $dropshipService,
        Helper $helper
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->dropshipService = $dropshipService;
        $this->helper = $helper;
    }

    /**
     * Shipment request
     *
     * @param Shipment $shipment
     * @param bool $manual
     * @return string[] $result
     */
    public function shipmentRequest($shipment, $manual = false)
    {
        $response = null;
        $result = [
            'status' => '',
            'message' => ''
        ];
        $comment = '';

        if ($manual) {
            $manual = 'manually';
        } else {
            $manual = 'automatically';
        }

        try {
            $shippingMethod = $shipment->getOrder()->getShippingMethod(true);

            $carrier = $this->carrierFactory->create($shippingMethod->getData('carrier_code'));

            if ($carrier instanceof Parcelninja) {
                // Create shipment request and get outbound ID
                $response = $carrier->createShipmentRequest($shipment);

                $outboundId = $response->getOutboundId();

                // Set shipment outbound ID
                $shipment->setData(Config::KEY_SHIPPING_OUTBOUND_ID, $outboundId);

                $objectManager = ObjectManager::getInstance();

                $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                    ->setNumber($outboundId)
                    ->setCarrierCode($carrier->getCarrierCode())
                    ->setTitle($carrier->getCarrierTitle())
                    ->setShipment($shipment)
                    ->setParentId($shipment->getId())
                    ->setOrderId($shipment->getOrderId())
                    ->setStoreId($shipment->getStoreId());

                $collection = $this->trackCollectionFactory->create()->setShipmentFilter($shipment->getId());
                $collection->addItem($track);

                $shipment->setData(ShipmentInterface::TRACKS, $collection->getItems());
                $shipment->getResource()->save($shipment);

                $comment = __(
                    'The outbound shipment to Parcelninja was %1 submitted successfully. Outbound ID: %2.',
                    $manual,
                    $outboundId
                );

                $result['status'] = 'success';
                $result['message'] = $comment;
            }
        } catch (\Exception $e) {
            // Cannot throw exceptions at this stage
            $this->helper->handleException($e);
            $comment = __(
                'The outbound shipment to Parcelninja failed. Error: %1',
                $e->getMessage()
            );

            $result['status'] = 'error';
            $result['message'] = $comment;
        }

        try {
            if ($comment !== '') {
                $shipment->getOrder()->addStatusHistoryComment($comment);
                $shipment->getOrder()->getResource()->save($shipment->getOrder());
            }
        } catch (\Exception $e) {
            // Cannot throw exceptions at this stage
            $this->helper->handleException($e);

            $result['status'] = 'error';
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Prepare order to dropship
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string[]
     */
    public function prepareOrderToDropship($order)
    {
        $orderRef = $order->getIncrementId();
        $items = [];

        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct();

            if ($product && !$item->getIsVirtual()) {
                $items[] = [
                    'supplier_id'   => (int)$product->getData('brand'),
                    'sku'           => $product->getSku(),
                    'barcode'       => $product->getData('barcode'),
                    'description'   => $product->getName(),
                    'options'       => '',
                    'qty'           => (int)$item->getQtyOrdered()
                ];
            }
        }

        $result = $this->dropshipService->postOrder($orderRef, $items);

        return $this->dropshipService->fetchResponse('pending_order', $result);
    }
}