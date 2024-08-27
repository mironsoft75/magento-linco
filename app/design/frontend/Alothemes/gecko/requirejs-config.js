/*
* @Author: nguyen
* @Date:   2019-06-03 14:48:52
* @Last Modified by:   nguyen
* @Last Modified time: 2020-06-30 20:31:19
*/
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'js/mixins/cart-updated': true
            },
            'Magento_ReCaptchaFrontendUi/js/reCaptcha': {
                'js/mixins/reCaptcha-mixin': true
            }
        }
    },
    map: {
		'*': {
			'gecko': 'js/gecko',
		},
	},


    /*paths: {
        'bootstrapbundle'          : 'js/bootstrap.bundle.min',
        
    },*/




	shim: {
        'gecko': {
            deps: ['jquery']
        },
	}
};



