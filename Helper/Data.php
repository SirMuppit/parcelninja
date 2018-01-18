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

namespace Fontera\Parcelninja\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\Store;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream as ZendStreamWriter;

/**
 * Class Data
 * @package Fontera\Parcelninja\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Service endpoint
     *
     * @var string
     */
    //const SERVICE_ENDPOINT = 'https://qa-storeapi.parcelninja.com/api/v1/';
    const SERVICE_ENDPOINT = 'https://www.parcelninja.co.za/api/v1/';

    /**
     * Dropship service endpoint
     *
     * @var string
     */
    const DROPSHIP_SERVICE_ENDPOINT = 'http://ds.fontera.com/api/v1/';

    /**
     * Logger
     *
     * @var null
     */
    protected $zendLogger = null;

    /**
     * Log writer
     *
     * @var null
     */
    protected $zendLogWriter = null;

    /**
     * Logger
     *
     * Added correct logger class
     *
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $logger;

    /**
     * General settings
     *
     * @var string
     */
    protected $generalSettings = 'general_settings';

    /**
     * API settings
     *
     * @var string
     */
    protected $apiSettings = 'api_settings';

    /**
     * Dropship API settings
     *
     * @var string
     */
    protected $dropshipApiSettings = 'dropship_api_settings';

    /**
     * Directory list
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Encryptor
     *
     * @var Encryptor
     */
    private $encryptor;

    /**
     * Data constructor
     *
     * By default, all objects created via automatic constructor dependency injection are “singleton-ish” objects.
     * i.e. they’re created via the object manager’s get method.
     *
     * If you want a new instance of an object, i.e. you want the object manager to use create, you’ll need to add some
     * additional <type/> configuration to your module’s di.xml file.
     *
     * If the di.xml shared attribute is set to false, then Magento will use the create method to instantiate an object
     * every time it encounters ModuleListInterface as an automatically injected constructor argument. The shared
     * attribute has no effect on objects instantiated directly via PHPs new keyword or the object managers two methods.
     *
     * This attribute is named shared due to an implementation detail in the object manager. When you use get to
     * instantiate an object, the object manager stores all the already instantiated objects in _sharedInstances array.
     *
     * When you configure a specific type (i.e. a specific PHP class) with shared="false", youre telling Magento that
     * you don’t want to use this _sharedInstances array.
     *
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param DirectoryList $directoryList
     * @param Encryptor $encryptor
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        DirectoryList $directoryList,
        Encryptor $encryptor
    ) {
        $this->moduleList = $moduleList;
        $this->directoryList = $directoryList;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Debug
     *
     * @param string[] $mixed
     * @param int $priority
     * @param string $fileSuffix
     * @throws \Zend_Log_Exception
     * @return void
     */
    public function debug($mixed = [], $priority = ZendLogger::DEBUG, $fileSuffix = 'debug')
    {
        if ($this->isActive() && $this->isDebug()) {

            if (is_array($mixed) || is_object($mixed)) {
                $mixed = print_r($mixed, true);
            }

            try {
                $file = strtolower($this->_getModuleName()) . '-' . $fileSuffix . '.log';
                $logPath = $this->directoryList->getPath('log') . '/' . $file;

                if ($this->zendLogger === null) {
                    $this->zendLogger = new ZendLogger;
                }

                if ($this->zendLogWriter === null) {
                    $this->zendLogWriter = new ZendStreamWriter($logPath);
                    $this->zendLogger->addWriter($this->zendLogWriter);
                }

                $this->zendLogger->log($priority, $mixed);
            } catch (\Zend_Log_Exception $e) {
                // Silence
            }
        }
    }

    /**
     * Handle exception
     *
     * @param \Exception $exception
     * @return void
     */
    public function handleException(\Exception $exception)
    {
        if ($exception instanceof \Exception) {
            $this->debug($exception->getMessage(), ZendLogger::ERR, 'exception');
        }
    }

    /**
     * Is module active
     *
     * @return bool
     */
    public function isActive()
    {
        return (boolean)$this->getConfigSetting('active');
    }

    /**
     * Is debug active
     *
     * @return bool
     */
    public function isDebug()
    {
        return (boolean)$this->getConfigSetting('debug');
    }

    /**
     * Get config setting
     *
     * @param string $config
     * @param string $store
     * @return string
     */
    public function getConfigSetting($config, $store = null)
    {
        return $this->scopeConfig->getValue(
            strtolower($this->_getModuleName()) . '/' . $this->generalSettings . '/' . $config,
            ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Get API setting
     *
     * @param string $config
     * @param string $store
     * @return string
     */
    public function getApiSetting($config, $store = null)
    {
        return $this->scopeConfig->getValue(
            strtolower($this->_getModuleName()) . '/' . $this->apiSettings . '/' . $config,
            ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Get dropship API setting
     *
     * @param string $config
     * @param string $store
     * @return string
     */
    public function getDropshipApiSetting($config, $store = null)
    {
        return $this->scopeConfig->getValue(
            strtolower($this->_getModuleName()) . '/' . $this->dropshipApiSettings . '/' . $config,
            ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function getVersion()
    {
        $module = $this->moduleList->getOne($this->_getModuleName());

        if (is_array($module) && !empty($module['setup_version'])) {
            return $module['setup_version'];
        }

        return 'Unknown';
    }

    /**
     * Get service endpoint
     *
     * @return string
     */
    public static function getServiceEndpoint()
    {
        return self::SERVICE_ENDPOINT;
    }

    /**
     * Get API username
     *
     * @return string
     */
    public function getApiUsername()
    {
        return $this->getApiSetting('api_username');
    }

    /**
     * Get API password
     *
     * @return string
     */
    public function getApiPassword()
    {
        return $this->getApiSetting('api_password');
    }

    /**
     * Get API timeout
     *
     * @return int|string $value
     */
    public function getApiTimeout()
    {
        $value = $this->getApiSetting('timeout');

        if (!is_numeric($value)) {
            $value = 60;
        }

        return $value;
    }

    /**
     * Get dropship service endpoint
     *
     * @return string
     */
    public static function getDropshipServiceEndpoint()
    {
        return self::DROPSHIP_SERVICE_ENDPOINT;
    }

    /**
     * Get dropship API token
     *
     * @return string
     */
    public function getDropshipApiToken()
    {
        return $this->encryptor->decrypt($this->getDropshipApiSetting('api_token'));
    }

    /**
     * Get dropship API timeout
     *
     * @return int|string $value
     */
    public function getDropshipApiTimeout()
    {
        $value = $this->getDropshipApiSetting('timeout');

        if (!is_numeric($value)) {
            $value = 60;
        }

        return $value;
    }

    /**
     * Is order to dropship active
     *
     * @return bool
     */
    public function isOrderToDropshipActive()
    {
        return (boolean)$this->getDropshipApiSetting('order_to_dropship_active');
    }

    /**
     * Unserialize config
     *
     * @param string|string[] $value
     * @return string[] $result
     */
    public function unserializeConfig($value)
    {
        $result = [];

        if (is_string($value) && !empty($value)) {
            try {
                $result = @unserialize($value);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        } elseif (is_array($value) && !empty($value)) {
            $result = $value;
        }

        unset($result['__empty']);

        return $result;
    }

    /**
     * Serialize config
     *
     * @param string|string[] $value
     * @return string $result
     */
    public function serializeConfig($value)
    {
        $result = '';

        if (is_array($value)) {
            try {
                $result = @serialize($value);
            } catch (\Exception $e) {
                $this->handleException($e);
            }
        }

        return $result;
    }
}
