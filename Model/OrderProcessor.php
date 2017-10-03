<?php
/**
 * Copyright (c) 2017 INPAY S.A.
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Model;

/**
 * Class OrderProcessor
 * @package InPay\InPay\Model
 */
class OrderProcessor
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * OrderProcessor constructor.
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;
        $this->orderConfig = $orderConfig;
    }

    /**
     * @param $order
     * @param $status
     * @param $invoiceCode
     * @return bool
     */
    public function proceedStatus($order, $status, $invoiceCode)
    {
        switch ($status) {
            // Map to Magento status Complete
            case 'confirmed':
                $this->markOrderAsPaymentConfirmed($order, $invoiceCode);
                return true;
                break;

            // Map to Magento state Processing
            case 'received':
                $this->markOrderAsPaymentReceived($order, $invoiceCode);
                return true;
                break;

            // Map to Magento State Closed
            case 'expired':
            case 'aborted':
            case 'invalid':
                $this->markOrderAsCancelled($order);
                return true;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param $invoiceCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markOrderAsPaymentConfirmed(\Magento\Sales\Api\Data\OrderInterface $order, $invoiceCode)
    {
        if (!$order->getTotalDue() > 0 || !$order->canInvoice()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'markOrderAsPaymentConfirmed called but order does not have a balance due or Can`t invoice.'
                )
            );
        }

        $this->createInvoice($order);

        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->addStatusToHistory(
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                __('Payment has been confirmed by InPay. Transaction id: %1', $invoiceCode)
            );

        $payment = $order->getPayment();
        if (!$payment) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Order don\'t have payment object'
                )
            );
        }

        $payment->setLastTransId($invoiceCode);

        if (!$order->getEmailSent()) {
            $this->orderSender->send($order);
        }

        try {
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Problem during marking order as payment confirmed'
                )
            );
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createInvoice($order)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $order->prepareInvoice();
        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice without products.'));
        }
        $invoice->addComment(__('Invoice automatically by InPay'))
            ->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);

        $invoice->register();

        $order->addRelatedObject($invoice);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param $invoiceCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markOrderAsPaymentReceived(\Magento\Sales\Api\Data\OrderInterface $order, $invoiceCode)
    {
        $order
            ->addStatusToHistory(
                false,
                __('Payment has been received by InPay. Waiting for confirmation. Transaction id: %1', $invoiceCode)
            );

        try {
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Problem during marking order as payment received'
                )
            );
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markOrderAsCancelled(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
            ->setStatus($this->orderConfig->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_CANCELED))
            ->addStatusToHistory(
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                __('InPay cancel transaction')
            );

        try {
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Problem during marking order as cancelled'
                )
            );
        }
    }
}
