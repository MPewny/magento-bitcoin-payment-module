/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'inpay_gateway',
                component: 'InPay_InPay/js/view/payment/method-renderer/inpay_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
