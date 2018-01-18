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
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        parcelninjaShippingRatesValidator,
        parcelninjaShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator(
            'parcelninja',
            parcelninjaShippingRatesValidator
        );
        defaultShippingRatesValidationRules.registerRules(
            'parcelninja',
            parcelninjaShippingRatesValidationRules
        );
        return Component;
    }
);
