define(["jquery"], function($) {
    var hideProps = {},
        showProps = {};

    hideProps.height = "hide";
    showProps.height = "show";

    return function(widget) {
        $.widget("mage.collapsible", widget, {
            options: {
                scrollTo: false,
            },

            _create: function() {
                this.storage = $.localStorage;
                this.icons = false;

                if (typeof this.options.icons === "string") {
                    this.options.icons = $.parseJSON(this.options.icons);
                }

                this._processPanels();
                this._processState();
                this._refresh();

                if (
                    this.options.icons.header &&
                    this.options.icons.activeHeader
                ) {
                    this._createIcons();
                    this.icons = true;
                }

                if (this.options.scrollTo) {
                    this.element.on(
                        "dimensionsChanged",
                        function(e) {
                            if (
                                e.target &&
                                e.target.classList.contains("active")
                            ) {
                                this._scrollToTopIfVisible(e.target);
                            }
                        }.bind(this)
                    );
                }

                this._bind("click");
                this._trigger("created");
            },
        });

        return $.mage.collapsible;
    };
});


// sticky-product-bar
    // var clas = false;
    // var scrollAnt=0;
    // window.addEventListener("scroll", function(e) {
    //     scroll = window.scrollY;
    //     //remover clases
    //     if (scroll < 60 && clas && scroll < scrollAnt && scrollAnt < 130 ) {
    //     //  console.log(scroll,130,clas,scroll, scrollAnt);
    //         clas = false;
    //     //   console.log("removeclass");
    //         var elemento = document.getElementsByClassName("product-info-main");
    //         for (var i = 0; i < elemento.length; i++) {
    //             elemento[i].classList.remove("sticky-product-bar");
    //         }
    //     }
    //     //agregar clases
    //     if (scroll > 601 && !clas) {
    //         clas = true;
    // //     console.log("addclass");
    //         var elemento = document.getElementsByClassName("product-info-main");
    //         for (var i = 0; i < elemento.length; i++) {
    //             elemento[i].className += " sticky-product-bar";
    //         }
    //     }


    //     scrollAnt=scroll;

    // //  console.log("this is scroll position  " + scroll);
    // });
