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

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Fontera\Parcelninja\Model\ResourceModel\Config;

/**
 * Class DropshipSent
 * @package Fontera\Parcelninja\Ui\Component\Listing\Column
 */
class DropshipSent extends Column
{
    /**
     * Order repository
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

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
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
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

                $order = $this->orderRepository->get($item['entity_id']);
                $dropshipOrderId = $order->getData(Config::KEY_ORDER_DROPSHIP_ORDER_ID);

                if ($dropshipOrderId) {
                    $item[$this->getData('name')] = 'Yes';
                } else {
                    $item[$this->getData('name')] = 'No';
                }
            }
        }

        return $dataSource;
    }
}