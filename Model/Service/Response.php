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

/**
 * Class Response
 * @package Fontera\Parcelninja\Model\Service
 */
class Response extends AbstractResponse
{
    /**#@+
     * Parcelninja headers
     */
    const HEADER_OUTBOUND_ID    = 'x-parcelninja-outbound-id';
    const HEADER_ELAPSED_TIME   = 'x-parcelninja-elapsed-ms';
    const HEADER_LOG_ID         = 'x-parcelninja-log-id';

    /**#@+
     * Constants defined for keys of array
     */
    const KEY_API_LOG_ID = 'api_log_id';
    const KEY_OUTBOUND_ID = 'outbound_id';

    /**
     * Get API log ID
     *
     * @return string
     */
    public function getApiLogId()
    {
        return $this->getData(self::KEY_API_LOG_ID);
    }

    /**
     * Get API log ID
     *
     * @return string
     */
    public function getOutboundId()
    {
        return $this->getData(self::KEY_OUTBOUND_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHeaders($headers)
    {
        parent::setResponseHeaders($headers);

        // Set these if they exist
        return $this->setApiLogId()
            ->setOutboundId();
    }

    /**
     * Set API log ID
     *
     * @return Response
     */
    protected function setApiLogId()
    {
        return $this->setData(self::KEY_API_LOG_ID, $this->getResponseHeaderItem(self::HEADER_LOG_ID));
    }

    /**
     * Set outbound ID
     *
     * @return Response
     */
    protected function setOutboundId()
    {
        return $this->setData(self::KEY_OUTBOUND_ID, $this->getResponseHeaderItem(self::HEADER_OUTBOUND_ID));
    }
}