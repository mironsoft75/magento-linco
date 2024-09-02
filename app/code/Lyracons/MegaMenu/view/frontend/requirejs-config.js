

var config = {
    map: {
        '*': {
            megamenu: 'Lyracons_MegaMenu/js/menu',
			lc_googlemap: 'Lyracons_MegaMenu/js/googlemap',
        }
    },
	shim:{
		"Lyracons_MegaMenu/js/menu": ["jquery"],
		"Lyracons_MegaMenu/js/googlemap": ["jquery"]
	}
};
