define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'lyracons_bancard',
            component: 'Lyracons_Bancard/js/view/payment/method-renderer/standard'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
