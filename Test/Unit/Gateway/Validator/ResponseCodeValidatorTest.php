<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Gateway\Validator;

use InPay\InPay\Gateway\Validator\ResponseCodeValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class ResponseCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    public function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMock(ResultInterface::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEmptyResponse()
    {
        $validator = new ResponseCodeValidator($this->resultFactory, $this->logger);

        static::assertInstanceOf(
            ResultInterface::class,
            $validator->validate(['response' => ''])
        );
    }

    /**
     * @param array $response
     * @param array $expectationToResultCreation
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $response, array $expectationToResultCreation)
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $expectationToResultCreation
            )
            ->willReturn($this->resultMock);

        $validator = new ResponseCodeValidator($this->resultFactory, $this->logger);

        static::assertInstanceOf(
            ResultInterface::class,
            $validator->validate(['response' => $response])
        );
    }

    public function validateDataProvider()
    {
        return [
            'fail_1' => [
                'response' => [json_encode([])],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'fail_2' => [
                'response' => [json_encode([
                    ResponseCodeValidator::SUCCESS => 0,
                    ResponseCodeValidator::MESSAGE => '',
                    ResponseCodeValidator::REDIRECT_URL => 'http://example.com/redirect?n=1',
                    ResponseCodeValidator::INVOICE_CODE => '1'
                ])],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'fail_3' => [
                'response' => ['2' => json_encode([
                    ResponseCodeValidator::SUCCESS => 0,
                    ResponseCodeValidator::MESSAGE => '',
                    ResponseCodeValidator::REDIRECT_URL => 'http://example.com/redirect?n=1',
                    ResponseCodeValidator::INVOICE_CODE => '1'
                ])],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'success' => [
                'response' => [json_encode([
                    ResponseCodeValidator::SUCCESS => 1,
                    ResponseCodeValidator::MESSAGE => 'Redirect user to this URL',
                    ResponseCodeValidator::REDIRECT_URL => 'http://example.com/redirect?n=123456',
                    ResponseCodeValidator::INVOICE_CODE => '123456'
                ])],
                'expectationToResultCreation' => [
                    'isValid' => true,
                    'failsDescription' => []
                ]
            ]
        ];
    }
}
