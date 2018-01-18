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
 * Interface LogInterface
 * @package Fontera\Parcelninja\Api\Data
 */
interface LogInterface
{
    /**
     * Constants defined for keys of array
     */
    const KEY_ID = 'id';
    const KEY_METHOD_NAME = 'method_name';
    const KEY_REQUEST_ACTION = 'request_action';
    const KEY_REQUEST_TYPE = 'request_type';
    const KEY_REQUEST_HEADER_CONTENT_TYPE = 'request_header_content_type';
    const KEY_REQUEST_HEADER_ACCEPT = 'request_header_accept';
    const KEY_REQUEST_BODY = 'request_body';
    const KEY_RESPONSE_HEADER = 'response_header';
    const KEY_RESPONSE_HEADER_HTTP_CODE = 'response_header_http_code';
    const KEY_RESPONSE_HEADER_HTTP_MESSAGE = 'response_header_http_message';
    const KEY_RESULT_STATE = 'result_state';
    const KEY_RESULT_MESSAGE = 'result_message';
    const KEY_INTERNAL_ERROR = 'internal_error';
    const KEY_API_LOG_ID = 'api_log_id';
    const KEY_DATE = 'date';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get method name
     *
     * @return string
     */
    public function getMethodName();

    /**
     * Get request action
     *
     * @return string
     */
    public function getRequestAction();

    /**
     * Get request type
     *
     * @return string
     */
    public function getRequestType();

    /**
     * Get request header content type
     *
     * @return string
     */
    public function getRequestHeaderContentType();

    /**
     * Get request header accept
     *
     * @return string
     */
    public function getRequestHeaderAccept();

    /**
     * Get request header accept
     *
     * @return string
     */
    public function getRequestBody();

    /**
     * Get request response header
     *
     * @return string
     */
    public function getResponseHeader();

    /**
     * Get request response header HTTP code
     *
     * @return string
     */
    public function getResponseHeaderHttpCode();

    /**
     * Get request response header HTTP message
     *
     * @return string
     */
    public function getResponseHeaderHttpMessage();

    /**
     * Get result state
     *
     * @return string
     */
    public function getResultState();

    /**
     * Get result message
     *
     * @return string
     */
    public function getResultMessage();

    /**
     * Get internal error
     *
     * @return string
     */
    public function getInternalError();

    /**
     * Get API log ID
     *
     * @return string
     */
    public function getApiLogId();

    /**
     * Get date
     *
     * @return string
     */
    public function getDate();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setId($id);

    /**
     * Set method name
     *
     * @param string $methodName
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setMethodName($methodName);

    /**
     * Set request action
     *
     * @param string $requestAction
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setRequestAction($requestAction);

    /**
     * Set request type
     *
     * @param string $requestType
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setRequestType($requestType);

    /**
     * Set request header content type
     *
     * @param string $requestHeaderContentType
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setRequestHeaderContentType($requestHeaderContentType);

    /**
     * Set request header accept
     *
     * @param string $requestHeaderAccept
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setRequestHeaderAccept($requestHeaderAccept);

    /**
     * Set request header accept
     *
     * @param string $requestBody
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setRequestBody($requestBody);

    /**
     * Set request response header
     *
     * @param string $responseHeader
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setResponseHeader($responseHeader);

    /**
     * Set request response header HTTP code
     *
     * @param string $responseHeaderHttpCode
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setResponseHeaderHttpCode($responseHeaderHttpCode);

    /**
     * Set request response header HTTP message
     *
     * @param string $responseHeaderHttpMessage
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setResponseHeaderHttpMessage($responseHeaderHttpMessage);

    /**
     * Set result state
     *
     * @param string $resultState
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setResultState($resultState);

    /**
     * Set result message
     *
     * @param string $resultMessage
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setResultMessage($resultMessage);

    /**
     * Set internal error
     *
     * @param string $internalError
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setInternalError($internalError);

    /**
     * Set API log ID
     *
     * @param string $apiLogId
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setApiLogId($apiLogId);

    /**
     * Set date
     *
     * @param string $date
     * @return \Fontera\Parcelninja\Api\Data\LogInterface
     */
    public function setDate($date);
}