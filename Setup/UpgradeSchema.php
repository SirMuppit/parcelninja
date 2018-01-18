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

namespace Fontera\Parcelninja\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Fontera\Parcelninja\Model\ResourceModel\Config;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addColumnOutboundIdToShipment($installer);
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addColumnOutboundSentToShipmentGrid($installer);
        }

        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->addColumnDropshipOrderIdToOrder($installer);
            $this->addColumnDropshipSentToOrderGrid($installer);
        }

        $installer->endSetup();
    }

    /**
     * Add column "parcelninja_outbound_id" to "sales_shipment"
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnOutboundIdToShipment(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('sales_shipment'),
            Config::KEY_SHIPPING_OUTBOUND_ID,
            [
                'type'      => Table::TYPE_TEXT,
                'nullable'  => true,
                'length'    => 255,
                'comment'   => 'Parcelninja Outbound ID'
            ]
        );
    }

    /**
     * Add column "parcelninja_outbound_sent" to "sales_shipment_grid"
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnOutboundSentToShipmentGrid(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('sales_shipment_grid'),
            Config::KEY_SHIPPING_GRID_OUTBOUND_SENT,
            [
                'type'      => Table::TYPE_TEXT,
                'nullable'  => true,
                'length'    => 255,
                'comment'   => 'Parcelninja Outbound Sent'
            ]
        );
    }

    /**
     * Add column "parcelninja_dropship_order_id" to "sales_order"
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnDropshipOrderIdToOrder(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('sales_order'),
            Config::KEY_ORDER_DROPSHIP_ORDER_ID,
            [
                'type'      => Table::TYPE_TEXT,
                'nullable'  => true,
                'length'    => 255,
                'comment'   => 'Parcelninja Dropship Order ID'
            ]
        );
    }

    /**
     * Add column "parcelninja_dropship_sent" to "sales_order_grid"
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnDropshipSentToOrderGrid(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $connection->addColumn(
            $installer->getTable('sales_order_grid'),
            Config::KEY_ORDER_GRID_DROPSHIP_SENT,
            [
                'type'      => Table::TYPE_TEXT,
                'nullable'  => true,
                'length'    => 255,
                'comment'   => 'Parcelninja Dropship Sent'
            ]
        );
    }
}