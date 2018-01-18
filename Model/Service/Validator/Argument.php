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
 * Class Argument
 * @package Fontera\Parcelninja\Model\Service\Validator
 */
class Argument
{
    const TYPE_STRING = 'string';
    const TYPE_BOOL = 'bool';
    const TYPE_ARRAY = 'array';
    const TYPE_INT = 'int';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_OBJECT = 'object';

    /**
     * Argument name
     *
     * @var string
     */
    protected $argName;

    /**
     * Argument value
     *
     * @var string|string[]
     */
    protected $argValue;

    /**
     * Argument type
     *
     * @var string
     */
    protected $argType;

    /**
     * Argument default value
     *
     * @var string|string[]
     */
    protected $argDefaultValue;

    /**
     * Construct
     *
     * @param string $argName
     * @param string|string[] $argValue
     * @param string $argType
     * @param string|string[] $argDefaultValue
     */
    public function __construct($argName, $argValue, $argType, $argDefaultValue)
    {
        $this->argName = $argName;
        $this->argValue = $argValue;
        $this->argType = $argType;
        $this->argDefaultValue = $argDefaultValue;
    }

    /**
     * Validate required
     *
     * @throws LocalizedException
     * @return Argument
     */
    public function required()
    {
        $error = false;

        if (gettype($this->argValue) == self::TYPE_ARRAY) {
            if (empty($this->argValue)) {
                $error = true;
            }
        } else if (!$this->argValue || $this->argValue == '') {
            $error = true;
        }

        if ($error) {
            throw new LocalizedException(__('Argument "%1" is required.', $this->argName));
        }

        return $this;
    }

    /**
     * Validate type
     *
     * @throws LocalizedException
     * @return Argument
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function type()
    {
        $errorMessage = sprintf(
            'Argument type "%s" is invalid and should be of "%s" type.',
            $this->argName,
            $this->argType
        );

        if ($this->argType == self::TYPE_STRING) {
            if (!is_string($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else if ($this->argType == self::TYPE_INT) {
            if (!is_int($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else if ($this->argType == self::TYPE_ARRAY) {
            if (!is_array($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else if ($this->argType == self::TYPE_NUMERIC) {
            if (!is_numeric($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else if ($this->argType == self::TYPE_BOOL) {
            if (!is_bool($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else if ($this->argType == self::TYPE_OBJECT) {
            if (!is_object($this->argValue)) {
                throw new LocalizedException(__($errorMessage));
            }
        } else {
            throw new LocalizedException(__('Unknown type for argument "%1".', $this->argName));
        }

        return $this;
    }

    /**
     * Validate type
     *
     * @param string[] $allowedArray
     * @param string $errorMessage
     *
     * @throws LocalizedException
     * @return Argument
     */
    public function allowed($allowedArray, $errorMessage = '')
    {
        if (!in_array($this->argValue, $allowedArray)) {

            $suffixError = sprintf(' Use one of %s.', @implode(', ', $allowedArray));

            if ($errorMessage == '') {
                $errorMessage = sprintf('"%s" is not allowed.%s', $this->argName, $suffixError);
            } else {
                $errorMessage = $errorMessage . $suffixError;
            }

            throw new LocalizedException(__($errorMessage));
        }

        return $this;
    }

    /**
     * Compare
     *
     * @param string $operator
     * @param string $compareValue
     * @param string $errorSuffix
     *
     * @throws LocalizedException
     * @return Argument
     */
    public function compareStrLen($operator = '==', $compareValue = '', $errorSuffix = '')
    {
        $value = strlen($this->argValue);

        $knownOps = [
            '=='    => function ($first, $second) {
                return $first == $second;
            },
            '==='   => function ($first, $second) {
                return $first === $second;
            },
            '!='    => function ($first, $second) {
                return $first != $second;
            },
            '!=='   => function ($first, $second) {
                return $first !== $second;
            },
            '>'     => function ($first, $second) {
                return $first > $second;
            },
            '>='    => function ($first, $second) {
                return $first >= $second;
            },
            '<'     => function ($first, $second) {
                return $first < $second;
            },
            '<='    => function ($first, $second) {
                return $first <= $second;
            }
        ];

        $errorOps = [
            '=='    => sprintf('The value must not contain "%s" characters.', $compareValue),
            '==='   => sprintf('The value must not contain "%s" characters.', $compareValue),
            '!='    => sprintf('The value must contain "%s" characters.', $compareValue),
            '!=='   => sprintf('The value must contain "%s" characters.', $compareValue),
            '>'     => sprintf('The value must contain "%s" characters or less.', $compareValue),
            '>='    => sprintf('The value must contain less than "%s" characters.', $compareValue),
            '<'     => sprintf('The value must contain "%s" characters or more.', $compareValue),
            '<='    => sprintf('The value must contain more than "%s" characters.', $compareValue)
        ];

        if (isset($knownOps[$operator])) {
            if ($knownOps[$operator]($value, $compareValue)) {

                $errorMessage = sprintf('Argument "%s" is invalid.', $this->argName);

                if (isset($errorOps[$operator])) {
                    $errorMessage .= ' ' . $errorOps[$operator];
                }

                if ($errorSuffix !== '') {
                    $errorMessage .= ' ' . $errorSuffix;
                }

                throw new LocalizedException(__($errorMessage));
            }
        }

        return $this;
    }

    /**
     * Get value or default value
     *
     * @return string|string[]
     */
    public function getValue()
    {
        // @todo clean value

        if ($this->argValue) {
            return $this->argValue;
        } else {

            if ($this->argDefaultValue === false) {

                return self::getTypeValue($this->argType);
            } else {
                return $this->argDefaultValue;
            }
        }
    }

    /**
     * Get type value
     *
     * @param string $type
     *
     * @return bool|int|null|string|string[]
     */
    static public function getTypeValue($type)
    {
        if ($type == self::TYPE_STRING) {
            return '';
        } else if ($type == self::TYPE_INT || $type == self::TYPE_NUMERIC) {
            return 0;
        } else if ($type == self::TYPE_ARRAY) {
            return [];
        } else if ($type == self::TYPE_BOOL) {
            return false;
        } else {
            return null;
        }
    }
}