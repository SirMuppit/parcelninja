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

namespace Fontera\Parcelninja\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Fontera\Parcelninja\Helper\Data as Helper;
use Fontera\Parcelninja\Model\Process as ParcelninjaProcess;

/**
 * Class SendOutbound
 * @package Fontera\Parcelninja\Controller\Adminhtml\Shipping
 */
class SendOutbound extends Action
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

        $shippingId = $this->_request->getParam('shipping_id');

        try {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shippingId);

            if (!$shipment->getId()) {
                throw new \Exception('Shipment is invalid.');
            }

            $requestResult = $this->parcelninjaProcess->shipmentRequest($shipment, true);

            if (isset($requestResult['status']) && isset($requestResult['message'])) {
                if ($requestResult['status'] == 'success') {
                    $status = $requestResult['status'];
                    $message = $requestResult['message'];
                    $this->getMessageManager()->addSuccessMessage($message);
                } else {
                    throw new \Exception($requestResult['message']);
                }
            } else {
                throw new \Exception('No response was returned or is response was empty.');
            }
        } catch (\Exception $e) {
            $this->helper->handleException($e);

            $status = 'error';
            $message = $e->getMessage();
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }

        $result = ['status' => $status, 'message' => $message];

        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
        return $resultJson->setData($result);
    }
}