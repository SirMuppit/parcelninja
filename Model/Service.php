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

use Fontera\Parcelninja\Model\Service\AbstractService;
use Fontera\Parcelninja\Model\Service\Validator\Argument;
use Magento\Framework\Exception\LocalizedException;
use Fontera\Parcelninja\Model\Service\Response;
use Zend\Http\Request;

/**
 * Class Service
 * @package Fontera\Parcelninja\Model
 */
class Service extends AbstractService
{
    const TYPE_ID_INBOUND_STOCK = 1;
    const TYPE_ID_OUTBOUND_STOCK = 2;
    const TYPE_ID_INBOUND_RMA = 3;
    const TYPE_ID_OUTBOUND_RSO = 4;

    const INVENTORY_SORT_FIELD_NONE = 0;
    const INVENTORY_SORT_FIELD_ITEMNO = 1;
    const INVENTORY_SORT_FIELD_ITEMNAME = 2;
    const INVENTORY_SORT_FIELD_INSTOCK = 3;
    const INVENTORY_SORT_FIELD_ALLOCATED = 4;
    const INVENTORY_SORT_FIELD_UNALLOCATED = 5;
    const INVENTORY_SORT_FIELD_ONREORDER = 6;
    const INVENTORY_SORT_FIELD_BROKEN = 7;
    const INVENTORY_SORT_FIELD_RESERVED = 8;
    const INVENTORY_SORT_FIELD_COSTPRICE = 9;

    const INVENTORY_SORT_DIR_DESC = 'desc';
    const INVENTORY_SORT_DIR_ASC = 'asc';

    const INVENTORY_FIELD_FILTER_NONE = 0;
    const INVENTORY_FIELD_FILTER_INSTOCK = 1;
    const INVENTORY_FIELD_FILTER_ALLOCATED = 2;
    const INVENTORY_FIELD_FILTER_UNALLOCATED = 3;
    const INVENTORY_FIELD_FILTER_ONREORDER = 4;
    const INVENTORY_FIELD_FILTER_BROKEN = 5;
    const INVENTORY_FIELD_FILTER_RESERVED = 6;

    const INVENTORY_RESERVE_ADD = 1;
    const INVENTORY_RESERVE_REMOVE = 2;

    const EVENT_STATUSES_ITEMS = 1;
    const EVENT_STATUSES_ORDERS = 2;

    /**
     * Process request
     *
     * @param string $method
     * @param [] $params
     *
     * @throws \Exception|LocalizedException
     * @return \Fontera\Parcelninja\Model\Service\Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processRequest($method, $params)
    {
        try {
            if (!is_string($method)) {
                throw new LocalizedException(__('The method is invalid and should be a string.'));
            }

            if (!method_exists($this, $method)) {
                throw new LocalizedException(__('The method "%1" does not exist.', $method));
            }

            $reflectionMethod = new \ReflectionMethod($this, $method);
            $methodParams = $reflectionMethod->getParameters();

            $passableArgs = [];

            /** @var \ReflectionParameter $methodParam */
            foreach ($methodParams as $methodParam) {
                if (!empty($params[$methodParam->getName()])) {
                    $passableArgs[$methodParam->getName()] = $params[$methodParam->getName()];
                } else {
                    // Check required arg
                    if (!$methodParam->isOptional()) {
                        throw new LocalizedException(
                            __('Parameter "%1" is a required field.', $methodParam->getName())
                        );
                    } else {
                        $passableArgs[$methodParam->getName()] = $methodParam->getDefaultValue();
                    }
                }
            }

