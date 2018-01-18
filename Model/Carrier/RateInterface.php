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
 * Interface RateInterface
 * @package Fontera\Parcelninja\Model\Carrier
 */
interface RateInterface
{
    /**
     * Get title
     *
     * @return null|string
     */
    public function getTitle();

    /**
     * Sets title
     *
     * @param string $title
     * @return Rate
     */
    public function setTitle($title = '');

    /**
     * Get fee
     *
     * @return null|string
     */
    public function getFee();

    /**
     * Sets fee
     *
     * @param float $fee
     * @return Rate
     */
    public function setFee($fee = 0.00);
}