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

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Fontera\Parcelninja\Helper\Data as Helper;

/**
 * Class HeadNotice
 * @package Fontera\Parcelninja\Block\Adminhtml\System\Config
 */
class HeadNotice extends Template implements RendererInterface
{
    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Fontera_Parcelninja::system/config/head-notice.phtml';

    /**
     * Helper
     *
     * @var Helper
     */
    protected $helper;

    /**
     * Construct
     *
     * @param Context $context
     * @param Helper $helper
     * @param [] $data
     */
    public function __construct(Context $context, Helper $helper, $data = [])
    {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $elementOriginalData = $element->getData('original_data');

        if (isset($elementOriginalData['help_url'])) {
            $this->setData('help_url', $elementOriginalData['help_url']);
        }

        return $this->toHtml();
    }
}