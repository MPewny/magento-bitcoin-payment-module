<?php
/**
 * Copyright (c) 2017 INPAY S.A. * Permission is hereby granted, free of charge, to any person obtaining a copy * of this software and associated documentation files (the "Software"), to deal * in the Software without restriction, including without limitation the rights * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell * copies of the Software, and to permit persons to whom the Software is * furnished to do so, subject to the following conditions: * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE  * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,  * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. *
 * @package InPay_InPay
 */

namespace InPay\InPay\Controller\Payment;

/**
 * Class Ipn
 * @package InPay\InPay\Controller\Payment
 */
class Ipn extends \Magento\Framework\App\Action\Action
{
    const PAYMENT_STATUS_PAID = 0;
    const PAYMENT_STATUS_REFUND = 4;
    const PAYMENT_STATUS_CANCEL = 2;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \InPay\InPay\Helper\Data
     */
    protected $inpayHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \InPay\InPay\Model\OrderProcessor
     */
    protected $orderProcessor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \InPay\InPay\Helper\Data $inpayHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \InPay\InPay\Model\OrderProcessor $orderProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $httpRequest,
        \InPay\InPay\Helper\Data $inpayHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \InPay\InPay\Model\OrderProcessor $orderProcessor
    ) {
        parent::__construct($context);
        $this->httpRequest = $httpRequest;
        $this->inpayHelper = $inpayHelper;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderProcessor = $orderProcessor;
    }

    public function execute()
    {
        $post = $this->httpRequest->getPost();
        $apiHash = $this->httpRequest->getHeader('API-Hash');
        $orderId = $post['orderCode'];

        if (!$this->inpayHelper->isHashValid($post, $apiHash)) {
            $this->logger->debug(json_encode([
                'orderCode' => $orderId,
                'request' => $post,
                'message' => 'hashInvalid'
            ]));
            throw new \Magento\Framework\Exception\LocalizedException(__('Hash is Invalid'));
        }

        $order = $this->getOrder($orderId);

        if ($order === false || !$order->getEntityId()) {
            $this->logger->debug(json_encode([
                'orderCode' => $orderId,
                'request' => $post,
                'message' => __('Order object with this increment Id does not exist')
            ]));
            throw new \Magento\Framework\Exception\LocalizedException(__('Order object does not contain an Id'));
        }

        $this->logger->debug(json_encode([
            'orderCode' => $orderId,
            'request' => $post,
            'message' => 'IPN before proceedStatus'
        ]));

        $this->orderProcessor->proceedStatus($order, $post['status'], $post['invoiceCode']);
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrder($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $orderId)
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();

        return reset($orderList);
    }
}
