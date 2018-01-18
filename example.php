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

use \Magento\Framework\App\Bootstrap;
use \Magento\Store\Model\StoreManager;

require __DIR__ . '../../../app/bootstrap.php';
$_SERVER[StoreManager::PARAM_RUN_CODE] = 'default';
$bootstrap = Bootstrap::create(BP, $_SERVER);

$obj = $bootstrap->getObjectManager();
/* @var \Magento\Framework\Event\ManagerInterface $eventManager */
$eventManager = $obj->create("\\Magento\\Framework\\Event\\ManagerInterface");

// Pass result object by reference to retrieve result
$result = new \Magento\Framework\DataObject;

// GET INBOUNDS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getInbounds',
        'params'    => [
            'startDate'     => '20160620',
            'endDate'       => '20170629',
            'pageSize'      => 15,
            'page'          => 1
            //'orderTypeId'   => 0,
            //'search'        => '',
            //'startRange'    => 0
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET INBOUND
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getInbound',
        'params'    => [
            'id'     => 1901664 //1901668, 1907010
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// DELETE INBOUND
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'deleteInbound',
        'params'    => [
            'id'     => '1901662'
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET INBOUND WITH EVENTS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getInboundWithEvents',
        'params'    => [
            'id'     => 1901664 //1901668, 1907010
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// CREATE INBOUND STOCK ORDER
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'createInbound',
        'params'    => [
            'purchaseOrderNo'       => '123-B', // Required
            'customerName'          => 'peter Pan', // Required
            'estimatedArrivalDate'  => '20160630', // Required
            'items'                 => [
                0 => [
                    'sku'           => 'AAA-111', // Required
                    'productId'     => '10',
                    'name'          => 'Shampoo', // Required
                    'imageUrl'      => null,
                    'barcode'       => 'AAAA1111', // Required
                    'qty'           => 2, // Required
                    'costPrice'     => null,
                    'sellingPrice'  => null
                ]
            ]
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// CREATE INBOUND STOCK ORDER RMA
/*eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'createInbound',
        'params'    => [
            'poNumber'              => '56789', // Required
            'customerName'          => 'John Snow', // Required
            'estimatedArrivalDate'  => '20171220', // Required
            'items'                 => [
                'sku'                   => 'ACB-123', // Required
                'product_id'            => '',
                'product_name'          => 'My Test Product', // Required
                'product_img_url'       => '',
                'barcode'               => '0987654321', // Required
                'qty'                   => 10, // Required
                'cost_price'            => '',
                'selling_price'         => '',
                'return_reason'         => '',
                'return_detail'         => ''
            ],
            'isRma'                 => true
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET OUTBOUNDS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getOutbounds',
        'params'    => [
            'startDate'     => '20160620',
            'endDate'       => '20170629',
            'pageSize'      => 20,
            'page'          => 1
            //'orderTypeId'   => 0,
            //'search'        => '',
            //'startRange'    => 0
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET OUTBOUND
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getOutbound',
        'params'    => [
            'id'     => '1903574' //1906999, 1907004, 1907005
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// DELETE OUTBOUND
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'deleteOutbound',
        'params'    => [
            'id'     => '1903574'
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET OUTBOUND WITH EVENTS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getOutboundWithEvents',
        'params'    => [
            'id'     => '1903574'
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// CREATE OUTBOUND
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'createOutbound',
        'params'    => [
            'orderNumber'   => 'CO-00003', // Required
            'deliveryInfo'  => [
                'customer'          => 'Test outbound', // Required
                'email'             => 'shaughn@fontera.com',
                'contactNo'         => '0214182811', // Required
                'addressLine1'      => '75c Somerset Square, Somerset Road', // Required
                'addressLine2'      => '',
                'suburb'            => 'Sea Point', // Required
                'postalCode'        => '8001' // Required
            ],
            'items'         => [
                0 => [
                    'sku'                   => 'ACB-123', // Required
                    'name'                  => 'blahhh',
                    'qty'                   => 2,
                    'allocateFromReserve'   => 1
                ]
            ],
            'deliveryQuoteId'   => 0,
            'collectAtWarehouse'    => true
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET FULL INVENTORY
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getFullInventory',
        'params'    => [
            'pageSize'      => 20, // Required
            'page'          => 1, // Required
            //'search'        => '7-KETO'
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET SUBSET INVENTORY
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getSubsetInventory',
        'params'    => [
            'skuList'   => ['123', '321', 'abc'], // Required
            'pageSize'  => 20, // Required
            'page'      => 1, // Required
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET SKU
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getSku',
        'params'    => [
            'sku'   => 'JN1620' // Required
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// RESERVE INVENTORY - ADD
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'reserveInventory',
        'params'    => [
            'sku'       => 'JN1620', // Required
            'qty'       => 2,
            'remove'    => false
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// RESERVE INVENTORY - REMOVE
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'reserveInventory',
        'params'    => [
            'sku'       => 'JN1620', // Required
            'qty'       => 2,
            'remove'    => true
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET DELIVERY QUOTES
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getDeliveryQuotes',
        'params'    => [
            'postalCode'    => '8060', // Required
            'suburb'        => 'Sea Point', // Required
            'items'         => [
                0 => [
                    'sku'                   => 'JN1620', // Required
                    'qty'                   => 2,
                    'allocateFromReserve'   => false
                ],
                1 => [
                    'sku'       => 'JN1621', // Required
                    'width'     => '250', // Required if sku is out of stock or does not exist
                    'length'    => '100', // Required if sku is out of stock or does not exist
                    'height'    => '200', // Required if sku is out of stock or does not exist
                    'weight'    => '800' // Required if sku is out of stock or does not exist
                ]
            ]
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET CHEAPEST DELIVERY QUOTE
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getDeliveryQuotes',
        'params'    => [
            'postalCode'    => '8060', // Required
            'suburb'        => 'Sea Point', // Required
            'items'         => [
                0 => [
                    'sku'                   => 'JN1620', // Required
                    'qty'                   => 2,
                    'allocateFromReserve'   => false
                ],
                1 => [
                    'sku'       => 'JN1621', // Required
                    'width'     => '250', // Required if sku is out of stock or does not exist
                    'length'    => '100', // Required if sku is out of stock or does not exist
                    'height'    => '200', // Required if sku is out of stock or does not exist
                    'weight'    => '800' // Required if sku is out of stock or does not exist
                ]
            ],
            'returnCheapest'    => true
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// CHECK DELIVERY
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'checkDelivery',
        'params'    => [
            'deliveryQuoteId'   => '15112124', // Required
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// NOMINATE DATE
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'checkDelivery',
        'params'    => [
            'deliveryQuoteId'   => '15112124', // Required
            'nominatedDate'     => '2016/07/14T08:00', // Required for nominated service & if you want to nominate date
        ],
        'result'  => $result
    ]
);
$helper->debug($result->getData());*/

// GET WAYBILL TRACKING
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getWaybillTracking',
        'params'    => [
            'waybillNo'   => 'WIA1340707', // Required
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET WAYBILL TRACKING WITH EVENTS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getWaybillTracking',
        'params'    => [
            'waybillNo'     => 'WIA1340707', // Required
            'includeEvents' => true
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET ORDER TYPES
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getOrderTypes',
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET EVENT STATUSES FOR ITEMS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getEventStatuses',
        'params'    => [
            'typeId'    => 1
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET EVENT STATUSES FOR ORDERS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getEventStatuses',
        'params'    => [
            'typeId'    => 2
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET SPECIFIC SUBURBS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getSuburbs',
        'params'    => [
            'search'    => 'Parklands'
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET ALL SUBURBS
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getSuburbs',
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET LOG
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getLog',
        'params'    => [
            'logId'    => 'B409BEC3-5DA1-498B-9D85-6319B066F55C'
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// CREATE CALLBACK URL
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'createCallbackUrl',
        'params'    => [
            'hookUrl'    => 'http://magento2.dev/test'
        ],
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/

// GET CALLBACK URL
/*$eventManager->dispatch(
    'fontera_parcelninja_service',
    [
        'method'    => 'getCallbackUrl',
        'result'    => $result
    ]
);
$helper->debug($result->getData());*/