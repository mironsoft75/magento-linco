

var config = {
    map: {
        '*': {
            menuLayout: 'Lyracons_MegaMenu/js/menu-layout',
			jqueryTmpl: "Lyracons_MegaMenu/js/jquery.tmpl",
			lcJqueryUi: "Lyracons_MegaMenu/js/jquery-ui.min",
			categoryChooser: "Lyracons_MegaMenu/js/categorychooser",
			megamenu: 'Lyracons_MegaMenu/js/menu',
			wysiwygEditor: 'Lyracons_MegaMenu/js/wysiwyg-editor',
			lc_googlemap: 'Lyracons_MegaMenu/js/googlemap'
        }
    },
	shim:{
		"Lyracons_MegaMenu/js/menu-layout": ["jqueryTmpl","lcJqueryUi","categoryChooser","wysiwygEditor"],
		"Lyracons_MegaMenu/js/jquery.tmpl": ["jquery"],
		"Lyracons_MegaMenu/js/jquery-ui.min": ["jquery/jquery-ui"],
		"Lyracons_MegaMenu/js/menu": ["jquery"],
		"Lyracons_MegaMenu/js/wysiwyg-editor": ["jquery"],
		"Lyracons_MegaMenu/js/googlemap": ["jquery"]
	}
};
