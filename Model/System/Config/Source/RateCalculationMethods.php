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

namespace Fontera\Parcelninja\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class RateCalculationMethods
 * @package Fontera\Parcelninja\Model\System\Config\Source
 */
class RateCalculationMethods implements ArrayInterface
{
    const METHOD_DEFAULT = 1;
    const METHOD_FLATRATE = 2;
    const METHOD_VOLUMETRIC_RATE = 3;

    /**
     * Options getter
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::METHOD_FLATRATE, 'label' => __('Default (Not Implemented)')],
            ['value' => self::METHOD_FLATRATE, 'label' => __('Flatrate')],
            ['value' => self::METHOD_VOLUMETRIC_RATE, 'label' => __('Volumetric Rate')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return string[]
     */
    public function toArray()
    {
        return [
            self::METHOD_FLATRATE            => __('Default (Not Implemented)'),
            self::METHOD_FLATRATE           => __('Flatrate'),
            self::METHOD_VOLUMETRIC_RATE    => __('Volumetric Rate')
        ];
    }
}
