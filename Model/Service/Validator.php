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

use Fontera\Parcelninja\Model\Service\Validator\Argument;
use Fontera\Parcelninja\Model\Service\Validator\ArrayItem;

/**
 * Class Validator
 * @package Fontera\Parcelninja\Model\Service
 */
class Validator
{
    /**
     * Validate Argument
     *
     * @param string $argName
     * @param string|string[] $argValue
     * @param string $argType
     * @param bool $type
     * @param bool $required
     * @param bool|string|string[] $argDefaultValue
     *
     * @return Argument
     */
    public function validateArgument(
        $argName, $argValue, $argType, $type = true, $required = true, $argDefaultValue = false
    ) {
        $arg = new Argument($argName, $argValue, $argType, $argDefaultValue);

        if ($type) {
            $arg->type();
        }

        if ($required) {
            $arg->required();
        }

        return $arg;
    }

    /**
     * Validate array item
     *
     * @param string[] $arr
     * @param string $arrIndex
     * @param string $argType
     * @param bool $type
     * @param bool $required
     * @param bool|string|string[] $argDefaultValue
     *
     * @return Argument
     */
    public function validateArrayItem(
        $arr, $arrIndex, $argType, $type = true, $required = true, $argDefaultValue = false
    ) {
        $arg = new ArrayItem($arr, $arrIndex, $argType, $argDefaultValue);

        if ($type) {
            $arg->type();
        }

        if ($required) {
            $arg->required();
        }

        return $arg;
    }
}