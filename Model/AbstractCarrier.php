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

namespace Fontera\Parcelninja\Model;

use Magento\Shipping\Model\Carrier\AbstractCarrier as BaseAbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory as TrackStatusFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Fontera\Parcelninja\Model\Carrier\QuotesCache;
use Fontera\Parcelninja\Model\Carrier\Rate;
use Fontera\Parcelninja\Helper\Data as Helper;
use Fontera\Parcelninja\Model\Service\Response as ServiceResponse;
use Magento\Framework\DataObject as DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result; // RateFactory
use Fontera\Parcelninja\Model\System\Config\Source\RateCalculationMethods;

/**
 * Class AbstractCarrier
 * @package Fontera\Parcelninja\Model
 */
abstract class AbstractCarrier extends BaseAbstractCarrier implements CarrierInterface
{
    /**
     * Rate result model
     *
     * @var RateFactory
     */
    protected $rateFactory;

    /**
     * Quote address rate result method model
     *
     * @var RateMethodFactory
     */
    protected $rateMethodFactory;

    /**
     * Tracking result model
     *
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * Tracking result error model
     *
     * @var TrackErrorFactory
     */
    protected $trackErrorFactory;

    /**
     * Tracking result status model
     *
     * @var TrackStatusFactory
     */
    protected $trackStatusFactory;

    /**
     * Region model
     *
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * Country model
     *
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * Currency model
     *
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Directory data
     *
     * @var DirectoryHelper
     */
    protected $directoryData = null;

    /**
     * Stock registry API interface
     *
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Event manager interface
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $request = null;

    /**
     * Rate request parameters
     *
     * @var DataObject|null
     */
    protected $requestParams = null;

    /**
     * Rate result or tracking data
     *
     * @var Result|null
     */
    protected $result = null;

    /**
     * Quotes cache
     *
     * @var QuotesCache
     */
    protected $quotesCache;

    /**
     * Rate
     *
     * @var Rate
     */
    protected $rate;

    /**
     * Parcelninja helper
     *
     * @var Helper
     */
    protected $helper;

    /**
     * Construct
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param RateErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param RateFactory $rateFactory
     * @param RateMethodFactory $rateMethodFactory
     * @param TrackFactory $trackFactory
     * @param TrackErrorFactory $trackErrorFactory
     * @param TrackStatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param DirectoryHelper $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param EventManagerInterface $eventManager
     * @param Helper $helper
     * @param QuotesCache $quotesCache
     * @param Rate $rate
     * @param string[] $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        RateFactory $rateFactory,
        RateMethodFactory $rateMethodFactory,
        TrackFactory $trackFactory,
        TrackErrorFactory $trackErrorFactory,
        TrackStatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        DirectoryHelper $directoryData,
        StockRegistryInterface $stockRegistry,
        EventManagerInterface $eventManager,
        Helper $helper,
        QuotesCache $quotesCache,
        Rate $rate,
        $data = []
    ) {
        $this->rateFactory = $rateFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->trackFactory = $trackFactory;
        $this->trackErrorFactory = $trackErrorFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->currencyFactory = $currencyFactory;
        $this->directoryData = $directoryData;
        $this->stockRegistry = $stockRegistry;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->quotesCache = $quotesCache;
        $this->rate = $rate;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCityRequired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Get rate calculation method
     *
     * @return int
     */
    public function getRateCalculationMethod()
    {
        return $this->getConfigData('rate_calculation_method');
    }

    /**
     * Can collect rates
     *
     * @return bool
     */
    public function canCollectRates()
    {
        return (bool)$this->getConfigFlag('active');
    }

    /**
     * Can use flatrate
     *
     * @return bool
     */
    public function canUseFlatrate()
    {
        return $this->getRateCalculationMethod() == RateCalculationMethods::METHOD_FLATRATE ? true : false;
    }

    /**
     * Get flatrate amount
     *
     * @return int|string
     */
    public function getFlatrateAmount()
    {
        return $this->getConfigData('flatrate_amount');
    }

    /**
     * Can use volumetric rates
     *
     * @return bool
     */
    public function canUseVolumetricRates()
    {
        return $this->getRateCalculationMethod() == RateCalculationMethods::METHOD_VOLUMETRIC_RATE ? true : false;
    }

    /**
     * Get volumetric rates
     *
     * @return int|string
     */
    public function getVolumetricRates()
    {
        return $this->helper->unserializeConfig($this->getConfigData('volumetric_rates'));
    }

