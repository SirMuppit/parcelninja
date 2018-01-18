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
/*global define*/
define(
    [
        'jquery',
        'mageUtils',
        './shipping-rates-validation-rules',
        'mage/translate'
    ],
    function ($, utils, validationRules, $t) {
        "use strict";
        return {
            validationErrors: [],
            validate: function(address) {
                var self = this;
                this.validationErrors = [];
                $.each(validationRules.getRules(), function(field, rule) {
                    if (rule.required && utils.isEmpty(address[field])) {
                        var message = $t('Field ') + field + $t(' is required.');
                        self.validationErrors.push(message);
                    }
                });
                return !Boolean(this.validationErrors.length);
            }
        };
    }
);