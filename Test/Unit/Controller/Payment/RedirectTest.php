<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Controller\Payment;

use InPay\InPay\Controller\Payment\Redirect;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Class RedirectTest
 * @package InPay\InPay\Test\Unit\Controller\Payment
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSession;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    protected function setUp()
    {
        $this->mockContext();
        $this->mockCheckout();
    }

    private function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->redirect);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);
    }

    private function mockCheckout()
    {
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession->expects($this->any())
            ->method('getLastRealOrder')
            ->willReturn($this->order);

        $this->order->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->payment);
    }

    public function getRedirectionUrl()
    {
        return [
            [
                'redirectUrl' => 'http://example.com/a?e=1'
            ],
            [
                'redirectUrl' => 'http://example.com/a?e=5'
            ]
        ];
    }

    /**
     * @dataProvider getRedirectionUrl
     * @param $redirectUrl
     */
    public function testRedirection($redirectUrl)
    {
        $this->mockRedirectionUrl($redirectUrl);
        $redirect = new Redirect(
            $this->context,
            $this->checkoutSession
        );

        $this->redirect->expects(static::once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $redirect->execute();
    }

    private function mockRedirectionUrl($redirectUrl)
    {
        $this->payment->expects($this->any())
            ->method('getAdditionalData')
            ->willReturn(
                json_encode([\InPay\InPay\Gateway\Validator\ResponseCodeValidator::REDIRECT_URL => $redirectUrl])
            );
    }
}
