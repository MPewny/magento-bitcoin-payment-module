<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Model;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class OrderProcessorTest
 * @package InPay\InPay\Test\Unit\Model
 */
class OrderProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderSender;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderInvoice;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderConfig;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderPayment;

    /**
     * @var \InPay\InPay\Model\OrderProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderProcessor;

    /**
     * @return array
     */
    public function getCanceledOrders()
    {
        $result = [];
        foreach ($this->getOrder() as $key => $order) {
            foreach ($this->getCancelStatuses() as $status) {
                unset($order['valid']);
                $result[$key] = $order;
                $result[$key]['status'] = $status;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getOrder()
    {
        return [
            [
                'totalDue' => 5,
                'totalQty' => 1,
                'canInvoice' => true,
                'emailSend' => true,
                'valid' => true
            ],
            [
                'totalDue' => 50,
                'totalQty' => 10,
                'canInvoice' => true,
                'emailSend' => false,
                'valid' => true
            ],
            [
                'totalDue' => 5,
                'totalQty' => 0,
                'canInvoice' => true,
                'emailSend' => true,
                'valid' => false
            ],
            [
                'totalDue' => 0,
                'totalQty' => 1,
                'canInvoice' => true,
                'emailSend' => true,
                'valid' => false
            ],
            [
                'totalDue' => 0,
                'totalQty' => 1,
                'canInvoice' => false,
                'emailSend' => true,
                'valid' => false
            ]
        ];
    }

    /**
     * @return array
     */
    private function getCancelStatuses()
    {
        return ['expired', 'aborted', 'invalid'];
    }

    /**
     * @return array
     */
    public function getReceivedOrders()
    {
        $result = [];
        foreach ($this->getOrder() as $key => $order) {
            foreach ($this->getReceivedStatuses() as $status) {
                unset($order['valid']);
                $result[$key . $status] = $order;
                $result[$key . $status]['status'] = $status;
            }
        }

        return $result;
    }

    private function getReceivedStatuses()
    {
        return ['received'];
    }

    /**
     * @return array
     */
    public function getInvalidConfirmedOrders()
    {
        $result = [];
        foreach ($this->getOrder() as $key => $order) {
            foreach ($this->getConfirmedStatuses() as $status) {
                if ($order['valid'] === false) {
                    unset($order['valid']);
                    $result[$key . $status] = $order;
                    $result[$key . $status]['status'] = $status;
                }
            }
        }

        return $result;
    }

    private function getConfirmedStatuses()
    {
        return ['confirmed'];
    }

    /**
     * @return array
     */
    public function getValidConfirmedOrders()
    {
        $result = [];
        foreach ($this->getOrder() as $key => $order) {
            foreach ($this->getConfirmedStatuses() as $status) {
                if ($order['valid'] === true) {
                    unset($order['valid']);
                    $result[$key . $status] = $order;
                    $result[$key . $status]['status'] = $status;
                }
            }
        }

        return $result;
    }

    public function getUnproceedStatus()
    {
        $result = [];
        foreach ($this->getOrder() as $key => $order) {
            foreach (['new', 'paid', 'overpaid', 'refund', 'suspected', 'some_status'] as $status) {
                unset($order['valid']);
                $result[$key . $status] = $order;
                $result[$key . $status]['status'] = $status;
            }
        }

        return $result;
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getCanceledOrders
     */
    public function testOrderCancellation($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->order->expects(static::once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order::STATE_CANCELED)
            ->willReturnSelf();

        $this->order->expects(static::once())
            ->method('setStatus')
            ->with(\Magento\Sales\Model\Order::STATE_CANCELED)
            ->willReturnSelf();

        $this->order->expects(static::once())
            ->method('addStatusToHistory')
            ->with(\Magento\Sales\Model\Order::STATE_PROCESSING, __('InPay cancel transaction'))
            ->willReturnSelf();

        $this->orderRepository->expects(static::once())
            ->method('save')
            ->with($this->order)
            ->willReturnSelf();

        $this->orderProcessor->proceedStatus($this->order, $status, '111');
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     */
    private function mockOrder($totalDue, $totalQty, $canInvoice, $emailSend)
    {
        $this->mockOrderConfig();
        $this->mockOrderPayment();
        $this->mockOrderInvoice($totalQty);

        /*
         * Should be 'Magento\Sales\Api\Data\OrderInterface' but there problem with status history in API
         */
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->order->expects($this->any())
            ->method('setState')
            ->willReturnSelf();

        $this->order->expects($this->any())
            ->method('setStatus')
            ->willReturnSelf();

        $this->order->expects($this->any())
            ->method('addStatusToHistory')
            ->willReturnSelf();

        $this->order->expects($this->any())
            ->method('getTotalDue')
            ->willReturn($totalDue);

        $this->order->expects($this->any())
            ->method('canInvoice')
            ->willReturn($canInvoice);

        $this->order->expects($this->any())
            ->method('addRelatedObject')
            ->willReturnSelf();

        $this->order->expects($this->any())
            ->method('getEmailSent')
            ->willReturn($emailSend);

        $this->order->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->orderPayment);

        $this->order->expects($this->any())
            ->method('prepareInvoice')
            ->willReturn($this->orderInvoice);
    }

    private function mockOrderConfig()
    {
        $this->orderConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderConfig->expects($this->any())
            ->method('getStateDefaultStatus')
            ->willReturn('canceled');
    }

    private function mockOrderPayment()
    {
        $this->orderPayment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderPayment->expects($this->any())
            ->method('setLastTransId')
            ->willReturnSelf();
    }

    /**
     * @param $totalQty
     */
    private function mockOrderInvoice($totalQty)
    {
        $this->orderInvoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInvoice->expects($this->any())
            ->method('getTotalQty')
            ->willReturn($totalQty);

        $this->orderInvoice->expects($this->any())
            ->method('addComment')
            ->willReturnSelf();

        $this->orderInvoice->expects($this->any())
            ->method('setRequestedCaptureCase')
            ->willReturnSelf();

        $this->orderInvoice->expects($this->any())
            ->method('register')
            ->willReturnSelf();
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getCanceledOrders
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCancelRepositorySaveError($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->orderRepository->expects($this->any())
            ->method('save')
            ->willThrowException(new \Exception('Some error durring save'));

        $this->orderProcessor->proceedStatus($this->order, $status, '111');
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getReceivedOrders
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testReceivedRepositorySaveError($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->orderRepository->expects($this->any())
            ->method('save')
            ->willThrowException(new \Exception('Some error durring save'));

        $this->orderProcessor->proceedStatus($this->order, $status, '111');
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getValidConfirmedOrders
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConfirmedRepositorySaveError($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->orderRepository->expects($this->any())
            ->method('save')
            ->willThrowException(new \Exception('Some error durring save'));

        $this->orderProcessor->proceedStatus($this->order, $status, '111');
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getReceivedOrders
     */
    public function testOrderReceived($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $invoiceCode = '222';
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->order->expects(static::once())
            ->method('addStatusToHistory')
            ->with(
                false,
                __('Payment has been received by InPay. Waiting for confirmation. Transaction id: %1', $invoiceCode)
            )
            ->willReturnSelf();

        $this->orderRepository->expects(static::once())
            ->method('save')
            ->with($this->order)
            ->willReturnSelf();

        $this->orderProcessor->proceedStatus($this->order, $status, $invoiceCode);
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getInvalidConfirmedOrders
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testInvalidOrderConfirmed($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $invoiceCode = '333';
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->orderProcessor->proceedStatus($this->order, $status, $invoiceCode);
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getValidConfirmedOrders
     */
    public function testValidOrderConfirmed($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $invoiceCode = '444';
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);

        $this->order->expects(static::once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->willReturnSelf();

        $this->order->expects(static::once())
            ->method('prepareInvoice');

        $this->orderInvoice->expects(static::once())
            ->method('addComment')
            ->with(__('Invoice automatically by InPay'))
            ->willReturnSelf();

        $this->orderInvoice->expects(static::once())
            ->method('register')
            ->willReturnSelf();

        $this->order->expects(static::once())
            ->method('addStatusToHistory')
            ->with(
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                __('Payment has been confirmed by InPay. Transaction id: %1', $invoiceCode)
            )
            ->willReturnSelf();

        if (!$emailSend) {
            $this->orderSender->expects(static::once())
                ->method('send')
                ->with($this->order)
                ->willReturnSelf();
        }

        $this->orderRepository->expects(static::once())
            ->method('save')
            ->with($this->order)
            ->willReturnSelf();

        $this->orderProcessor->proceedStatus($this->order, $status, $invoiceCode);
    }

    /**
     * @param $totalDue
     * @param $totalQty
     * @param $canInvoice
     * @param $emailSend
     * @param $status
     * @dataProvider getUnproceedStatus
     */
    public function testUnproceedStatus($totalDue, $totalQty, $canInvoice, $emailSend, $status)
    {
        $invoiceCode = '555';
        $this->mockOrder($totalDue, $totalQty, $canInvoice, $emailSend);
        $this->assertEquals(
            false,
            $this->orderProcessor->proceedStatus($this->order, $status, $invoiceCode)
        );
    }

    protected function setUp()
    {
        $this->prepareOrderProcessor();
    }

    private function prepareOrderProcessor()
    {
        $this->orderSender = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderSender->expects($this->any())
            ->method('send')
            ->willReturnSelf();

        $this->orderRepository->expects($this->any())
            ->method('save')
            ->willReturnSelf();

        $this->mockOrderConfig();

        $this->orderProcessor = new \InPay\InPay\Model\OrderProcessor(
            $this->orderSender,
            $this->orderRepository,
            $this->orderConfig
        );
    }

    private function getNeutralStatuses()
    {
        return ['new', 'paid', 'overpaid', 'suspected'];
    }
}
