/**
 * @file
 * Behaviors for the lime_doc theme.
 */

(function($, _, Drupal) {
  Drupal.behaviors.lime_doc = {
    attach: function() {
      // lime_doc JavaScript behaviors goes here.
    }
  };
  Drupal.behaviors.focus_search_bar_when_opened = {
    attach: function (context, settings) {
      $(context).find('button.btn.search-button').on('click touchstart', function () {
        var focus = function(){
          $('form#search-block-form input.form-search').focus();
        };
        setTimeout(focus, 500);
      });
    }
  };
  Drupal.behaviors.expand_content_full_width = {
    attach: function (context, settings) {
      $(context).find('button.btn.expand-main-content').on('click touchstart', function () {
        var mainContent =  $('.node--type-page.node--view-mode-full').find('.bs-region.bs-region--left');
        var mainContentBody =  $('.node--type-page.node--view-mode-full').find('.bs-region.bs-region--left .field--name-body');
        var sideBar =  $('.node--type-page.node--view-mode-full').find('.bs-region.bs-region--right');
        if (mainContent.hasClass( "col-sm-12" ) ) {
          mainContent.removeClass('animated slideInLeft col-sm-12');
          mainContentBody.removeClass('lead');
          sideBar.removeClass('animated fadeInDownBig col-sm-8');
          $(this).removeClass('expanded');
        } else {
          mainContent.addClass('animated slideInLeft col-sm-12');
          mainContentBody.addClass('lead');
          sideBar.addClass('animated fadeInDownBig col-sm-8');
          $(this).addClass('expanded');
        }
      });
    }
  };
})(window.jQuery, window._, window.Drupal);
