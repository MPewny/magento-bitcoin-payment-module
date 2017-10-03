<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class ResponseCodeValidator extends AbstractValidator
{
    const SUCCESS = 'success';

    const MESSAGE = 'message';

    const REDIRECT_URL = 'redirectUrl';

    const INVOICE_CODE = 'invoiceCode';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * ResponseCodeValidator constructor.
     * @param \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultInterfaceFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultInterfaceFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($resultInterfaceFactory);
        $this->logger = $logger;
    }

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Response does not exist'));
        }

        $this->logger->debug(json_encode([
            'response' => $validationSubject['response'],
            'message' => 'response on code validator'
        ]));

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        }

        return $this->createResult(
            false,
            [__('Gateway rejected the transaction.')]
        );
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        if (!isset($response[0])) {
            return false;
        }
        $response = json_decode($response[0], true);

        return isset(
            $response[self::SUCCESS],
            $response[self::MESSAGE],
            $response[self::REDIRECT_URL],
            $response[self::INVOICE_CODE]
        )
        && $response[self::SUCCESS] == true;
    }
}
