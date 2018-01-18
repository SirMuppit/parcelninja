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

namespace Fontera\Parcelninja\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field as SystemConfigFormField;
use Fontera\Parcelninja\Helper\Data as ModuleHelper;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Version
 * @package Fontera\Parcelninja\Block\Adminhtml\System\Config
 */
class Version extends SystemConfigFormField
{
    /**
     * Helper
     *
     * @var ModuleHelper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ModuleHelper $helper
     * @param string[] $data
     */
    public function __construct(Context $context, ModuleHelper $helper, $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = "<i style='line-height:34px; color:#2C9A54;'>";
        $html .= $this->helper->getVersion();
        $html .= "</i>";

        return $html;
    }
}