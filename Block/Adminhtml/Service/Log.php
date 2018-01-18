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

namespace Fontera\Parcelninja\Block\Adminhtml\Service;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Log
 * @package Fontera\Parcelninja\Block\Adminhtml\Service
 */
class Log extends Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'parcelninja_service_log';
        $this->_blockGroup = 'Fontera_Parcelninja';
        $this->_headerText = __('Manage Pages');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}