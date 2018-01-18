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

namespace Fontera\Parcelninja\Model\Carrier;

use Magento\Framework\DataObject;

/**
 * Class QuotesCache
 * @package Fontera\Parcelninja\Model\Carrier
 */
class QuotesCache extends DataObject implements QuotesCacheInterface
{
    /**
     * Get cached quotes
     *
     * Used to reduce number of same requests done to carrier service during one session
     *
     * @param string $str
     * @return null|string
     */
    public function getCachedQuotes($str)
    {
        $key = $this->getQuotesCacheKey($str);

        return $this->getData($key) ? $this->getData($key) : null;
    }

    /**
     * Sets cached quotes
     *
     * @param string $str
     * @param string $cache
     * @return QuotesCache
     */
    public function setCachedQuotes($str, $cache)
    {
        $key = $this->getQuotesCacheKey($str);

        if (is_array($cache)) {
            $cache = serialize($cache);
        }

        $this->setData($key, $cache);

        return $this;
    }

    /**
     * Get quotes cache key
     *
     * @param string $str
     * @return int
     */
    protected function getQuotesCacheKey($str)
    {
        return crc32($str);
    }
}