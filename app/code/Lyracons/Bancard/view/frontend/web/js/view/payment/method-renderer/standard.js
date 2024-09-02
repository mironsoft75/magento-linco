define([
    'Magento_Checkout/js/view/payment/default',
    'mage/url',
], function (Component, url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Lyracons_Bancard/payment/standard'
        },
        redirectAfterPlaceOrder: false,

        context: function () {
            return this;
        },

        getCode: function () {
            return 'lyracons_bancard';
        },

        afterPlaceOrder: function () {
            window.location.replace(url.build('bancard/gateway/redirect'));
        },

        isActive: function () {
            return true;
        }
    });
});