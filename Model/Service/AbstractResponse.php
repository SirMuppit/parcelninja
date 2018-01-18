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

use Magento\Framework\DataObject;
use Fontera\Parcelninja\Api\Data\ResponseInterface;

/**
 * Class AbstractResponse
 * @package Fontera\Parcelninja\Model\Service
 */
abstract class AbstractResponse extends DataObject implements ResponseInterface
{
    /**#@+
     * Info 1xx
     */
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;

    /**#@+
     * Success 2xx
     */
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;

    /**#@+
     * Redirect 3xx
     */
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_UNUSED= 306;
    const HTTP_TEMPORARY_REDIRECT = 307;

    /**#@+
     * Client error 4xx
     */
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED  = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;

    /**#@+
     * Server error 5xx
     */
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct([
            self::KEY_REQUEST_METHOD                => '',
            self::KEY_REQUEST_ACTION                => '',
            self::KEY_REQUEST_HEADER_CONTENT_TYPE   => '',
            self::KEY_REQUEST_HEADER_ACCEPT         => '',
            self::KEY_REQUEST_BODY                  => '',
            self::KEY_RESPONSE_HEADERS              => '',
            self::KEY_RESPONSE_HTTP_CODE            => '',
            self::KEY_RESPONSE_BODY                 => '',
            self::KEY_PARSED_RESPONSE_BODY          => '',
            self::KEY_INTERNAL_ERROR                => ''
        ]);
    }

    /**
     * Get message for code
     *
     * @param int $code
     * @return string
     */
    public function getMessageForCode($code)
    {
        return $this->getHttpMessages()->getData($code);
    }

    /**
     * {@inheritdoc}
     */
    public function isError($code = 0)
    {
        if ($code == 0) {
            $code = $this->getResponseHttpCode();
        }

        return is_numeric($code) && $code >= self::HTTP_BAD_REQUEST;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccess($code = 0)
    {
        if ($code == 0) {
            $code = $this->getResponseHttpCode();
        }

        return is_numeric($code) && $code >= self::HTTP_OK && $code <= self::HTTP_PARTIAL_CONTENT;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestMethod($requestMethod)
    {
        return $this->setData(self::KEY_REQUEST_METHOD, $requestMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestMethod()
    {
        return $this->getData(self::KEY_REQUEST_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestAction($requestAction)
    {
        return $this->setData(self::KEY_REQUEST_ACTION, $requestAction);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestAction()
    {
        return $this->getData(self::KEY_REQUEST_ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaderContentType($contentType)
    {
        return $this->setData(self::KEY_REQUEST_HEADER_CONTENT_TYPE, $contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestHeaderContentType()
    {
        return $this->getData(self::KEY_REQUEST_HEADER_CONTENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaderAccept($accept)
    {
        return $this->setData(self::KEY_REQUEST_HEADER_ACCEPT, $accept);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestHeaderAccept()
    {
        return $this->getData(self::KEY_REQUEST_HEADER_ACCEPT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestBody($requestBody)
    {
        return $this->setData(self::KEY_REQUEST_BODY, $requestBody);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestBody()
    {
        return $this->getData(self::KEY_REQUEST_BODY);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHeaders($headers)
    {
        return $this->setData(self::KEY_RESPONSE_HEADERS, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaders()
    {
        return $this->getData(self::KEY_RESPONSE_HEADERS);
    }

    /**
     * Get response header item
     *
     * @param string $str
     * @return string
     */
    public function getResponseHeaderItem($str)
    {
        $headers = $this->getResponseHeaders();

        if (is_array($headers) && isset($headers[$str])) {
            return $headers[$str];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHttpCode($code)
    {
        return $this->setData(self::KEY_RESPONSE_HTTP_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHttpCode()
    {
        return $this->getData(self::KEY_RESPONSE_HTTP_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseBody($body)
    {
        return $this->setData(self::KEY_RESPONSE_BODY, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseBody()
    {
        return $this->getData(self::KEY_RESPONSE_BODY);
    }

    /**
     * {@inheritdoc}
     */
    public function setInternalError($error)
    {
        return $this->setData(self::KEY_INTERNAL_ERROR, $error);
    }

    /**
     * {@inheritdoc}
     */
    public function getInternalError()
    {
        return $this->getData(self::KEY_INTERNAL_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedResponse()
    {
        if (!$this->getData(self::KEY_PARSED_RESPONSE_BODY)) {

            $headers = $this->getResponseHeaders();
            $body = $this->getResponseBody();

            if (is_array($headers) && !empty($headers['Content-Type']) && $body !== '') {

                // Parse JSON
                if (preg_match('/\bjson\b/i', $headers['Content-Type'])) {
                    $body = @json_decode($body, true);
                }
            }

            $this->setData(self::KEY_PARSED_RESPONSE_BODY, $body);
        }

        return $this->getData(self::KEY_PARSED_RESPONSE_BODY);
    }

    /**
     * Get http messages
     *
     * @return DataObject
     */
    protected function getHttpMessages()
    {
        return new DataObject([
            100 => '100 Continue',
            101 => '101 Switching Protocols',
            200 => '200 OK',
            201 => '201 Created',
            202 => '202 Accepted',
            203 => '203 Non-Authoritative Information',
            204 => '204 No Content',
            205 => '205 Reset Content',
            206 => '206 Partial Content',
            300 => '300 Multiple Choices',
            301 => '301 Moved Permanently',
            302 => '302 Found',
            303 => '303 See Other',
            304 => '304 Not Modified',
            305 => '305 Use Proxy',
            306 => '306 (Unused)',
            307 => '307 Temporary Redirect',
            400 => '400 Bad Request',
            401 => '401 Unauthorized',
            402 => '402 Payment Required',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            405 => '405 Method Not Allowed',
            406 => '406 Not Acceptable',
            407 => '407 Proxy Authentication Required',
            408 => '408 Request Timeout',
            409 => '409 Conflict',
            410 => '410 Gone',
            411 => '411 Length Required',
            412 => '412 Precondition Failed',
            413 => '413 Request Entity Too Large',
            414 => '414 Request-URI Too Long',
            415 => '415 Unsupported Media Type',
            416 => '416 Requested Range Not Satisfiable',
            417 => '417 Expectation Failed',
            500 => '500 Internal Server Error',
            501 => '501 Not Implemented',
            502 => '502 Bad Gateway',
            503 => '503 Service Unavailable',
            504 => '504 Gateway Timeout',
            505 => '505 HTTP Version Not Supported'
        ]);
    }
}