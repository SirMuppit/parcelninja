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
define([
    "jquery",
    "jquery/ui",
    'Magento_Ui/js/modal/modal',
    "mage/translate"
], function($){
    "use strict";
    $.widget('mage.parcelninjaSendOutbound', {
        options: {
            url: null
        },

        /**
         * @protected
         */
        _create: function () {
            $('#shipment_parcelninja').loader({ texts: {loaderText: $.mage.__('Submitting to Parcelninja')} });
        },

        /**
         * Submit
         */
        submit: function() {
            $.ajax({
                url: this.options.url,
                type: 'get',
                dataType: 'json',
                showLoader: true,
                loaderContext: '#shipment_parcelninja'
            }).done(function(data){
                location.reload();
            });
        }
    });

    return $.mage.parcelninjaSendOutbound;
});
