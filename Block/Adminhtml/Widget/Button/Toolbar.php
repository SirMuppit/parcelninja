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

namespace Fontera\Parcelninja\Block\Adminhtml\Widget\Button;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Fontera\Parcelninja\Model\ResourceModel\Config;
use Magento\Shipping\Block\Adminhtml\View as ShippingView;
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

/**
 * Class Toolbar
 * @package Fontera\Parcelninja\Block\Adminhtml\Widget\Button
 */
class Toolbar
{
    /**
     * Before push buttons
     *
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return string[]
     */
    public function beforePushButtons(ToolbarContext $toolbar, AbstractBlock $context, ButtonList $buttonList)
    {
        if ($context instanceof ShippingView
            && $context->getShipment()
            && $context->getShipment()->getData(Config::KEY_SHIPPING_OUTBOUND_ID) == NULL
        ) {
            // Remove "Send Tracking Information" button
            $buttonList->remove('save');

            $url = $context->getUrl(
                'parcelninja/shipping/sendOutbound',
                ['shipping_id' => $context->getShipment()->getId()]
            );

            $onclickJs = 'jQuery(\'#shipment_parcelninja\').parcelninjaSendOutbound({url: \'' . $url
                . '\'}).parcelninjaSendOutbound(\'submit\');';

            // Add "Submit Outbound To Parcelninja" button as primary
            $buttonList->add('shipment_parcelninja',
                [
                    'label'     => __('Submit Outbound To Parcelninja'),
                    'onclick'   => $onclickJs,
                    'class'     => 'shipment_parcelninja primary',
                    'data_attribute' => [
                        'mage-init' => '{"parcelninjaSendOutbound":{}}',
                    ]
                ]
            );

            // Update sort order of button to far right for consistency
            $buttonList->update('shipment_parcelninja', 'sort_order', (count($buttonList->getItems()) + 1) * 10);
        }

        if ($context instanceof OrderView
            && $context->getOrder()
            && $context->getOrder()->getData(Config::KEY_ORDER_DROPSHIP_ORDER_ID) == NULL
            && $context->getOrder()->hasShipments()
        ) {
            $url = $context->getUrl(
                'parcelninja/order/sendDropship',
                ['order_id' => $context->getOrder()->getId()]
            );

            $onclickJs = 'jQuery(\'#dropship_order_parcelninja\').parcelninjaSendDropship({url: \'' . $url
                . '\'}).parcelninjaSendDropship(\'submit\');';

            // Add "Submit Order To Dropship" button as primary
            $buttonList->add('dropship_order_parcelninja',
                [
                    'label'     => __('Submit Order To Dropship'),
                    'onclick'   => $onclickJs,
                    'class'     => 'dropship_order_parcelninja primary',
                    'data_attribute' => [
                        'mage-init' => '{"parcelninjaSendDropship":{}}',
                    ]
                ]
            );

            // Update sort order of button to far right for consistency
            $buttonList->update('dropship_order_parcelninja', 'sort_order', (count($buttonList->getItems()) + 1) * 10);
        }

        return [$context, $buttonList];
    }
}