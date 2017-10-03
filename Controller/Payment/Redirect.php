<?php
/**
 * Copyright (c) 2017 INPAY S.A. * Permission is hereby granted, free of charge, to any person obtaining a copy * of this software and associated documentation files (the "Software"), to deal * in the Software without restriction, including without limitation the rights * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell * copies of the Software, and to permit persons to whom the Software is * furnished to do so, subject to the following conditions: * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE  * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,  * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. *
 * @package InPay_InPay
 */

namespace InPay\InPay\Controller\Payment;

/**
 * Class Redirect
 * @package InPay\InPay\Controller\Payment
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * Checkout session model
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $additionalData = $payment->getAdditionalData();
        $additionalData = json_decode($additionalData, true);
        $url = $additionalData['redirectUrl'];

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setStatusHeader(302);
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
}
