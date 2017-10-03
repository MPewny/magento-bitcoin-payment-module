<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Gateway\Request;

use InPay\InPay\Helper\Config as InPayConfig;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var InPayConfig
     */
    private $inpayConfig;

    /**
     * AuthorizationRequest constructor.
     * @param InPayConfig $inpayConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        InPayConfig $inpayConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->inpayConfig = $inpayConfig;
        $this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();

        $request = [
            'apiKey' => $this->inpayConfig->getApiKey(),
            'request_uri' => $this->inpayConfig->getInvoiceUrl(),
            'orderCode' => $order->getOrderIncrementId(),
            'amount' => (float)number_format(str_replace(',', '.', $order->getGrandTotalAmount()), 2),
            'currency' => $order->getCurrencyCode(),
            'customerEmail' => is_null($address) ? '' : (string)$address->getEmail(),
            'callbackUrl' => $this->inpayConfig->getCallbackUrl(),
            'successUrl' => $this->inpayConfig->getSuccessUrl(),
            'failUrl' => $this->inpayConfig->getFailUrl()
        ];

        $this->logger->debug(json_encode([
            'response' => $request,
            'message' => 'request send to payment gateway'
        ]));

        return $request;
    }
}
