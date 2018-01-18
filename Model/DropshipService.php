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
 * Class DropshipService
 * @package Fontera\Parcelninja\Model
 */
class DropshipService extends AbstractService
{
    /**
     * Request
     *
     * @param string $action
     * @param string $method
     *      GET
     *      PUT
     *      POST
     *      DELETE
     * @param string[] $params
     * @param string $urlSuffix
     *
     * @return Response $response
     * @throws \Exception|LocalizedException
     */
    protected function request($action, $method, $params = [], $urlSuffix = '')
    {
        $response = new Response();
        $response->setRequestMethod($method);

        try {
            $this->getValidator()
                ->validateArgument('action', $action, Argument::TYPE_STRING);
            $this->getValidator()
                ->validateArgument('method', $method, Argument::TYPE_STRING)->allowed($this->allowedMethods);
            $this->getValidator()
                ->validateArgument('username', $this->helper->getDropshipApiToken(), Argument::TYPE_STRING);

            $stack = debug_backtrace();

            if (!empty($stack[1]['function'])) {
                $this->helper->debug(sprintf('Request: %s', $stack[1]['function']));
            }

            unset($stack);

            $this->helper->debug(sprintf('Request action: %s', $action));
            $this->helper->debug(sprintf('Request method: %s', $method));

            // Build headers
            $headers = [
                'Content-Type'      => self::HEADER_CONTENT_TYPE,
                'Accept'            => self::HEADER_ACCEPT,
                'X-AUTHORIZE-KEY'   => $this->helper->getDropshipApiToken()
            ];
            $response->setRequestHeaderContentType(self::HEADER_CONTENT_TYPE);
            $response->setRequestHeaderAccept(self::HEADER_ACCEPT);

            $this->helper->debug('Request headers:');
            $this->helper->debug($headers);

            // Set headers
            $this->getCurlClient()->setHeaders($headers);

            // Set timeout
            $this->getCurlClient()->setTimeout($this->helper->getDropshipApiTimeout());

            // Build URL
            $url = $this->buildUrl($action, $urlSuffix);
            $this->helper->debug(sprintf('Request endpoint: %s', $url));

            if ($method == Request::METHOD_POST) {
                $postBodyType = self::POST_BODY_TYPE;
            } else {
                $postBodyType = '';
            }

            $this->helper->debug('Request params:');
            $this->helper->debug(@json_encode($params, JSON_PRETTY_PRINT));

            // Make request
            $this->getCurlClient()->makeRequest($method, $url, $params, $postBodyType);

            $this->helper->debug($this->getCurlClient());

            // Update response object
            $response->setRequestAction($url);
            $response->setRequestBody(@json_encode($params));
            $response->setResponseHeaders($this->getCurlClient()->getHeaders());
            $response->setResponseHttpCode($this->getCurlClient()->getStatus());
            $response->setResponseBody($this->getCurlClient()->getBody());

        } catch (\Exception $e) {
            $response->setInternalError(sprintf('The API request failed: %s', $e->getMessage()));
            $this->helper->debug(sprintf('Exception: %s', $e->getMessage()));
        }

        // Parse the response
        $response->getParsedResponse();

        return $response;
    }

    /**
     * Build URL
     *
     * @param string $action
     * @param string $urlSuffix
     *
     * @return string
     */
    protected function buildUrl($action, $urlSuffix = '')
    {
        return $this->helper->getDropshipServiceEndpoint() . $action . $urlSuffix;
    }

    /**
     * Fetch Response
     *
     * @param string $key
     * @param Response $response
     * @throws \Exception
     * @return mixed|string[]
     */
    public function fetchResponse($key, $response)
    {
        if ($response instanceof Response) {
            if ($response->getInternalError()) {
                throw new \Exception($response->getInternalError());
            } else {
                $httpCode = $response->getResponseHttpCode();

                if ($response->isError($httpCode)) {
                    if (!empty($parsedResponse['message'])) {
                        throw new \Exception($parsedResponse['message']);
                    } else {
                        throw new \Exception($response->getMessageForCode($httpCode));
                    }
                }
            }

            $result = $response->getParsedResponse();

            if (!empty($result[$key])) {
                return $result[$key];
            }
        }

        throw new \Exception(__('Could not fetch "%1" from response.', $key));
    }

    /**
     * Get purchase orders
     *
     * Name:        Get purchase orders
     * Action:      purchaseorders
     * Method:      GET
     *
     * Filters: offset, limit, is_sent_to_third_party
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getPurchaseOrders()
    {
        $params = ['limit' => '1000'];

        return $this->request('purchaseorders', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Get suppliers
     *
     * Name:        Get all suppliers
     * Action:      suppliers
     * Method:      GET
     *
     * Filters: offset, limit
     *
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    protected function getSuppliers()
    {
        $params = ['limit' => '1000'];

        return $this->request('suppliers', Request::METHOD_GET, [], $this->buildQuery($params, '?'));
    }

    /**
     * Post order
     *
     * Name:        Add a new pending order
     * Action:      order
     * Method:      POST
     *
     * @param string $orderRef (Order number) Required
     * @param string[] $items Required
     *      $items[]['supplier_id'] - int Required
     *      $items[]['sku'] - string Required
     *      $items[]['description'] - string Required
     *      $items[]['options'] - string
     *      $items[]['barcode'] - string Required
     *      $items[]['qty'] - int Required
     *
     * @throws LocalizedException
     * @return \Fontera\Parcelninja\Model\Service\Response
     */
    public function postOrder($orderRef, $items)
    {
        $params['order_ref'] = $this->getValidator()
            ->validateArgument('orderRef', $orderRef, Argument::TYPE_STRING)->getValue();

        // Items
        $items = $this->getValidator()
            ->validateArgument('items', $items, Argument::TYPE_ARRAY)->getValue();

        foreach ($items as $idx => $item) {
            try {
                $params['items'][$idx]['supplier_id'] = $this->getValidator()
                    ->validateArrayItem($item, 'supplier_id', Argument::TYPE_INT)
                    ->compareStrLen('>', 11)->getValue();

                $params['items'][$idx]['sku'] = $this->getValidator()
                    ->validateArrayItem($item, 'sku', Argument::TYPE_STRING)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['description'] = $this->getValidator()
                    ->validateArrayItem($item, 'description', Argument::TYPE_STRING)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['options'] = $this->getValidator()
                    ->validateArrayItem($item, 'options', Argument::TYPE_STRING, true, false)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['barcode'] = $this->getValidator()
                    ->validateArrayItem($item, 'barcode', Argument::TYPE_STRING)
                    ->compareStrLen('>', 255)->getValue();

                $params['items'][$idx]['qty'] = $this->getValidator()
                    ->validateArrayItem($item, 'qty', Argument::TYPE_INT)
                    ->compareStrLen('>', 11)->getValue();
            } catch (\Exception $e) {
                throw new LocalizedException(__('Array item[%1] is invalid: %2', $idx, $e->getMessage()));
            }
        }

        return $this->request('order.json', Request::METHOD_POST, $params);
    }
}