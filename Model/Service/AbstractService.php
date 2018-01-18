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

namespace Fontera\Parcelninja\Model\Service;

use Fontera\Parcelninja\Helper\Data as Helper;
use Fontera\Parcelninja\Model\Service\Http\Client\Curl;
use Magento\Framework\Exception\LocalizedException;
use Fontera\Parcelninja\Model\Service\Validator\Argument;
use Fontera\Parcelninja\Model\Service\Log as ServiceLog;
use Zend\Http\Request;

/**
 * Class AbstractService
 * @package Fontera\Parcelninja\Model\Service
 */
abstract class AbstractService
{
    /**#@+
     * CONFIGS
     */
    const HEADER_CONTENT_TYPE   = 'application/json';
    const HEADER_ACCEPT         = 'application/json; charset=UTF-8';
    const POST_BODY_TYPE        = 'json';

    /**
     * Allowed request methods
     *
     * @var string[]
     */
    protected $allowedMethods = [
        Request::METHOD_GET,
        Request::METHOD_POST,
        Request::METHOD_DELETE
    ];

    /**
     * Helper
     *
     * @var Helper
     */
    protected $helper;

    /**
     * CURL factory
     *
     * @var Curl
     */
    private $curlClient;

    /**
     * Validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * Service log
     *
     * @var ServiceLog
     */
    protected $serviceLog;

    /**
     * Construct
     *
     * @param Helper $helper
     * @param Curl $curl
     * @param Validator $validator
     * @param ServiceLog $serviceLog
     * @param string[] $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(Helper $helper, Curl $curl, Validator $validator, ServiceLog $serviceLog, $data = [])
    {
        $this->helper = $helper;
        $this->curlClient = $curl;
        $this->validator = $validator;
        $this->serviceLog = $serviceLog;
    }

    /**
     * Get CURL client
     *
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * Get validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

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
                ->validateArgument('username', $this->helper->getApiUsername(), Argument::TYPE_STRING);
            $this->getValidator()
                ->validateArgument('password', $this->helper->getApiPassword(), Argument::TYPE_STRING);

            $stack = debug_backtrace();

            if (!empty($stack[1]['function'])) {
                $this->helper->debug(sprintf('Request: %s', $stack[1]['function']));
            }

            unset($stack);

            $this->helper->debug(sprintf('Request action: %s', $action));
            $this->helper->debug(sprintf('Request method: %s', $method));

            // Build headers
            $headers = [
                'Content-Type' => self::HEADER_CONTENT_TYPE,
                'Accept'       => self::HEADER_ACCEPT
            ];
            $response->setRequestHeaderContentType(self::HEADER_CONTENT_TYPE);
            $response->setRequestHeaderAccept(self::HEADER_ACCEPT);

            $this->helper->debug('Request headers:');
            $this->helper->debug($headers);

            // Set headers
            $this->getCurlClient()->setHeaders($headers);

            // Set credentials
            $this->getCurlClient()->setCredentials($this->helper->getApiUsername(), $this->helper->getApiPassword());

            // Set timeout
            $this->getCurlClient()->setTimeout($this->helper->getApiTimeout());

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
        return $this->helper->getServiceEndpoint() . $action . '/' . $urlSuffix;
    }

    /**
     * Build query
     *
     * @param string[] $params
     * @param string $queryPrefix
     * @param string $separator
     *
     * @return string
     */
    protected function buildQuery($params, $queryPrefix = '', $separator = '&')
    {
        return $queryPrefix . http_build_query($params, null, $separator);
    }
}