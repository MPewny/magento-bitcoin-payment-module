<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package InPay\InPay\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    private $inpayConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param \InPay\InPay\Helper\Config $inpayConfig
     */
    public function __construct(
        Context $context,
        \InPay\InPay\Helper\Config $inpayConfig
    ) {
        parent::__construct($context);
        $this->inpayConfig = $inpayConfig;
    }

    /**
     * @param $post
     * @param $apiHash
     * @return bool
     */
    public function isHashValid($post, $apiHash)
    {
        $secretApiKey = $this->inpayConfig->getApiSecret();
        $query = http_build_query($post);
        $hash = hash_hmac('sha512', $query, $secretApiKey);
        return $apiHash == $hash;
    }
}