    /**
     * Can auto create shipment
     *
     * @return bool
     */
    public function canAutoCreateShipment()
    {
        return (bool)$this->getConfigFlag('auto_create_shipping');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getCarrierTitle()];
    }

    /**
     * Get carrier title
     *
     * @return string
     */
    public function getCarrierTitle()
    {
        return $this->getConfigData('frontend_label') ?
            $this->getConfigData('frontend_label') : $this->getConfigData('title');
    }

    /**
     * Get all items
     *
     * @param DataObject|RateRequest $request
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAllItems(RateRequest $request)
    {
        $items = [];

        if ($request->getAllItems()) {

            /* @var $item \Magento\Quote\Model\Quote\Item */
            foreach ($request->getAllItems() as $item) {

                // Skip virtual product and parent item like configurable parent
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this->processAdditionalValidation($request);
    }

    /**
     * Processing additional validation to check if carrier applicable
     *
     * @param DataObject $request
     * @return Carrier|bool|DataObject
     */
    public function processAdditionalValidation(DataObject $request)
    {
        // Skip if no items
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        return $this;
    }

    /**
     * Do request
     *
     * @param string $method
     * @param DataObject|null $result
     * @return DataObject $result
     */
    protected function doRequest($method, DataObject $result = null)
    {
        if ($result === null) {
            $result = new DataObject();
        }

        $this->eventManager->dispatch(
            'fontera_parcelninja_service',
            [
                'method'    => $method,
                'params'    => $this->requestParams->toArray(),
                'result'    => $result
            ]
        );

        return $result;
    }

    /**
     * Get response from result
     *
     * @param DataObject|null $result
     * @return ServiceResponse|bool $result
     */
    private function getResponse(DataObject $result = null)
    {
        $response = $result->getData('result');

        if ($response instanceof ServiceResponse) {
            return $response;
        }

        return false;
    }

    /**
     * Fetch response from result
     *
     * @param DataObject $result
     * @return ServiceResponse $response
     * @throws \Exception
     */
    protected function fetchResponse(DataObject $result)
    {
        $response = $this->getResponse($result);

        if ($response !== false) {
            if ($response->getInternalError()) {
                throw new \Exception($response->getInternalError());
            } else {
                $httpCode = $response->getResponseHttpCode();

                if ($response->isError($httpCode)) {
                    if (!empty($parsedResponse['message'])) {
                        throw new \Exception($parsedResponse['message']);
                    } else {
                        throw new \Exception($response->getMessageForCode($httpCode));
                    }
                }
            }

            return $response;
        }

        throw new \Exception(__('An unknown error occurred.'));
    }

    /**
     * Get error result
     *
     * @param string $errorMessage
     * @param bool $useDefaultError
     * @return Result
     */
    protected function getError($errorMessage = '', $useDefaultError = false)
    {
        if ($this->getConfigData('showmethod')) {
            $this->result = $this->rateFactory->create();
            $error = $this->_rateErrorFactory->create();

            $error->setData('carrier', $this->getCarrierCode())
                ->setData('carrier_title', $this->getCarrierTitle())
                ->setData('error_message', $errorMessage);

            if ($useDefaultError || $errorMessage == '') {
                $error->setData('error_message', $this->getConfigData('specificerrmsg'));
            }

            return $error;
        }

        return false;
    }

    /**
     * Get flatrate result
     *
     * @return Result
     */
    protected function getFlatrate()
    {
        $this->result = $this->rateFactory->create();

        /* @var $rate \Magento\Quote\Model\Quote\Address\RateResult\Method */
        $rate = $this->rateMethodFactory->create();

        $methodTitle = $this->getConfigData('flatrate_method_title') ?
            $this->getConfigData('flatrate_method_title') : __('Flatrate');

        $rate->setData('carrier', $this->getCarrierCode())
            ->setData('carrier_title', $this->getCarrierTitle())
            ->setData('method', 'flatrate')
            ->setData('method_title', $methodTitle)
            ->setData('cost', $this->getFlatrateAmount())
            ->setPrice($this->getFlatrateAmount());

        return $this->result->append($rate);
    }

    /**
     * Get volumetric rate result
     *
     * @param string[] $rates
     * @return Result
     */
    protected function getVolumetricRateResult($rates)
    {
        $this->result = $this->rateFactory->create();

        /* @var $rate \Magento\Quote\Model\Quote\Address\RateResult\Method */
        $rateResult = $this->rateMethodFactory->create();

        // Calculate rates for $this->rate
        $this->calculateVolumetricRateAmount($rates);

        // Set method title
        if (!$methodTitle = $this->getConfigData('volumetric_method_title')) {
            $methodTitle = $this->rate->getTitle() ? $this->rate->getTitle() : __('Volumetric Rate');
        }

        $rateResult->setData('carrier', $this->getCarrierCode())
            ->setData('carrier_title', $this->getCarrierTitle())
            ->setData('method', 'volumetricrate')
            ->setData('method_title', $methodTitle)
            ->setData('cost', $this->rate->getFee())
            ->setPrice($this->rate->getFee());

        return $this->result->append($rateResult);
    }

    /**
     * Calculate volumetric rate amount
     *
     * Volumetric Mass= I x b x h/5000 (for all the products in the basket).
     * Actual Mass = combined weight of all the products in the basket.
     *
     * @param string[] $rates
     * @return Rate
     */
    protected function calculateVolumetricRateAmount($rates)
    {
        $mass = $this->getMass();

        // Loop through each configured rate and fetch matching
        foreach ($rates as $rate) {
            if (isset($rate['weight_from'])
                && isset($rate['weight_to'])
                && isset($rate['name'])
                && isset($rate['delivery_fee'])
            ) {
                if ($rate['weight_to'] == '*') {
                    $rate['weight_to'] = '1000000000';
                }

                if ($mass > $rate['weight_from'] && $mass < $rate['weight_to']) {
                    $this->rate->setTitle($rate['name']);
                    $this->rate->setFee($rate['delivery_fee']);

                    break;
                }
            }
        }

        return $this->rate;
    }

    /**
     * Get mass
     *
     * @return float
     */
    protected function getMass()
    {
        $volumetricMass = 0;
        $actualMass = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->getAllItems($this->request) as $item) {

            $product = $item->getProduct();

            if ($product && !$product->getData('free_delivery')) {
                $length = $product->getData('length');
                $width = $product->getData('width');
                $height = $product->getData('height');

                $volumetricMass = $volumetricMass + ((($length * $width * $height) / 5000) * $item->getQty());

                $actualMass = $actualMass + ($product->getWeight() * $item->getQty());
            }
        }

        // Use volumetric weight if larger than actual mass
        if ($volumetricMass > $actualMass) {
            $actualMass = $volumetricMass;
        }

        return (double)$actualMass;
    }
}