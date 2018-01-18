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

namespace Fontera\Parcelninja\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Zend\Http\Request;

/**
 * Class ApiTest
 * @package Fontera\Parcelninja\Test\Unit\Model
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Service mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_serviceMock;

    /**
     * Helper
     *
     * @var \Fontera\Parcelninja\Helper\Data
     */
    protected $_helper;

    /**
     * CURL
     *
     * @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $curl;

    /**
     * Service response
     *
     * @var \Fontera\Parcelninja\Model\Service\Response
     */
    protected $_serviceResponse;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->_helper = $objectManager->getObject('Fontera\Parcelninja\Helper\Data');

        $this->curl = $this->getMock('Magento\Framework\HTTP\Adapter\Curl', [], [], '', false);
        $curlFactory = $this->getMock('Magento\Framework\HTTP\Adapter\CurlFactory', ['create'], [], '', false);
        $curlFactory->expects($this->any())->method('create')->will($this->returnValue($this->curl));

        $this->_serviceMock = $this->getMockBuilder('Fontera\Parcelninja\Model\Service')
            ->setConstructorArgs([$this->_helper, $curlFactory])
            ->getMock();

        $this->_serviceResponse = $objectManager->getObject('Fontera\Parcelninja\Model\Service\Response');
    }

    /**
     * Tears down, clear all
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->_helper = null;
        $this->curl = null;
        $this->_serviceMock = null;
        $this->_serviceResponse = null;
    }

    /**
     * Call protected method
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $object
     * @param string $method
     * @param [] $args
     * @return object|array|int
     */
    public static function callProtectedMethod($object, $method, $args = [])
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    /**
     * Test request action invalid exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testRequestActionInvalidException()
    {
        self::callProtectedMethod($this->_serviceMock, '_request', ['', Request::METHOD_GET]);
    }

    /**
     * Test request action not string exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testRequestActionNotStringException()
    {
        self::callProtectedMethod($this->_serviceMock, '_request', [[], Request::METHOD_GET]);
    }

    /**
     * Test request method invalid exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testRequestMethodInvalidException()
    {
        self::callProtectedMethod($this->_serviceMock, '_request', ['some_action', '']);
    }

    /**
     * Test request method not string exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testRequestMethodNotStringException()
    {
        self::callProtectedMethod($this->_serviceMock, '_request', ['some_action', []]);
    }

    /**
     * Test request method not allowed exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testRequestMethodNotAllowedException()
    {
        self::callProtectedMethod($this->_serviceMock, '_request', ['some_action', Request::METHOD_HEAD]);
    }

    /**
     * Test getInbounds
     *
     * @covers Fontera\Parcelninja\Model\Service::getInbounds
     * @return void
     */
    public function testGetInbounds()
    {
        //self::assertEquals(21, $this->_serviceResponse->getData());




        /*$curlMock = $this->getCurlMock(['post', 'getBody', 'setOptions']);
        $curlMock->expects($this->once())
            ->method('post');
        $curlMock->expects($this->once())
            ->method('setOptions');
        $curlMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->returnPackages));
        $this->partnersModelMock->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($curlMock));

        $cacheMock = $this->getCacheMock(['savePartnersToCache']);
        $cacheMock->expects($this->once())
            ->method('savePartnersToCache');
        $this->partnersModelMock->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cacheMock));
        $this->partnersModelMock->expects($this->once())
            ->method('getReferer');

        $this->partnersModelMock->getPartners();*/
    }
}