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

namespace Fontera\Parcelninja\Model\ResourceModel;

/**
 * Class Config
 * @package Fontera\Parcelninja\Model\ResourceModel
 */
class Config
{
    /**#@+
     * Keys
     */
    const KEY_SHIPPING_OUTBOUND_ID = 'parcelninja_outbound_id';
    const KEY_SHIPPING_GRID_OUTBOUND_SENT = 'parcelninja_outbound_sent';
    const KEY_ORDER_DROPSHIP_ORDER_ID = 'parcelninja_dropship_order_id';
    const KEY_ORDER_GRID_DROPSHIP_SENT = 'parcelninja_dropship_sent';
}