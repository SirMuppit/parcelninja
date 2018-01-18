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

namespace Fontera\Parcelninja\Ui\Component\Listing\Column;

use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Fontera\Parcelninja\Model\ResourceModel\Config;

/**
 * Class OutboundSent
 * @package Fontera\Parcelninja\Ui\Component\Listing\Column
 */
class OutboundSent extends Column
{
    /**
     * Shipment repository
     *
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * Search criteria
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * Construct
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $criteria
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteria = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $shipment = $this->shipmentRepository->get($item['entity_id']);
                $outboundId = $shipment->getData(Config::KEY_SHIPPING_OUTBOUND_ID);

                if ($outboundId) {
                    $item[$this->getData('name')] = 'Yes';
                } else {
                    $item[$this->getData('name')] = 'No';
                }
            }
        }

        return $dataSource;
    }
}