<?php
/**
 * Copyright (c) 2017 INPAY S.A.Permission is hereby granted, free of charge, to any person obtaining a copyof this software and associated documentation files (the "Software"), to dealin the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sellcopies of the Software, and to permit persons to whom the Software isfurnished to do so, subject to the following conditions:The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ORIMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * @package InPay_InPay
 */

namespace InPay\InPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_INPAY_API_KEY = 'payment/inpay_gateway/api_key';

    const XML_PATH_INPAY_API_SECRET = 'payment/inpay_gateway/api_secret';

    const XML_PATH_INPAY_TEST = 'payment/inpay_gateway/test';

    const PRODUCTION_URL = 'https://api.inpay.pl';

    const TEST_URL = 'https://apitest.inpay.pl';

    const INVOICE_URL_PATH = '/invoice/create';

    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INPAY_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiSecret()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INPAY_API_SECRET,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getInvoiceUrl()
    {
        return $this->getUrl() . self::INVOICE_URL_PATH;
    }

    public function getUrl()
    {
        return $this->isTestModeEnabled() ? self::TEST_URL : self::PRODUCTION_URL;
    }

    public function isTestModeEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_INPAY_TEST,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCallbackUrl()
    {
        return $this->_urlBuilder->getUrl('inpay/payment/ipn');
    }

    public function getSuccessUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/onepage/success');
    }

    public function getFailUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart');
    }
}
