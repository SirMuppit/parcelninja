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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('fontera_parcelninja_service_log'))
            ->addColumn(
                'id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn('method_name', Table::TYPE_TEXT, 255, [], 'Method Name')
            ->addColumn('request_action', Table::TYPE_TEXT, 255, [], 'Request Action')
            ->addColumn('request_type', Table::TYPE_TEXT, 10, [], 'Request Type')
            ->addColumn('request_header_content_type', Table::TYPE_TEXT, 50, [], 'Request Header Content Type')
            ->addColumn('request_header_accept', Table::TYPE_TEXT, 50, [], 'Request Header Accept')
            ->addColumn('request_body', Table::TYPE_TEXT, '64K', [], 'Request Body')
            ->addColumn('response_header', Table::TYPE_TEXT, '64K', [], 'Response Header')
            ->addColumn('response_header_http_code', Table::TYPE_TEXT, 10, [], 'Response Header HTTP Code')
            ->addColumn('response_header_http_message', Table::TYPE_TEXT, 20, [], 'Response Header HTTP Message')
            ->addColumn('result_state', Table::TYPE_TEXT, 20, [], 'Result State')
            ->addColumn('result_message', Table::TYPE_TEXT, '64K', [], 'Result Message')
            ->addColumn('internal_error', Table::TYPE_TEXT, '64K', [], 'Internal Error')
            ->addColumn('api_log_id', Table::TYPE_TEXT, 255, [], 'API Log ID')
            ->addColumn('date', Table::TYPE_DATETIME, null, [], 'Date')
            ->addIndex(
                $setup->getIdxName(
                    $installer->getTable('fontera_parcelninja_service_log'),
                    [
                        'method_name',
                        'request_action',
                        'request_type',
                        'request_header_content_type',
                        'request_header_accept',
                        'request_body',
                        'response_header',
                        'response_header_http_code',
                        'response_header_http_message',
                        'result_state',
                        'result_message',
                        'internal_error',
                        'api_log_id',
                    ],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'method_name',
                    'request_action',
                    'request_type',
                    'request_header_content_type',
                    'request_header_accept',
                    'request_body',
                    'response_header',
                    'response_header_http_code',
                    'response_header_http_message',
                    'result_state',
                    'result_message',
                    'internal_error',
                    'api_log_id',
                ],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment('Fontera Parcelninja Service Log');

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}