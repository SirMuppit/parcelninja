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
class Rate extends DataObject implements RateInterface
{
    /**#@+
     * Keys
     */
    const KEY_TITLE = 'title';
    const KEY_FEE = 'fee';

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title = '')
    {
        $this->setData(self::KEY_TITLE, $title);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFee()
    {
        return $this->getData(self::KEY_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFee($fee = 0.00)
    {
        $this->setData(self::KEY_FEE, $fee);
        return $this;
    }
}