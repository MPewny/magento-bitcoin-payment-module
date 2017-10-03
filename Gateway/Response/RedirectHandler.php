<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Gateway\Response;

use InPay\InPay\Gateway\Validator\ResponseCodeValidator;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class RedirectHandler
 * @package InPay\InPay\Gateway\Response
 */
class RedirectHandler implements HandlerInterface
{
    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment data object should be provided'));
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        if (!isset($response[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response invalid'));
        }
        $response = json_decode($response[0], true);
        if (empty($response)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response invalid'));
        }

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[ResponseCodeValidator::INVOICE_CODE]);
        $payment->setAdditionalData(json_encode([
            'message' => $response[ResponseCodeValidator::MESSAGE],
            'redirectUrl' => $response[ResponseCodeValidator::REDIRECT_URL]
        ]));
        $payment->setIsTransactionClosed(false);
    }
}
