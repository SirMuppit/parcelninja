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

use Magento\Framework\Model\AbstractModel;
use Fontera\Parcelninja\Api\Data\LogInterface;

/**
 * Class Log
 * @package Fontera\Parcelninja\Model\Service
 */
class Log extends AbstractModel implements LogInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'parcelninja_service_log';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Fontera\Parcelninja\Model\ResourceModel\Service\Log');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodName()
    {
        return $this->getData(self::KEY_METHOD_NAME);
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
    public function getRequestType()
    {
        return $this->getData(self::KEY_REQUEST_TYPE);
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
    public function getRequestHeaderAccept()
    {
        return $this->getData(self::KEY_REQUEST_HEADER_ACCEPT);
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
    public function getResponseHeader()
    {
        return $this->getData(self::KEY_RESPONSE_HEADER);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaderHttpCode()
    {
        return $this->getData(self::KEY_RESPONSE_HEADER_HTTP_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaderHttpMessage()
    {
        return $this->getData(self::KEY_RESPONSE_HEADER_HTTP_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultState()
    {
        return $this->getData(self::KEY_RESULT_STATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultMessage()
    {
        return $this->getData(self::KEY_RESULT_MESSAGE);
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
    public function getApiLogId()
    {
        return $this->getData(self::KEY_API_LOG_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->getData(self::KEY_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($logId)
    {
        return $this->setData(self::KEY_ID, $logId);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodName($methodName)
    {
        return $this->setData(self::KEY_METHOD_NAME, $methodName);
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
    public function setRequestType($requestType)
    {
        return $this->setData(self::KEY_REQUEST_TYPE, $requestType);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaderContentType($requestHeaderContentType)
    {
        return $this->setData(self::KEY_REQUEST_HEADER_CONTENT_TYPE, $requestHeaderContentType);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaderAccept($requestHeaderAccept)
    {
        return $this->setData(self::KEY_REQUEST_HEADER_ACCEPT, $requestHeaderAccept);
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
    public function setResponseHeader($responseHeader)
    {
        if (is_array($responseHeader)) {
            $responseHeader = @json_encode($responseHeader);
        }

        return $this->setData(self::KEY_RESPONSE_HEADER, $responseHeader);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHeaderHttpCode($responseHeaderHttpCode)
    {
        return $this->setData(self::KEY_RESPONSE_HEADER_HTTP_CODE, $responseHeaderHttpCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHeaderHttpMessage($responseHeaderHttpMessage)
    {
        return $this->setData(self::KEY_RESPONSE_HEADER_HTTP_MESSAGE, $responseHeaderHttpMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function setResultState($resultState)
    {
        return $this->setData(self::KEY_RESULT_STATE, $resultState);
    }

    /**
     * {@inheritdoc}
     */
    public function setResultMessage($resultMessage)
    {
        return $this->setData(self::KEY_RESULT_MESSAGE, $resultMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function setInternalError($internalError)
    {
        return $this->setData(self::KEY_INTERNAL_ERROR, $internalError);
    }

    /**
     * {@inheritdoc}
     */
    public function setApiLogId($apiLogId)
    {
        return $this->setData(self::KEY_API_LOG_ID, $apiLogId);
    }

    /**
     * {@inheritdoc}
     */
    public function setDate($date)
    {
        return $this->setData(self::KEY_DATE, $date);
    }

    /**
     * Set date
     *
     * @return bool
     */
    public function hasDate()
    {
        if (!empty($this->getDate())) {
            return true;
        }

        return false;
    }
}