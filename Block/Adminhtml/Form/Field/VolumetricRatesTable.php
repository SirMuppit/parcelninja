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

namespace Fontera\Parcelninja\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class VolumetricRatesTable
 * @package Fontera\Parcelninja\Block\Adminhtml\Form\Field
 */
class VolumetricRatesTable extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('name', ['label' => __('Category')]);
        $this->addColumn('weight_from', ['label' => __('From (Kg)')]);
        $this->addColumn('weight_to', ['label' => __('To (Kg)')]);
        $this->addColumn('delivery_fee', ['label' => __('Delivery Fee')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Volumetric Rate');
    }
}
