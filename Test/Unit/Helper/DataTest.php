<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Helper;

use InPay\InPay\Helper\Config;
use Magento\Framework\App\Helper\Context;

/**
 * Class DataTest
 * @package InPay\InPay\Test\Unit\Helper
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \InPay\InPay\Helper\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \InPay\InPay\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @return array
     */
    public function initDataProvider()
    {
        return [
            [
                'api_secret' => '123456789',
                'api_hash' => '9992e4e7cf4a6755237f0d240766cd7c274555cc283c8926cb7f89b19df07c6068f6a0899b00188e985a54607beb857acbbd5bbb3c7d86fa483c50fd11556146',
                'post' => [
                    'orderCode' => '000000001',
                    'amount' => '10.00',
                    'currency' => 'USD',
                    'fee' => '0',
                    'invoiceCode' => '1234526789123',
                    'status' => 'confirmed',
                    'optData' => []
                ],
                'valid' => true
            ],
            [
                'api_secret' => '123',
                'api_hash' => '5c72080ec3b87d2e7fc655e64a52a36a96ef0645dd812861d4f0c32fd37c505b43b5e7643c2c8835be666d03a25b9c8777273bef251d3b9674b72ed647cc0a4b',
                'post' => [
                    'orderCode' => '000000002',
                    'amount' => '15.00',
                    'currency' => 'BTC',
                    'fee' => '0',
                    'invoiceCode' => '987654321',
                    'status' => 'received',
                    'optData' => []
                ],
                'valid' => true
            ],
            [
                'api_secret' => 'WRONG_SECRET',
                'api_hash' => 'WRONG_HASH',
                'post' => [
                    'orderCode' => '000000003',
                    'amount' => '65.00',
                    'currency' => 'USD',
                    'fee' => '0',
                    'invoiceCode' => '987654321',
                    'status' => 'received',
                    'optData' => []
                ],
                'valid' => false
            ],
            [
                'api_secret' => '123456789',
                'api_hash' => '123456789',
                'post' => [],
                'valid' => false
            ]
        ];
    }

    /**
     * @param $apiSecret
     * @param $apiHash
     * @param $post
     * @param $valid
     * @dataProvider initDataProvider
     */
    public function testHashValidation($apiSecret, $apiHash, $post, $valid)
    {
        $this->prepareApiSecret($apiSecret);
        $this->assertEquals($valid, $this->helper->isHashValid($post, $apiHash));
    }

    /**
     * @param $apiSecret
     */
    private function prepareApiSecret($apiSecret)
    {
        $this->config->expects($this->any())
            ->method('getApiSecret')
            ->willReturn($apiSecret);
    }

    protected function setUp()
    {
        $this->mockContext();

        $this->helper = new \InPay\InPay\Helper\Data(
            $this->context,
            $this->config
        );
    }

    private function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