            $result = call_user_func_array([$this, $method], $passableArgs);

        } catch (\Exception $e) {
            $result = new Response();
            $result->setInternalError($e->getMessage());
        }

        // Save service log
        try {
            $this->serviceLog->setMethodName($method);

            if ($result instanceof Response) {
                $this->serviceLog->setRequestAction($result->getRequestAction());
                $this->serviceLog->setRequestType($result->getRequestMethod());
                $this->serviceLog->setRequestHeaderContentType($result->getRequestHeaderContentType());
                $this->serviceLog->setRequestHeaderAccept($result->getRequestHeaderAccept());
                $this->serviceLog->setRequestBody($result->getRequestBody());
                $this->serviceLog->setResponseHeader($result->getResponseHeaders());
                $this->serviceLog->setResponseHeaderHttpCode($result->getResponseHttpCode());
                $this->serviceLog->setApiLogId($result->getApiLogId());

                $resultState = $result->getInternalError() || ($result->isError()) ?
                    'error' : 'success';

                $this->serviceLog->setResultState($resultState);
                $this->serviceLog->setInternalError($result->getInternalError());

                // Set error message
                if ($result->isError()) {
                    $parsedResponse = $result->getParsedResponse();

                    if (!empty($parsedResponse['message'])) {
                        $this->serviceLog->setResultMessage(trim($parsedResponse['message']));
                    }
                }
            } else {
                $this->serviceLog->setResultState('error');
                $this->serviceLog->setResultMessage('Unknown result return.');
            }

            $this->serviceLog->save();
        } catch (\Exception $e) {
            $this->helper->debug(sprintf('Exception: %s', $e->getMessage()));
        }

        $this->helper->debug('Request response object:');
        $this->helper->debug($result);

        return $result;
    }

    /**
     * Get inbounds
     *
     * Name:        Retrieve inbounds
     * Action:      inbounds
     * Method:      GET
     * Description: Returns a summary of inbounds in a given time period.
     *
     * @param string $startDate (Format YYYYMMDD - Date from) required
     * @param string $endDate (Format YYYYMMDD - Date to) required
     * @param int $pageSize (Number of records per page) required
     * @param int $page (Page number) required
     * @param int $orderTypeId (Filter on type of order. Get via /lookup/getOrderTypes) optional
     * @param string $search (Search on multiple fields) optional
     * @param int $startRange (Min order number filter, returns all orders above value) optional
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getInbounds(
        $startDate = '', $endDate = '', $pageSize = 20, $page = 1, $orderTypeId = 0, $search = '', $startRange = 0
    ) {

        $params = [
            'startDate'     => (!$startDate || strlen($startDate) !== 8) ? date('Ymd') : $startDate,
            'endDate'       => (!$endDate || strlen($endDate) !== 8) ? date('Ymd') : $endDate,
            'pageSize'      => $pageSize,
            'page'          => $page,
            'orderTypeId'   => $orderTypeId,
            'search'        => $search,
            'startRange'    => $startRange
        ];

        if ($params['orderTypeId'] == 0) {
            unset($params['orderTypeId']);
        }

        if ($params['search'] == '') {
            unset($params['search']);
        }

        if ($params['startRange'] == 0) {
            unset($params['startRange']);
        }

        return $this->request('inbounds', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Get inbound
     *
     * Name:        Retrieve an inbound
     * Action:      inbounds
     * Method:      GET
     * Description: Returns an inbound with minimal details.
     *              If you would like to find an inbound by your custom order number that you created it with, then
     *              you may include it as the value for the header X-Client-Id. This will override the regular
     *              Parcelninja-Inbound-Id.
     *
     * @param int $inboundId (ID of the inbound) required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getInbound($inboundId)
    {
        $inboundId = $this->getValidator()->validateArgument('id', $inboundId, Argument::TYPE_INT)->getValue();

        return $this->request('inbounds', Request::METHOD_GET, [], $inboundId);
    }

    /**
     * Delete inbound
     *
     * Name:        Delete an inbound
     * Action:      inbounds
     * Method:      DELETE
     * Description: Deletes an inbound. Please first check the Inbound with event history.
     *              If the latest Event code is greater than 200, it can not be cancelled.
     *
     * @param int $inboundId (ID of the inbound) required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function deleteInbound($inboundId)
    {
        $inboundId = $this->getValidator()->validateArgument('id', $inboundId, Argument::TYPE_INT)->getValue();

        return $this->request('inbounds', Request::METHOD_DELETE, [], $inboundId);
    }

    /**
     * Get inbound with events
     *
     * Name:        Retrieve inbound with events
     * Action:      inbounds
     * Method:      GET
     * Description: Returns an inbound with full event history.
     *
     * @param int $inboundId (ID of the inbound) Required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getInboundWithEvents($inboundId)
    {
        $inboundId = $this->getValidator()->validateArgument('id', $inboundId, Argument::TYPE_INT)->getValue();

        return $this->request('inbounds', Request::METHOD_GET, [], $inboundId . '/events');
    }

    /**
     * Create inbound stock order
     *
     * Name:        Create Inbound
     * Action:      inbounds
     * Method:      POST
     * Description: When a new inbound is created, the internal id is returned in the header as
     *              x-parcelninja-inbound-id. This is the id that should be used when making lookup requests.
     *
     * @param string $purchaseOrderNo (Purchase order number) Required
     * @param string(50) $customerName (Customer full name) Required
     * @param string(8) $estimatedArrivalDate (Format YYYYMMDD - Estimated Arrival date for Inbound) Required
     * @param string[] $items Required
     *      $items[]['sku'] - string(50) Required
     *      $items[]['productId'] - string(255)
     *      $items[]['name'] - string(255) Required
     *      $items[]['imageUrl'] - string(255)
     *      $items[]['barcode'] - string(100) Required
     *      $items[]['qty'] - number (Default = 1)
     *      $items[]['costPrice'] - number
     *      $items[]['sellingPrice'] - number
     *      $items[]['returnReason'] - string (If $isRma = true)
     *      $items[]['returnDetail'] - string (If $isRma = true)
     * @param bool $isRma
     *
     * @throws LocalizedException
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function createInbound($purchaseOrderNo, $customerName, $estimatedArrivalDate, $items, $isRma = false)
    {
        $params['clientId'] = $this->getValidator()
            ->validateArgument('purchaseOrderNo', $purchaseOrderNo, Argument::TYPE_STRING)->getValue();

        $params['typeId'] = $isRma ? $this::TYPE_ID_INBOUND_RMA : $this::TYPE_ID_INBOUND_STOCK;

        // Delivery info
        $params['deliveryInfo']['customer'] = $this->getValidator()
            ->validateArgument('customerName', $customerName, Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['estimatedArrivalDate'] = $this->getValidator()
            ->validateArgument(
                'estimatedArrivalDate',
                $estimatedArrivalDate,
                Argument::TYPE_STRING,
                true,
                false,
                date('Ymd')
            )
            ->compareStrLen('!==', 8, 'Use "YYYYMMDD" format.')->getValue();

        // Items
        $items = $this->getValidator()
            ->validateArgument('items', $items, Argument::TYPE_ARRAY)->getValue();

        foreach ($items as $idx => $item) {
            try {
                $params['items'][$idx]['itemNo'] = $this->getValidator()
                    ->validateArrayItem($item, 'sku', Argument::TYPE_STRING)
                    ->compareStrLen('>', 50)->getValue();

                $params['items'][$idx]['clientId'] = $this->getValidator()
                    ->validateArrayItem($item, 'productId', Argument::TYPE_STRING, true, false)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['name'] = $this->getValidator()
                    ->validateArrayItem($item, 'name', Argument::TYPE_STRING)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['imageURL'] = $this->getValidator()
                    ->validateArrayItem($item, 'imageUrl', Argument::TYPE_STRING, true, false)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['barcode'] = $this->getValidator()
                    ->validateArrayItem($item, 'barcode', Argument::TYPE_STRING)
                    ->compareStrLen('>', 100)->getValue();

                $params['items'][$idx]['qty'] = $this->getValidator()
                    ->validateArrayItem($item, 'qty', Argument::TYPE_NUMERIC, true, false, 1)->getValue();

                $params['items'][$idx]['costPrice'] = $this->getValidator()
                    ->validateArrayItem($item, 'costPrice', Argument::TYPE_NUMERIC, true, false)->getValue();

                $params['items'][$idx]['sellingPrice'] = $this->getValidator()
                    ->validateArrayItem($item, 'sellingPrice', Argument::TYPE_NUMERIC, true, false)->getValue();

                if ($isRma) {
                    $params['items'][$idx]['returnReason'] = $this->getValidator()
                        ->validateArrayItem($item, 'returnReason', Argument::TYPE_STRING, true, false)->getValue();

                    $params['items'][$idx]['returnDetail'] = $this->getValidator()
                        ->validateArrayItem($item, 'returnDetail', Argument::TYPE_STRING, true, false)->getValue();
                }
            } catch (\Exception $e) {
                throw new LocalizedException(__('Array item[%1] is invalid: %2', $idx, $e->getMessage()));
            }
        }

        return $this->request('inbounds', Request::METHOD_POST, $params);
    }

    /**
     * Get outbounds
     *
     * Name:        Retrieve outbounds
     * Action:      outbounds
     * Method:      GET
     * Description: Returns a summary of outbounds.
     *
     * @param string $startDate (Format YYYYMMDD - Date from) required
     * @param string $endDate (Format YYYYMMDD - Date to) required
     * @param int $pageSize (Number of records per page) required
     * @param int $page (Page number) required
     * @param int $orderTypeId (Filter on type of order. Get via /lookup/getOrderTypes) optional
     * @param string $search (Search on multiple fields) optional
     * @param int $startRange (Min order number filter, returns all orders above value) optional
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getOutbounds(
        $startDate = '', $endDate = '', $pageSize = 20, $page = 1, $orderTypeId = 0, $search = '', $startRange = 0
    ) {

        $params = [
            'startDate'     => (!$startDate || strlen($startDate) !== 8) ? date('Ymd') : $startDate,
            'endDate'       => (!$endDate || strlen($endDate) !== 8) ? date('Ymd') : $endDate,
            'pageSize'      => $pageSize,
            'page'          => $page,
            'orderTypeId'   => $orderTypeId,
            'search'        => $search,
            'startRange'    => $startRange
        ];

        if ($params['orderTypeId'] == 0) {
            unset($params['orderTypeId']);
        }

        if ($params['search'] == '') {
            unset($params['search']);
        }

        if ($params['startRange'] == 0) {
            unset($params['startRange']);
        }

        return $this->request('outbounds', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Get outbound
     *
     * Name:        Retrieve an outbound
     * Action:      outbounds
     * Method:      GET
     * Description: Returns a specific outbound with minimal data.
     *              If you would like to find an outbound by your custom order number that you created it with, then you
     *              may include it as the value for the header X-Client-Id. This will override the regular
     *              Parcelninja-Outbound-Id.
     *
     * @param int $outboundId (ID of the outbound) required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getOutbound($outboundId)
    {
        $outboundId = $this->getValidator()->validateArgument('id', $outboundId, Argument::TYPE_INT)->getValue();

        return $this->request('outbounds', Request::METHOD_GET, [], $outboundId);
    }

    /**
     * Delete outbound
     *
     * Name:        Delete an outbound
     * Action:      outbounds
     * Method:      DELETE
     * Description: Deletes an outbound. Please first check the Outbound with event history.
     *              If the Event code list contains a 241 status code, it can not be cancelled.
     *
     * @param int $outboundId (ID of the outbound) required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function deleteOutbound($outboundId)
    {
        $outboundId = $this->getValidator()->validateArgument('id', $outboundId, Argument::TYPE_INT)->getValue();

        return $this->request('outbounds', Request::METHOD_DELETE, [], $outboundId);
    }

    /**
     * Get outbound with events
     *
     * Name:        Retrieve an outbound with event history
     * Action:      outbounds
     * Method:      GET
     * Description: Returns an outbound with event history.
     *
     * @param int $outboundId (ID of the outbound) Required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getOutboundWithEvents($outboundId)
    {
        $outboundId = $this->getValidator()->validateArgument('id', $outboundId, Argument::TYPE_INT)->getValue();

        return $this->request('outbounds', Request::METHOD_GET, [], $outboundId . '/events');
    }

    /**
     * Create outbound
     *
     * Name:        Create outbound
     * Action:      outbounds
     * Method:      POST
     * Description: When a new outbound is created, the internal id is returned in the header as
     *              x-parcelninja-outbound-id. This is the id that should be used when making lookup requests.
     *
     * @param string $orderNumber (Order number) Required
     * @param string[] $deliveryInfo Required (Customer's delivery info)
     *      $deliveryInfo['customer'] string(50) Required (Customer's full name)
     *      $deliveryInfo['email'] string(50)
     *      $deliveryInfo['contactNo'] string(10) Required
     *      $deliveryInfo['addressLine1'] string(50) Required
     *      $deliveryInfo['addressLine2'] string(50)
     *      $deliveryInfo['suburb'] string(50) Required
     *      $deliveryInfo['postalCode'] string(50) Required
     * @param $items[] $items Required (List of items)
     *      $items[]['sku'] string(50) Required
     *      $items[]['name'] string(255)
     *      $items[]['qty'] number (Default: 1)
     *      $items[]['allocateFromReserve'] bool (If 1 then allocate item from reserved stock)
     * @param int $deliveryQuoteId (From method getDeliveryQuote) (Default: 0 = cheapest delivery option) Required
     * @param bool $collectAtWarehouse (Order to be collected at Warehouse) (Default: false)
     * @param string $channelId (Default: '')
     * @param bool $isRso (Return Stock Order) (Default: false)
     *
     * @throws LocalizedException
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function createOutbound(
        $orderNumber, $deliveryInfo, $items, $deliveryQuoteId = 0, $collectAtWarehouse = false, $channelId = '',
        $isRso = false
    ) {
        $params['clientId'] = $this->getValidator()
            ->validateArgument('orderNumber', $orderNumber, Argument::TYPE_STRING)->getValue();

        $params['typeId'] = $isRso ? $this::TYPE_ID_OUTBOUND_RSO : $this::TYPE_ID_OUTBOUND_STOCK;

        $params['channelId'] = $this->getValidator()
            ->validateArgument('channelId', $channelId, Argument::TYPE_STRING, true, false)->getValue();

        // Delivery info
        $deliveryInfo = $this->getValidator()
            ->validateArgument('deliveryInfo', $deliveryInfo, Argument::TYPE_ARRAY)->getValue();

        $params['deliveryInfo']['customer'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'customer', Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['email'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'email', Argument::TYPE_STRING, true, false)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['contactNo'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'contactNo', Argument::TYPE_STRING)
            ->compareStrLen('!==', 10)->getValue();

        $params['deliveryInfo']['addressLine1'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'addressLine1', Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['addressLine2'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'addressLine2', Argument::TYPE_STRING, true, false)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['suburb'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'suburb', Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['postalCode'] = $this->getValidator()
            ->validateArrayItem($deliveryInfo, 'postalCode', Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInfo']['deliveryOption']['deliveryQuoteId'] = $this->getValidator()
            ->validateArgument('deliveryQuoteId', $deliveryQuoteId, Argument::TYPE_NUMERIC, true, false, 0)->getValue();

        $params['deliveryInfo']['ForCollection'] = $this->getValidator()
            ->validateArgument('collectAtWarehouse', $collectAtWarehouse, Argument::TYPE_BOOL, true, false)
            ->getValue();

        // Items
        $items = $this->getValidator()
            ->validateArgument('items', $items, Argument::TYPE_ARRAY)->getValue();

        foreach ($items as $idx => $item) {
            try {
                $params['items'][$idx]['itemNo'] = $this->getValidator()
                    ->validateArrayItem($item, 'sku', Argument::TYPE_STRING)
                    ->compareStrLen('>', 50)->getValue();

                $params['items'][$idx]['name'] = $this->getValidator()
                    ->validateArrayItem($item, 'name', Argument::TYPE_STRING, true, false)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['qty'] = $this->getValidator()
                    ->validateArrayItem($item, 'qty', Argument::TYPE_NUMERIC, true, false, 1)->getValue();

                $params['items'][$idx]['fromReserve'] = $this->getValidator()
                    ->validateArrayItem($item, 'allocateFromReserve', Argument::TYPE_NUMERIC, true, false)->getValue();

            } catch (\Exception $e) {
                throw new LocalizedException(__('Array item[%1] is invalid: %2', $idx, $e->getMessage()));
            }
        }

        return $this->request('outbounds', Request::METHOD_POST, $params);
    }

    /**
     * Get full inventory
     *
     * Name:        Retrieve full inventory
     * Action:      inventory
     * Method:      GET
     * Description: List of Inventory
     *
     * @param int $pageSize (Number of records per page) required
     * @param int $page (Page number) required
     * @param string $search (Search on multiple fields, eg: ItemNo and ItemName) optional
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getFullInventory($pageSize = 20, $page = 1, $search = '')
    {
        $params = [
            'pageSize'      => $pageSize,
            'page'          => $page,
            'search'        => $search
        ];

        if ($params['search'] == '') {
            unset($params['search']);
        }

        return $this->request('inventory', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Get subset inventory
     *
     * Name:        Retrieve a subset of inventory
     * Action:      inventory
     * Method:      GET
     * Description: This endpoint allows you to retrieve information about a supplied list of SKUs. You can paginate,
     *              sort and filter these SKUs for easier view display.
     *
     * @param string[] $skuList (A list of SKUs to search for) required
     * @param int $pageSize (Number of records per page) required
     * @param int $page (Page number) required
     * @param int $sortField optional
     * @param string $sortDirection optional
     * @param int $fieldFilter optional
     *
     * @todo Currently returns: The requested resource does not support http method 'GET'. Will need to contact PN.
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getSubsetInventory(
        $skuList, $pageSize = 20, $page = 1, $sortField = self::INVENTORY_SORT_FIELD_NONE,
        $sortDirection = self::INVENTORY_SORT_DIR_ASC, $fieldFilter = self::INVENTORY_FIELD_FILTER_NONE
    ) {
        $bodyParams = [
            'sortField'     => $sortField,
            'sortDirection' => $sortDirection,
            'fieldFilter'   => $fieldFilter
        ];

        $skuList = $this->getValidator()
            ->validateArgument('skuList', $skuList, Argument::TYPE_ARRAY)->getValue();

        foreach ($skuList as $sku) {
            $sku = $this->getValidator()->validateArgument('sku', $sku, Argument::TYPE_STRING)->getValue();
            $bodyParams['skuList'][] = ['itemNo' => $sku];
        }

        $urlParams = [
            'pageSize'      => $pageSize,
            'page'          => $page
        ];

        return $this->request('inventory', Request::METHOD_GET, $bodyParams, $this->buildQuery($urlParams, 'subset?'));
    }

    /**
     * Get SKU
     *
     * Name:        Retrieve a single SKU
     * Action:      inventory
     * Method:      GET
     * Description: Specific SKU
     *
     * @param string $sku required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getSku($sku)
    {
        $sku = $this->getValidator()->validateArgument('sku', $sku, Argument::TYPE_STRING)->getValue();

        return $this->request('inventory', Request::METHOD_GET, [], $sku);
    }

    /**
     * Reserve inventory
     *
     * Name:        Reserve inventory
     * Action:      inventory/reserve
     * Method:      POST
     * Description: Reserve inventory.
     *
     * @param string $sku Required
     * @param int|number $qty Required
     * @param bool $remove (Default: false)
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function reserveInventory($sku, $qty = 1, $remove = false)
    {
        $params['itemNo'] = $this->getValidator()
            ->validateArgument('sku', $sku, Argument::TYPE_STRING)->getValue();

        $params['qty'] = $this->getValidator()
            ->validateArgument('qty', $qty, Argument::TYPE_NUMERIC, true, false, 1)->getValue();

        $params['action'] = self::INVENTORY_RESERVE_ADD;

        if ($remove) {
            $params['action'] = self::INVENTORY_RESERVE_REMOVE;
        }

        return $this->request('inventory/reserve', Request::METHOD_POST, $params);
    }

    /**
     * Get delivery quotes
     *
     * Name:        Get List of Delivery Quotes
     * Action:      delivery/quote
     * Method:      POST
     * Description: Return a granular list of delivery quotes ordered by the fastest option. In this case, some SKUs are
     *              out of stock but you have supplied dimensions.
     *
     * @param string(50) $postalCode required
     * @param string(50) $suburb required
     * @param string[] $items required
     *      $items[]['sku'] string(50) Required
     *      $items[]['qty'] int|number
     *      $items[]['allocateFromReserve'] bool (If 1 then allocate item from reserved stock)
     *      $items[]['width'] int|number (mm/millimeters) Required if sku is out of stock or does not exist
     *      $items[]['length'] int|number (mm/millimeters) Required if sku is out of stock or does not exist
     *      $items[]['height'] int|number (mm/millimeters) Required if sku is out of stock or does not exist
     *      $items[]['weight'] int|number (g/grams) Required if sku is out of stock or does not exist
     * @param bool $returnCheapest (Default: false)
     *
     * @throws LocalizedException
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getDeliveryQuotes($postalCode, $suburb, $items, $returnCheapest = false)
    {
        // Delivery info
        $params['deliveryInformation']['postalCode'] = $this->getValidator()
            ->validateArgument('postalCode', $postalCode, Argument::TYPE_STRING)
            ->compareStrLen('>', 50)->getValue();

        $params['deliveryInformation']['suburb'] = $this->getValidator()
            ->validateArgument('suburb', $suburb, Argument::TYPE_STRING, true, false)
            ->compareStrLen('>', 50)->getValue();

        // Items
        $items = $this->getValidator()
            ->validateArgument('items', $items, Argument::TYPE_ARRAY)->getValue();

        foreach ($items as $idx => $item) {
            try {
                $params['items'][$idx]['sku'] = $this->getValidator()
                    ->validateArrayItem($item, 'sku', Argument::TYPE_STRING)
                    ->compareStrLen('>', 50)->getValue();

                $params['items'][$idx]['quantity'] = $this->getValidator()
                    ->validateArrayItem($item, 'qty', Argument::TYPE_NUMERIC, true, false, 1)->getValue();

                $params['items'][$idx]['fromReserve'] = $this->getValidator()
                    ->validateArrayItem($item, 'allocateFromReserve', Argument::TYPE_BOOL, true, false)->getValue();

                $params['items'][$idx]['dimensions']['width'] = $this->getValidator()
                    ->validateArrayItem($item, 'width', Argument::TYPE_NUMERIC, true, false)->getValue();

                $params['items'][$idx]['dimensions']['length'] = $this->getValidator()
                    ->validateArrayItem($item, 'length', Argument::TYPE_NUMERIC, true, false)->getValue();

                $params['items'][$idx]['dimensions']['height'] = $this->getValidator()
                    ->validateArrayItem($item, 'height', Argument::TYPE_NUMERIC, true, false)->getValue();

                $params['items'][$idx]['dimensions']['weight'] = $this->getValidator()
                    ->validateArrayItem($item, 'weight', Argument::TYPE_NUMERIC, true, false)->getValue();

                $dms = $params['items'][$idx]['dimensions'];

                if (!$dms['width'] || !$dms['length'] || !$dms['height'] || !$dms['weight']) {
                    unset($params['items'][$idx]['dimensions']);
                    unset($dms);
                }

            } catch (\Exception $e) {
                throw new LocalizedException(__('Array item[%1] is invalid: %2', $idx, $e->getMessage()));
            }
        }

        $action = 'delivery/quote';

        if ($returnCheapest) {
            $action = 'delivery/quote/cheapest';
        }

        return $this->request($action, Request::METHOD_POST, $params);
    }

    /**
     * Check delivery quote / nominate date
     *
     * Name:        Nominate Date / Check Delivery Quote
     * Action:      delivery/CheckQuote
     * Method:      POST
     * Description: Check the information surrounding a delivery quote or nominate a date for a given service offering.
     *
     * In the first case, only the DeliveryQuoteId of interest need be provided and the related Delivery Quote object
     * will be returnedIn.
     *
     * The second case a nominated date must be provided along with a quoteId with the relevant service offering
     * (e.g. if the nominated date is after hours the quoteId must refer to an after hours service offering), if this
     * nominated date is fulfillable a new delivery quote will be generated and returned, if this is not possible an
     * error message shall result instead.Please note this newly generated delivery quote may not be altered and must
     * be used when creating an outbound. If a different nominated date is required please use the original
     * DeliveryQuoteId for that nominated service offering.
     *
     * @param int|number $deliveryQuoteId required
     * @param string $nominatedDate
     *
     * @todo This method needs further testing for nominatedDate as there are specific rules required for this to work.
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function checkDelivery($deliveryQuoteId, $nominatedDate = '')
    {
        $params['deliveryQuoteId'] = $this->getValidator()
            ->validateArgument('deliveryQuoteId', $deliveryQuoteId, Argument::TYPE_NUMERIC)->getValue();

        if ($nominatedDate !== '') {
            $params['nominatedDate'] = $this->getValidator()
                ->validateArgument('nominatedDate', $nominatedDate, Argument::TYPE_STRING)
                ->compareStrLen('>', 16, 'Use "YYYY/MM/DDTH:i" format (eg 2015/06/01T08:38).')
                ->compareStrLen('<', 14, 'Use "YYYY/MM/DDTH:i" format (eg 2015/06/01T08:38).')->getValue();
        }

        return $this->request('delivery/CheckQuote', Request::METHOD_POST, $params);
    }

    /**
     * Get waybill tracking
     *
     * Name:        Track waybill / Track waybill with event history
     * Action:      tracking
     * Method:      GET
     * Description: Returns the last updated courier tracking status for a waybill.
     *
     * @param string $waybillNo required
     * @param bool $includeEvents Returns the full courier tracking information for a waybill (Default: false)
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getWaybillTracking($waybillNo, $includeEvents = false)
    {
        $waybillNo = $this->getValidator()
            ->validateArgument('waybillNo', $waybillNo, Argument::TYPE_STRING)->getValue();

        $urlParams = $waybillNo;

        if ($includeEvents) {
            $urlParams .= '/events';
        }

        return $this->request('tracking', Request::METHOD_GET, [], $urlParams);
    }

    /**
     * Get order types
     *
     * Name:        Retrieve order types
     * Action:      lookups/getOrderTypes
     * Method:      GET
     * Description: Returns a list of order types.
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getOrderTypes()
    {
        return $this->request('lookups/getOrderTypes', Request::METHOD_GET);
    }

    /**
     * Get event statuses
     *
     * Name:        Retrieve Event Statuses
     * Action:      lookups/getEventTypes
     * Method:      GET
     * Description: Returns a list of event statuses
     *
     * @param int $typeId (1 = item events, 2 = order events) (Default: 1)
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getEventStatuses($typeId = self::EVENT_STATUSES_ITEMS)
    {
        $params['typeId'] = $this->getValidator()->validateArgument('typeId', $typeId, Argument::TYPE_INT)->getValue();

        return $this->request('lookups/getEventTypes', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Get suburbs
     *
     * Name:        Retrieve Suburbs and postal codes
     * Action:      lookups/getSuburbs
     * Method:      GET
     * Description: Returns all suburbs if search parameter not supplied.
     *
     * @param string $search (Default = '')
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getSuburbs($search = '')
    {
        $params['search'] = $this->getValidator()
            ->validateArgument('search', $search, Argument::TYPE_STRING, true, false)->getValue();

        $urlParams = '';

        if ($params['search'] !== '') {
            $urlParams = $this->buildQuery($params, '?');
        }

        return $this->request('lookups/getSuburbs', Request::METHOD_GET, [], $urlParams);
    }

    /**
     * Get log
     *
     * Name:        Retrieve a Log
     * Action:      logs
     * Method:      GET
     * Description: Returns specific log.
     *
     * @param string $logId (GUID) Required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getLog($logId)
    {
        $logId = $this->getValidator()->validateArgument('logId', $logId, Argument::TYPE_STRING)->getValue();

        return $this->request('logs', Request::METHOD_GET, [], $logId);
    }

    /**
     * Create callback URL
     *
     * Name:        Create Callback Hook
     * Action:      hooks
     * Method:      POST
     * Description: Set a custom URL that hooks should be sent to.
     *
     * @param string $hookUrl (URL of the service that hooks should be sent to) Required
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function createCallbackUrl($hookUrl)
    {
        $params['hookUrl'] = $this->getValidator()
            ->validateArgument('hookUrl', $hookUrl, Argument::TYPE_STRING)->getValue();

        return $this->request('hooks', Request::METHOD_POST, $params);
    }

    /**
     * Get callback URL
     *
     * Name:        Retrieve Callback URL
     * Action:      hooks/getCallback
     * Method:      GET
     * Description: Retrieve the callback URL defined for a site.
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getCallbackUrl()
    {
        return $this->request('hooks/getCallback', Request::METHOD_GET);
    }
}