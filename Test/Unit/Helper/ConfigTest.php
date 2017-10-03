<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Helper;

use InPay\InPay\Helper\Config as InPayHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var InPayHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @return array
     */
    public function initDataProvider()
    {
        return [
            [
                'data' => [
                    'api_key' => '123456789',
                    'api_secret' => '123456789',
                    'test' => true
                ],
                'url' => [
                    'inpay/payment/ipn' => 'http://example.com/inpay/payment/ipn',
                    'checkout/onepage/success' => 'http://example.com/checkout/onepage/success',
                    'checkout/cart' => 'http://example.com/checkout/cart'
                ],
                'expected' => [
                    'url' => InPayHelper::TEST_URL,
                    'invoice_url' => InPayHelper::TEST_URL . InPayHelper::INVOICE_URL_PATH
                ]
            ],
            [
                'data' => [
                    'api_key' => 'key_api',
                    'api_secret' => 'secret_api',
                    'test' => false
                ],
                'url' => [
                    'inpay/payment/ipn' => 'https://example.com/inpay/payment/ipn',
                    'checkout/onepage/success' => 'https://example.com/checkout/onepage/success',
                    'checkout/cart' => 'https://example.com/checkout/cart'
                ],
                'expected' => [
                    'url' => InPayHelper::PRODUCTION_URL,
                    'invoice_url' => InPayHelper::PRODUCTION_URL . InPayHelper::INVOICE_URL_PATH
                ]
            ],
        ];
    }

    /**
     * @param $data
     * @dataProvider initDataProvider
     */
    public function testGetApiKey($data)
    {
        $this->prepareConfig($data);
        $this->assertEquals($this->helper->getApiKey(), $data['api_key']);
    }

    private function prepareConfig($data)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                [
                    InPayHelper::XML_PATH_INPAY_API_KEY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['api_key']
                ],
                [
                    InPayHelper::XML_PATH_INPAY_API_SECRET,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['api_secret']
                ],
                [
                    InPayHelper::XML_PATH_INPAY_TEST,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['test']
                ],
            ]);
    }

    /**
     * @param $data
     * @dataProvider initDataProvider
     */
    public function testGetApiSecret($data)
    {
        $this->prepareConfig($data);
        $this->assertEquals($data['api_secret'], $this->helper->getApiSecret());
    }

    /**
     * @param $data
     * @dataProvider initDataProvider
     */
    public function testIsTestModeEnabled($data)
    {
        $this->prepareConfig($data);
        $this->assertEquals($data['test'], $this->helper->isTestModeEnabled());
    }

    /**
     * @param $data
     * @param $url
     * @param $expected
     * @dataProvider initDataProvider
     */
    public function testGetUrl($data, $url, $expected)
    {
        $this->prepareConfig($data);
        $this->assertEquals($expected['url'], $this->helper->getUrl());
    }

    /**
     * @param $data
     * @param $url
     * @param $expected
     * @dataProvider initDataProvider
     */
    public function testGetInvoiceUrl($data, $url, $expected)
    {
        $this->prepareConfig($data);
        $this->assertEquals($expected['invoice_url'], $this->helper->getInvoiceUrl());
    }

    /**
     * @param $data
     * @param $url
     * @param $expected
     * @dataProvider initDataProvider
     */
    public function testGetCallbackUrl($data, $url, $expected)
    {
        $this->prepareUrl($url);
        $this->assertEquals($url['inpay/payment/ipn'], $this->helper->getCallbackUrl());
    }

    /**
     * @param $url
     */
    private function prepareUrl($url)
    {
        $data = [];
        foreach ($url as $key => $value) {
            $data[] = [$key, null, $value];
        }

        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->willReturnMap($data);
    }

    /**
     * @param $data
     * @param $url
     * @param $expected
     * @dataProvider initDataProvider
     */
    public function testGetSuccessUrl($data, $url, $expected)
    {
        $this->prepareUrl($url);
        $this->assertEquals($this->helper->getSuccessUrl(), $url['checkout/onepage/success']);
    }

    /**
     * @param $data
     * @param $url
     * @param $expected
     * @dataProvider initDataProvider
     */
    public function testGetFailUrl($data, $url, $expected)
    {
        $this->prepareUrl($url);
        $this->assertEquals($this->helper->getFailUrl(), $url['checkout/cart']);
    }

    protected function setUp()
    {
        $this->mockContext();

        $this->helper = new \InPay\InPay\Helper\Config(
            $this->context
        );
    }

    private function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);

        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
    }
}
