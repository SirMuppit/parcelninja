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

namespace Fontera\Parcelninja\Api\Data;

/**
 * Interface ResponseInterface
 * @package Fontera\Parcelninja\Api\Data
 */
interface ResponseInterface
{
    /**#@+
     * Constants defined for keys of array
     */
    const KEY_REQUEST_METHOD = 'request_method';
    const KEY_REQUEST_ACTION = 'request_action';
    const KEY_REQUEST_HEADER_CONTENT_TYPE = 'request_header_content_type';
    const KEY_REQUEST_HEADER_ACCEPT = 'request_header_accept';
    const KEY_REQUEST_BODY = 'request_body';
    const KEY_RESPONSE_HEADERS = 'response_headers';
    const KEY_RESPONSE_HTTP_CODE = 'response_http_code';
    const KEY_RESPONSE_BODY = 'response_body';
    const KEY_PARSED_RESPONSE_BODY = 'parsed_response_body';
    const KEY_INTERNAL_ERROR = 'internal_error';

    /**
     * Is error
     *
     * @param int $code
     * @return bool
     */
    public function isError($code = 0);

    /**
     * Is success
     *
     * @param int $code
     * @return bool
     */
    public function isSuccess($code = 0);

    /**
     * Set request method
     *
     * @param string $requestMethod
     * @return ResponseInterface
     */
    public function setRequestMethod($requestMethod);

    /**
     * Get request method
     *
     * @return string
     */
    public function getRequestMethod();

    /**
     * Set request action
     *
     * @param string $requestAction
     * @return ResponseInterface
     */
    public function setRequestAction($requestAction);

    /**
     * Get request action
     *
     * @return string
     */
    public function getRequestAction();

    /**
     * Set request header content type
     *
     * @param string $contentType
     * @return ResponseInterface
     */
    public function setRequestHeaderContentType($contentType);

    /**
     * Get request header content type
     *
     * @return string
     */
    public function getRequestHeaderContentType();

    /**
     * Set request header accept
     *
     * @param string $accept
     * @return ResponseInterface
     */
    public function setRequestHeaderAccept($accept);

    /**
     * Get request header accept
     *
     * @return string
     */
    public function getRequestHeaderAccept();

    /**
     * Set request body
     *
     * @param string $requestBody
     * @return ResponseInterface
     */
    public function setRequestBody($requestBody);

    /**
     * Get request body
     *
     * @return string
     */
    public function getRequestBody();

    /**
     * Set response headers
     *
     * @param string[] $headers
     * @return ResponseInterface
     */
    public function setResponseHeaders($headers);

    /**
     * Get response headers
     *
     * @return string[]
     */
    public function getResponseHeaders();

    /**
     * Set response HTTP code
     *
     * @param int|string $code
     * @return ResponseInterface
     */
    public function setResponseHttpCode($code);

    /**
     * Get response HTTP code
     *
     * @return string
     */
    public function getResponseHttpCode();

    /**
     * Set response body
     *
     * @param string|string[] $body
     * @return ResponseInterface
     */
    public function setResponseBody($body);

    /**
     * Get response body
     *
     * @return int|string|string[]
     */
    public function getResponseBody();

    /**
     * Set internal error
     *
     * @param string $error
     * @return ResponseInterface
     */
    public function setInternalError($error);

    /**
     * Get internal error
     *
     * @return string
     */
    public function getInternalError();

    /**
     * Get parsed response
     *
     * @return int|string|string[] $responseBody
     */
    public function getParsedResponse();
}