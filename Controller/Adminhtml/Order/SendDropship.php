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

namespace Fontera\Parcelninja\Controller\Adminhtml\Order;

use Fontera\Parcelninja\Model\ResourceModel\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Fontera\Parcelninja\Helper\Data as Helper;
use Fontera\Parcelninja\Model\Process as ParcelninjaProcess;

/**
 * Class SendDropship
 * @package Fontera\Parcelninja\Controller\Adminhtml\Order
 */
class SendDropship extends Action
{
    /**
     * Parcelninja process
     *
     * @var ParcelninjaProcess
     */
    protected $parcelninjaProcess;

    /**
     * Page result factory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

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
     * @param JsonFactory $resultJsonFactory
     * @param ParcelninjaProcess $parcelninjaProcess
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ParcelninjaProcess $parcelninjaProcess,
        Helper $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->parcelninjaProcess = $parcelninjaProcess;
        $this->helper = $helper;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     * @todo change this
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fontera_Parcelninja::parcelninja_service_log');
    }

    /**
     * Execute
     *
     * Create and send shipment request
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $orderId = $this->_request->getParam('order_id');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);

        try {
            if (!$order->getId()) {
                throw new \Exception('Order is invalid.');
            }

            $dropshipPendingOrder = $this->parcelninjaProcess->prepareOrderToDropship($order);

            if (!empty($dropshipPendingOrder['id'])) {
                $status = 'success';
                $message = __(
                    'The order was sent to Dropship successfully. Pending Order ID: %1.',
                    $dropshipPendingOrder['id']
                );

                $order->setData(Config::KEY_ORDER_DROPSHIP_ORDER_ID, $dropshipPendingOrder['id']);

                $this->getMessageManager()->addSuccessMessage($message);
            } else {
                $status = 'error';
                $message = __('The order could not be sent to Dropship.');
                $this->getMessageManager()->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->helper->handleException($e);

            $status = 'error';
            $message = __('The order could not be sent to Dropship. Error: %1', $e->getMessage());
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }

        try {
            $order->addStatusHistoryComment($message);
            $order->getResource()->save($order);
        } catch (\Exception $e) {
            $this->helper->handleException($e);

            $status = 'error';
            $message = __('The order could not be sent to Dropship. Error: %1', $e->getMessage());
        }

        $result = ['status' => $status, 'message' => $message];

        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $resultJson->setData($result);
    }
}