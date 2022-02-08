(function ($, Drupal, debounce) {
  "use strict";
  
  var numericPagerSelector = '[data-drupal-views-infinite-scroll-numeric-pager]';
  
  $.fn.infiniteScrollPagerInsertView = function ($newView) {
    // Extract the view DOM ID from the view classes.
    var matches = /(js-view-dom-id-\w+)/.exec(this.attr('class'));
    var currentViewId = matches[1].replace('js-view-dom-id-', 'views_dom_id:');
    // Get the existing ajaxViews object.
    var view = Drupal.views.instances[currentViewId];
    var $existingNumericPager = view.$view.find(numericPagerSelector);
    var $newNumericPager = $newView.find(numericPagerSelector);
    
    $existingNumericPager.replaceWith($newNumericPager);
    $.fn.infiniteScrollInsertView.apply(this, [$newView]);
  };
  
})(jQuery, Drupal, Drupal.debounce);
