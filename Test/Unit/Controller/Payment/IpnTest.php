<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Test\Unit\Controller\Payment;

use InPay\InPay\Helper\Data;
use InPay\InPay\Model\OrderProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class IpnTest
 * @package InPay\InPay\Test\Unit\Controller\Payment
 */
class IpnTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpRequest;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inpayHelper;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var OrderProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderProcessor;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var OrderSearchResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderSearchResult;

    protected function setup()
    {
        $this->mockContext();
        $this->mockLogger();
        $this->mockOrderRepository();
    }

    private function mockContext()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockLogger()
    {
        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockOrderRepository()
    {
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();

        $this->searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteria);

        $this->orderSearchResult = $this->getMockBuilder(OrderSearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepository->expects($this->any())
            ->method('getList')
            ->with($this->searchCriteria)
            ->willReturn($this->orderSearchResult);

        $this->order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderSearchResult->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->order]);

        $this->orderProcessor = $this->getMockBuilder(OrderProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testInvalidHash()
    {
        $orderId = 1;
        $post = [
            'orderCode' => '1',
            'invoiceCode' => '1',
            'status' => 'received'
        ];
        $apiHash = '1';

        $this->mockOrder($orderId);
        $this->mockHttpRequest($post, $apiHash);
        $this->mockInPayHelper(false);
        $ipnController = new \InPay\InPay\Controller\Payment\Ipn(
            $this->context,
            $this->httpRequest,
            $this->inpayHelper,
            $this->logger,
            $this->orderRepository,
            $this->searchCriteriaBuilder,
            $this->orderProcessor
        );
        $ipnController->execute();
    }

    private function mockOrder($oderId)
    {
        $this->order->expects($this->any())
            ->method('getEntityId')
            ->willReturn($oderId);
    }

    private function mockHttpRequest($post, $header)
    {
        $this->httpRequest = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpRequest->expects($this->any())
            ->method('getPost')
            ->willReturn($post);

        $this->httpRequest->expects($this->any())
            ->method('getHeader')
            ->willReturn($header);
    }

    private function mockInPayHelper($isValidHash)
    {
        $this->inpayHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inpayHelper->expects($this->any())
            ->method('isHashValid')
            ->willReturn($isValidHash);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testNonExistingOrder()
    {
        $orderId = false;
        $post = [
            'orderCode' => '1',
            'invoiceCode' => '1',
            'status' => 'received'
        ];
        $apiHash = '1';

        $this->mockOrder($orderId);
        $this->mockHttpRequest($post, $apiHash);
        $this->mockInPayHelper(true);
        $ipnController = new \InPay\InPay\Controller\Payment\Ipn(
            $this->context,
            $this->httpRequest,
            $this->inpayHelper,
            $this->logger,
            $this->orderRepository,
            $this->searchCriteriaBuilder,
            $this->orderProcessor
        );

        $ipnController->execute();
    }

    public function testExecute()
    {
        $orderId = 1;
        $post = [
            'orderCode' => '1',
            'invoiceCode' => '1',
            'status' => 'received'
        ];
        $apiHash = '1';

        $this->mockOrder($orderId);
        $this->mockHttpRequest($post, $apiHash);
        $this->mockInPayHelper(true);

        $this->orderProcessor->expects(static::once())
            ->method('proceedStatus')
            ->willReturn(false);

        $ipnController = new \InPay\InPay\Controller\Payment\Ipn(
            $this->context,
            $this->httpRequest,
            $this->inpayHelper,
            $this->logger,
            $this->orderRepository,
            $this->searchCriteriaBuilder,
            $this->orderProcessor
        );

        $ipnController->execute();
    }
}
