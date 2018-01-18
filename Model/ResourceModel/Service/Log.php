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

namespace Fontera\Parcelninja\Model\ResourceModel\Service;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Log
 * @package Fontera\Parcelninja\Model\ResourceModel\Service
 */
class Log extends AbstractDb
{
    /**
     * Date time
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(Context $context, DateTime $date, $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('fontera_parcelninja_service_log', 'id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Fontera\Parcelninja\Model\Service\Log $object*/
        if ($object->isObjectNew() && !$object->hasDate()) {
            $object->setDate($this->date->gmtDate());
        }

        $object->setDate($this->date->gmtDate());

        return parent::_beforeSave($object);
    }
}