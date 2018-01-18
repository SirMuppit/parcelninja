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

namespace Fontera\Parcelninja\Controller\Adminhtml\Service\Log;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Fontera\Parcelninja\Model\ResourceModel\Service\Log\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Fontera\Parcelninja\Controller\Adminhtml\Service\Log
 */
class MassDelete extends Action
{
    /**
     * Filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        /** @var \Fontera\Parcelninja\Model\Service\Log $item */
        foreach ($collection as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}