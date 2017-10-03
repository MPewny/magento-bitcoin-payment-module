<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Gateway\Http;

use InPay\InPay\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransferFactoryTest
 * @package InPay\InPay\Test\Unit\Gateway\Http
 */
class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $request = [
            'apiKey' => 'SOME_API_KEY',
            'orderCode' => '1000000',
            'amount' => '10.00',
            'currency' => 'PLN'
        ];

        $transferBuilder = $this->getMockBuilder(TransferBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $transferObject = $this->getMock(TransferInterface::class);

        $transferBuilder->expects(static::once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $request['request_uri'] = 'https://inpay.pl';

        $transferBuilder->expects(static::once())
            ->method('setMethod')
            ->with('POST')
            ->willReturnSelf();

        $transferBuilder->expects(static::once())
            ->method('shouldEncode')
            ->willReturnSelf();

        $transferBuilder->expects(static::once())
            ->method('setUri')
            ->with($request['request_uri'])
            ->willReturnSelf();

        $transferBuilder->expects(static::once())
            ->method('setHeaders')
            ->with(
                [
                    'Content-type: application/x-www-form-urlencoded'
                ]
            )
            ->willReturnSelf();

        $transferBuilder->expects(static::once())
            ->method('build')
            ->willReturn($transferObject);

        $transferFactory = new TransferFactory($transferBuilder);

        static::assertSame(
            $transferObject,
            $transferFactory->create($request)
        );
    }
}
