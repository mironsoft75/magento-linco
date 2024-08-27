/*
* @Author: nguyen
* @Date:   2020-06-30 20:31:53
* @Last Modified by:   nguyen
* @Last Modified time: 2020-06-30 20:32:12
*/


require([
'jquery'
    ], function($){
    $(document).ready(function(){
      $(".close-cookie").on("click", function(){
        $( "#btn-cookie-allow" ).trigger( "click" );
      });
      // toggle search screen large
      $(".toggle-slide-search").on('click', function(){
          $( "html" ).toggleClass( "search-opened");
          // $(".search-area").slideToggle();
          $(".search-area-large").toggleClass( "active");    
      });
      $(".close-search-screen-large").on('click', function(){
          $(".toggle-slide-search").trigger( "click" );
      });
    });
    'use strict';
    $('body').append('<div class="menu-overlay"></div>');
    $(".nav-desktop").mouseenter(function() {
        $('body').addClass('menu-open');

        $(".menu-overlay").fadeIn();
    });
    $('.nav-desktop').mouseleave(function() {
        $('body').removeClass('menu-open');
        $(".menu-overlay").hide();
    });
    $(".menu-overlay").mouseenter(function() {
        $(this).hide();
    });
});

