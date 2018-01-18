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

use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\DataObject as DataObject;
use Magento\Shipping\Model\Rate\Result; // RateFactory

/**
 * Class Carrier
 * @package Fontera\Parcelninja\Model
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * Carrier code
     *
     * @var string
     */
    const CODE = 'parcelninja';

    /**
     * South African country ID
     */
    const ZA_COUNTRY_ID = 'ZA';

    /**
     * Base weight unit
     */
    const BASE_WEIGHT_UNIT = 'g';

    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * {@inheritdoc}
     */
    public function isZipCodeRequired($countryId = null)
    {
        if ($countryId != null) {

            if ($countryId == self::ZA_COUNTRY_ID) {
                return true;
            }

            return !$this->directoryData->isZipCodeOptional($countryId);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function processAdditionalValidation(DataObject $request)
    {
        // Skip if no items
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        //@Todo weight validation

        return $this;
    }

    /**
     * Create shipment request
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    public function createShipmentRequest($shipment)
    {
        $shippingAddress = $shipment->getShippingAddress();
        $items = $shipment->getAllItems();

        // Get suburb
        $suburb = $shippingAddress->getData('suburb');

        if ($suburb || $suburb == '') {
            $suburb = 'Other';
            $addressLineArr = explode(', ', $shippingAddress->getStreetLine(2));

            if (isset($addressLineArr[1])) {
                $suburb = $addressLineArr[1];
            }
        }

        $this->requestParams = new DataObject();
        $this->requestParams->setData('orderNumber', $shipment->getIncrementId());
        $this->requestParams->setData('deliveryInfo', [
            'customer'      => $shippingAddress->getName(),
            'email'         => $shippingAddress->getEmail(),
            'contactNo'     => $shippingAddress->getTelephone(),
            'addressLine1'  => $shippingAddress->getStreetLine(1),
            'addressLine2'  => $shippingAddress->getStreetLine(2),
            'suburb'        => $suburb,
            'postalCode'    => $shippingAddress->getPostcode()
        ]);

        $itemsArray = [];

        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($items as $item) {
            $itemsArray[] = [
                'sku'                   => $item->getSku(),
                'name'                  => $item->getName(),
                'qty'                   => $item->getQty(),
                'allocateFromReserve'   => 0, // @Todo Need to implement config
            ];
        }

        $this->requestParams->setData('items', $itemsArray);

        //@Todo Need to implement config for deliveryQuoteId
        $this->requestParams->setData('deliveryQuoteId', 0); // 0 = cheapest delivery option (for flat rate)
        $this->requestParams->setData('collectAtWarehouse', false); // @Todo Need to implement config
        $this->requestParams->setData('channelId', ''); // @Todo Need to implement config
        $this->requestParams->setData('isRso', false); // @Todo Need to implement config

        // Do request and fetch response
        return $this->fetchResponse($this->doRequest('createOutbound'));
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackingNumbers
     * @return Result|null
     */
    public function getTracking($trackingNumbers)
    {
        if (!is_array($trackingNumbers)) {
            $trackingNumbers = [$trackingNumbers];
        }

        foreach ($trackingNumbers as $trackingNumber) {
            $this->createTrackingRequest($trackingNumber);
        }

        return $this->result;
    }

    /**
     * Create tracking request
     *
     * @param string $trackingNumber
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createTrackingRequest($trackingNumber)
    {
        $this->requestParams = new DataObject();
        $this->requestParams->setData('outboundId', (int)$trackingNumber);

        $trackingData = [
            //'progressdetail'    => [],
            //'weight'            => '0',
            //'service'           => '',
            //'status'            => '',
            //'deliverydate'      => '',
            //'deliverytime'      => '',
            //'deliverylocation'  => '',
            //'signedby'          => '',
            //'shippeddate'       => ''
        ];

        if (!$this->result) {
            $this->result = $this->trackFactory->create();
        }

        try {
            // Do request and fetch response
            $response = $this->fetchResponse(
                $this->doRequest('getOutboundWithEvents')
            );

            $parsedResponse = $response->getParsedResponse();

            // Status
            if (isset($parsedResponse['status']['description'])) {
                $trackingData['status'] = (string)$parsedResponse['status']['description'];
            }

            // Service
            if (isset($parsedResponse['deliveryInfo']['courierName'])) {
                $trackingData['service'] = (string)$parsedResponse['deliveryInfo']['courierName'];

                if (isset($parsedResponse['deliveryInfo']['service']['description'])) {
                    $trackingData['service'] = $trackingData['service']
                        . ' - ' . (string)$parsedResponse['deliveryInfo']['service']['description'];
                }
            }

            // Delivery date and time
            if (isset($parsedResponse['deliveryInfo']['deliveryOption']['deliveryEndDate'])) {
                $trackingData['deliverydate'] = date(
                    'Y-m-d',
                    strtotime((string)$parsedResponse['deliveryInfo']['deliveryOption']['deliveryEndDate'])
                );

                $trackingData['deliverytime'] = date(
                    'H:i:s',
                    strtotime((string)$parsedResponse['deliveryInfo']['deliveryOption']['deliveryEndDate'])
                );
            }

            // Delivery location
            $deliveryLocationArr = [];

            if (isset($parsedResponse['deliveryInfo']['addressLine1'])) {
                $deliveryLocationArr[] = (string)$parsedResponse['deliveryInfo']['addressLine1'];
            }

            if (isset($parsedResponse['deliveryInfo']['addressLine2'])) {
                $deliveryLocationArr[] = (string)$parsedResponse['deliveryInfo']['addressLine2'];
            }

            if (isset($parsedResponse['deliveryInfo']['suburb'])) {
                $deliveryLocationArr[] = (string)$parsedResponse['deliveryInfo']['suburb'];
            }

            if (isset($parsedResponse['deliveryInfo']['postalCode'])) {
                $deliveryLocationArr[] = (string)$parsedResponse['deliveryInfo']['postalCode'];
            }

            if ($deliveryLocationArr) {
                $trackingData['deliverylocation'] = implode(', ', $deliveryLocationArr);
            }

            // Signed by
            $trackingData['signedby'] = '';

            // Shipped date
            if (isset($parsedResponse['status']['code']) && isset($parsedResponse['status']['timeStamp'])) {
                // 245 = Delivered
                if ((int)$parsedResponse['status']['code'] == 245) {
                    $trackingData['shippeddate'] = date(
                        'Y-m-d',
                        strtotime((string)$parsedResponse['status']['timeStamp'])
                    );
                }
            }

            // Weight
            if (isset($parsedResponse['deliveryInfo']['courierBillingInfo']['shippingWeight'])) {
                $weight = (string)$parsedResponse['deliveryInfo']['courierBillingInfo']['shippingWeight'];
                $unit = self::BASE_WEIGHT_UNIT;
                $trackingData['weight'] = "{$weight} {$unit}";
            }

            // Progress
            $progressDetails = [];

            if (!empty($parsedResponse['events'])) {
                foreach ($parsedResponse['events'] as $event) {
                    $eventArr = [];

                    if (isset($event['description'])) {
                        $eventArr['activity'] = (string)$event['description'];
                    }

                    if (isset($event['timeStamp'])) {
                        $timestamp = strtotime((string)$event['timeStamp']);
                        if ($timestamp) {
                            $eventArr['deliverydate'] = date('Y-m-d', $timestamp);
                            $eventArr['deliverytime'] = date('H:i:s', $timestamp);
                        }
                    }

                    $eventArr['deliverylocation'] = ucfirst(self::CODE);

                    $progressDetails[] = $eventArr;
                }
            }

            $trackingData['progressdetail'] = $progressDetails;

            /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */
            $tracking = $this->trackStatusFactory->create();

            $tracking->setData('carrier', $this->_code);
            $tracking->setData('carrier_title', $this->getCarrierTitle());
            $tracking->setData('tracking', $trackingNumber);
            $tracking->addData($trackingData);

            $this->result->append($tracking);

        } catch (\Exception $e) {
            $this->helper->handleException($e);

            $error = $this->trackErrorFactory->create();
            $error->setData('carrier', $this->_code);
            $error->setData('carrier_title', $this->getCarrierTitle());
            $error->setData('tracking', $trackingNumber);
            $error->setData(
                'error_message',
                __('For some reason we can\'t retrieve the tracking info right now. Please try again later.')
            );

            $this->result->append($error);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function requestToShipment($request)
    {
        $this->helper->debug(sprintf('%s not implemented.', __METHOD__));
        return new DataObject();
    }

    /**
     * {@inheritdoc}
     */
    public function returnOfShipment($request)
    {
        $this->helper->debug(sprintf('%s not implemented.', __METHOD__));
        return new DataObject();
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        // Check if we can collect rates
        if (!$this->canCollectRates()) {
            return $this->getError('', true);
        }

        // Check if we can use a flat rate
        if ($this->canUseFlatrate() && $this->getFlatrateAmount() > 0) {
            return $this->getFlatrate();
        }

        $this->setRequest($request);

        if ($this->canUseVolumetricRates() && !empty($this->getVolumetricRates())) {
            return $this->getVolumetricRateResult($this->getVolumetricRates());
        }



        $this->getShippingQuotes();

        return $this->result;
    }

    /**
     * Prepare and set request
     *
     * @param RateRequest $request
     * @return Carrier
     */
    public function setRequest(RateRequest $request)
    {
        $this->request = $request;

        $itemsRequest = [];
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->getAllItems($request) as $item) {
            $itemsRequest[] = [
                'sku'                   => $item['sku'],
                'qty'                   => $item['qty'],
                'allocateFromReserve'   => false, // @todo this should be config value part of inventory
                'width'                 => $item['width'], //required
                'length'                => $item['length'], //required
                'height'                => $item['height'], //required
                'weight'                => $item['weight']
            ];
        }

        $this->requestParams = [
            'postalCode'        => $request->getDestPostcode(),
            'suburb'            => $request->getDestRegionCode(),
            'items'             => $itemsRequest,
            'returnCheapest'    => (bool)$this->getConfigFlag('show_cheapest_method')
        ];

        unset($itemsRequest);

        return $this;
    }

    /**
     * Get shipping quotes
     *
     * @return Result
     * @todo Still need to complete this method and test
     */
    protected function getShippingQuotes()
    {
        $this->result = $this->rateFactory->create();

        // Get from cached quotes
        $requestString = serialize($this->requestParams);

        $result = $this->quotesCache->getCachedQuotes($requestString);

        if ($result === null) {
            try {
                $result = $this->doRequest('getDeliveryQuotes', $this->requestParams);

                // Cache results for current session
                //$this->quotesCache->setCachedQuotes($requestString, serialize($result));
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        } else {
            $result = unserialize($result);
        }

        // Do request and fetch response
        $response = $this->fetchResponse($result);

        /**$preparedResponse = $this->parseResponse($response);

        if (!$preparedResponse->getError() || $this->result->getError() && $preparedResponse->getError()) {
            $this->result->append($preparedResponse);
        }**/

        return $this->result;
    }
}