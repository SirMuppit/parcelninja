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

namespace Fontera\Parcelninja\Model\Service\Validator;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class ArrayItem
 * @package Fontera\Parcelninja\Model\Service\Validator
 */
class ArrayItem extends Argument
{
    /**
     * Construct
     *
     * @param string[] $arr
     * @param string $arrIndex
     * @param string $argType
     * @param string|string[] $argDefaultValue
     *
     * @throws LocalizedException
     */
    public function __construct($arr, $arrIndex, $argType, $argDefaultValue)
    {
        if (!is_array($arr)) {
            throw new LocalizedException(__('Cannot validate array item. The array specified is invalid.'));
        }

        if (!isset($arr[$arrIndex])) {
            $arr[$arrIndex] = Argument::getTypeValue($argType);
        }

        parent::__construct($arrIndex, $arr[$arrIndex], $argType, $argDefaultValue);
    }
}