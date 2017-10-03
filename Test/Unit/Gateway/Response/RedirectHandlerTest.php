<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Gateway\Response;

use InPay\InPay\Gateway\Response\RedirectHandler;
use InPay\InPay\Gateway\Validator\ResponseCodeValidator;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class RedirectHandlerTest
 * @package InPay\InPay\Test\Unit\Gateway\Response
 */
class RedirectHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentModel;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    public function setup()
    {
        $this->paymentDO = $this->getMock(PaymentDataObjectInterface::class);
        $this->paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testNoPaymentInSubject()
    {
        $request = new RedirectHandler();
        $request->handle([], [json_encode([])]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEmptyResponse()
    {
        $request = new RedirectHandler();
        $request->handle(['payment' => $this->paymentDO], []);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEmptyJsonResponse()
    {
        $request = new RedirectHandler();
        $request->handle(['payment' => $this->paymentDO], [json_encode([])]);
    }

    public function testHandle()
    {
        $response = [
            ResponseCodeValidator::SUCCESS => 1,
            ResponseCodeValidator::MESSAGE => 'SOME_MESSAGE',
            ResponseCodeValidator::REDIRECT_URL => 'http://example.com/redirect/123456789',
            ResponseCodeValidator::INVOICE_CODE => '123456789'
        ];

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentModel);

        $this->paymentModel->expects(static::once())
            ->method('setTransactionId')
            ->with($response[ResponseCodeValidator::INVOICE_CODE]);

        $this->paymentModel->expects(static::once())
            ->method('setAdditionalData')
            ->with(json_encode([
                'message' => $response[ResponseCodeValidator::MESSAGE],
                'redirectUrl' => $response[ResponseCodeValidator::REDIRECT_URL]
            ]));

        $this->paymentModel->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false);

        $request = new RedirectHandler();
        $request->handle(['payment' => $this->paymentDO], [json_encode($response)]);
    }
}
