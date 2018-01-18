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

/**
 * Interface QuotesCacheInterface
 * @package Fontera\Parcelninja\Model\Carrier
 */
interface QuotesCacheInterface
{
    /**
     * Get cached quotes
     *
     * Used to reduce number of same requests done to carrier service during one session
     *
     * @param string $str
     * @return null|string
     */
    public function getCachedQuotes($str);

    /**
     * Sets cached quotes
     *
     * @param string $str
     * @param string $cache
     * @return QuotesCache
     */
    public function setCachedQuotes($str, $cache);
}