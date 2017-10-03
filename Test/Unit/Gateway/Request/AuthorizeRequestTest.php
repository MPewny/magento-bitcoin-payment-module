<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Gateway\Request;

use InPay\InPay\Gateway\Request\AuthorizationRequest;
use InPay\InPay\Helper\Config as InPayConfig;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class AuthorizeRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var InPayConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inPayConfig;

    public function setup()
    {
        $this->inPayConfig = $this->getMockBuilder(InPayConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMock(OrderAdapterInterface::class);
        $this->addressMock = $this->getMock(AddressAdapterInterface::class);
        $this->payment = $this->getMock(PaymentDataObjectInterface::class);

        $this->payment->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::any())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testBuild()
    {
        $apiKey = 'secure_token';
        $invoiceUrl = 'https://apitest.inpay.pl/invoice/create';
        $callbackUrl = 'https://example.com/checkout/onepage/callback';
        $successUrl = 'https://example.com/checkout/onepage/success';
        $failUrl = 'https://example.com/checkout/onepage/callback';

        $invoiceId = 1001;
        $grandTotal = 12.20;
        $currencyCode = 'USD';
        $email = 'user@domain.com';

        $expectation = [
            'apiKey' => $apiKey,
            'request_uri' => $invoiceUrl,
            'orderCode' => $invoiceId,
            'amount' => $grandTotal,
            'currency' => $currencyCode,
            'customerEmail' => $email,
            'callbackUrl' => $callbackUrl,
            'successUrl' => $successUrl,
            'failUrl' => $failUrl
        ];

        $this->inPayConfig->expects(static::any())
            ->method('getApiKey')
            ->willReturn($apiKey);

        $this->inPayConfig->expects(static::any())
            ->method('getInvoiceUrl')
            ->willReturn($invoiceUrl);

        $this->inPayConfig->expects(static::any())
            ->method('getCallbackUrl')
            ->willReturn($callbackUrl);

        $this->inPayConfig->expects(static::any())
            ->method('getSuccessUrl')
            ->willReturn($successUrl);

        $this->inPayConfig->expects(static::any())
            ->method('getFailUrl')
            ->willReturn($failUrl);

        $this->orderMock->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($invoiceId);

        $this->orderMock->expects(static::once())
            ->method('getGrandTotalAmount')
            ->willReturn($grandTotal);

        $this->orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);

        $this->addressMock->expects(static::once())
            ->method('getEmail')
            ->willReturn($email);

        /** @var ConfigInterface $inPayConfig */
        $request = new AuthorizationRequest($this->inPayConfig, $this->logger);

        static::assertEquals(
            $expectation,
            $request->build(['payment' => $this->payment])
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testNoPaymentInSubject()
    {
        /** @var ConfigInterface $inPayConfig */
        $request = new AuthorizationRequest($this->inPayConfig, $this->logger);

        $request->build([]);
    }
}
