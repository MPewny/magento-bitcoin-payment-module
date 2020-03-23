# InPay Bitcoin Payment Gateway

This extension allows Your customers to pay for orders by InPay

## Features

* InPay payment gateway is added to checkout 
* Customers can pay for order by InPay
* After payment confirmation by InPay invoice is created


## Installation
* Download extension.
* Unzip archive.
* Upload extension files on your server to:

```
{Magento root}
└── app/
    └── code    
```
* In Magento root folder run commands:  

```
$ php bin/magento setup:upgrade
```

```
$ php bin/magento module:enable InPay_InPay
```

* In Magento Admin Panel go to 

```
Stores -> Configuration -> Sales -> "Payment Methods" -> "InPay Gateway"
```

* Set values for API key and API secret and change Enabled to Yes


* Enjoy!  



## License

Copyright (c) 2017 INPAY S.A.
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
