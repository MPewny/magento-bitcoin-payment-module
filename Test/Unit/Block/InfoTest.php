<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Block;

use InPay\InPay\Block\Info;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class InfoTest
 * @package InPay\InPay\Test\Unit\Block
 */
class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var InfoInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInfoModel;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMock(ConfigInterface::class);
        $this->paymentInfoModel = $this->getMock(InfoInterface::class);
    }

    public function testGetSpecificationInformation()
    {
        $this->config->expects(static::once())
            ->method('getValue')
            ->willReturnMap(
                [
                    ['paymentInfoKeys', null, $this->getPaymentInfoKeys()]
                ]
            );
        $this->paymentInfoModel->expects(static::atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                $this->getAdditionalFields()
            );

        $info = new Info(
            $this->context,
            $this->config,
            [
                'is_secure_mode' => 0,
                'info' => $this->paymentInfoModel
            ]
        );

        static::assertSame($this->getExpectedResult(), $info->getSpecificInformation());
    }

    /**
     * @return string
     */
    private function getPaymentInfoKeys()
    {
        return 'PUBLIC_DATA';
    }

    /**
     * @return array
     */
    private function getAdditionalFields()
    {
        return [
            ['FRAUD_MSG_LIST', ['Some issue happened', 'And some other happened too']],
            ['non_info_field', 'X'],
            ['PUBLIC_DATA', 'Payed with USD']
        ];
    }

    /**
     * @return array
     */
    private function getExpectedResult()
    {
        return [
            (string)__('PUBLIC_DATA') => 'Payed with USD'
        ];
    }

    public function testGetSpecificationInformationSecure()
    {
        $this->config->expects(static::exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['paymentInfoKeys', null, $this->getPaymentInfoKeys()]
                ]
            );
        $this->paymentInfoModel->expects(static::atLeastOnce())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                $this->getAdditionalFields()
            );

        $info = new Info(
            $this->context,
            $this->config,
            [
                'is_secure_mode' => 1,
                'info' => $this->paymentInfoModel
            ]
        );

        static::assertSame($this->getSecureExpectedResult(), $info->getSpecificInformation());
    }

    /**
     * @return array
     */
    private function getSecureExpectedResult()
    {
        return [
            (string)__('PUBLIC_DATA') => 'Payed with USD'
        ];
    }
}
