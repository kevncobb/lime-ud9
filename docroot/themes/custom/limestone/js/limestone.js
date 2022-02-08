/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.    stickybits("selector", { useStickyClasses: true });
   *
   */
  Drupal.behaviors.front_page_bg_video = {
    attach: function (context) {
      $(window).bind("load", function() {
        var ww = window.innerWidth;
        if (ww > 1023.98) {
          $(context).find('#yt-player').append('<video playsinline autoplay style="width: 100%;" class="ratio-content" loop muted poster="/sites/default/files/styles/d06/public/images/2020-07/curtis_university_2.jpg"> <source class="show-for-medium-up" src="/sites/default/files/2021-04/2021-homepage-video-background_0.mp4" type="video/mp4"> </video>');
        }
      });
    }
  };
  Drupal.behaviors.expand_active_menu = {
    attach: function (context) {
      $(window).bind("load", function () {
        $(context).find('#side-submenu > ul.root-level > li.menu-item--active-trail').each(function () {
          $(this).find("button").attr("aria-expanded", "true");
          $(this).find("ul.is-accordion-submenu").delay(200).attr("aria-hidden", "false").css("display", "block");
        });
      });
    }
  };
  Drupal.behaviors.callout = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('.lc-callout').prepend( "<span class='watermark'>&nbsp;</span>" );
      $(context).find('.lc-callout right').prepend( "<span class='watermark right'>&nbsp;</span>" );
    }
  };
  Drupal.behaviors.flyout = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('.flyout').click(function() {
        $(this).toggleClass('open');
      });
    }
  };
  Drupal.behaviors.trim_next_headline = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      $(context).find('#views_slideshow_cycle_main_hero_slider-block_1 .slideshow-controls .next-headline').prepend( "<span class='watermark'>&nbsp;</span>" );
    }
  };
  Drupal.behaviors.add_class_to_empty_sidemenu_h2 = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      //  .fibonacci-aside nav h2 span:has(a), .sidebar nav h2 span:has(a), .thirds-right nav h2 span:has(a), .large-4 nav h2 span:has(a)
      // $("p:has(img)")
      $(context).find('.sidebar nav h2 span:has(a)').addClass( "has-link" );
      $(context).find('.fibonacci-aside nav h2 span:has(a)').addClass( "has-link" );
      $(context).find('.thirds-right nav h2 span:has(a)').addClass( "has-link" );
      $(context).find('.large-4 nav h2 span:has(a)').addClass( "has-link" );
    }
  };
  Drupal.behaviors.remove_duplicate_courses = {
    attach: function (context, settings) {
      $(document).ready(function() {
        var seen = {};
        $('.block-views-blockcourses-block-1 table.cols-0 tr').each(function() {
          var txt = $(this).children("td").text();
          //console.log(txt)
          // $(this).isEmpty(td) == true )
          if ( (seen[txt]) || $(this).find('td').is(':empty') )  //
            $(this).remove();
          else
            seen[txt] = true;
        });
      });
    }
  };
  Drupal.behaviors.customCKEditorConfig = {
    attach: function (context, settings) {
      if (typeof CKEDITOR !== "undefined") {
        CKEDITOR.dtd.$removeEmpty['i'] = false;
        CKEDITOR.dtd.$removeEmpty['span'] = false;
        CKEDITOR.config.forcePasteAsPlainText = true;
        //console.log(CKEDITOR.dtd);
      }
    }
  };
  Drupal.behaviors.lc_menu = {
    attach: function (context, settings) {
      $(context).find('.lc-mobile-menu-toggle').bind('touchstart click', function (event) {
        //event.stopPropagation();
        //event.preventDefault();
        $(this).toggleClass("expander-hidden");
        if (!Foundation.MediaQuery.atLeast("medium")){
          // workaround for https://github.com/zurb/foundation-sites/issues/10478
          $(".is-dropdown-submenu-parent").removeClass("is-dropdown-submenu-parent");
        }
      });
    }
  };
  // td:has(> a)
  Drupal.behaviors.focus_search_bar_when_opened = {
    attach: function (context, settings) {
      $(context).find('button.button.search-button').on('click touchstart', function () {
        //event.stopPropagation();
        //event.preventDefault();
        var focus = function(){
          $('form#search-block-form input.form-search').focus();
        };
        setTimeout(focus, 500);
      });
    }
  };
  Drupal.behaviors.to_top = {
    attach: function (context, settings) {
      // Execute code once the DOM is ready. $(document).ready() not required within Drupal.behaviors.

      // To Top button appear on scroll
      $(window).bind("scroll", function() {
        if ($(this).scrollTop() > 300) {
          $('#to-top:hidden').stop(true, true).fadeIn();
        } else {
          $('#to-top').stop(true, true).fadeOut();
        }
        if ($(this).scrollTop() > 400) {
          $('.scroll-fade-1').stop(true, true).css("display", "none");
          $('.scroll-fade-in-1:hidden').stop(true, true).fadeIn();
        } else {
          $('.scroll-fade-1:hidden').stop(true, true).fadeIn();
          $('.scroll-fade-in-1').stop(true, true).css("display", "none");
        }
      });
    }
  };
  Drupal.behaviors.open_gallery = {
    attach: function (context, settings) {
      $(context).find('a.open-side-column-gallery').bind('touchstart click', function (event) {
        //event.stopPropagation();
        //event.preventDefault();
        var holdingCell = $(this).parents('.cell');
        var galleryElement = $(holdingCell).siblings('.cell').find('.cover-image > .field-items > .field-item:first-child a.colorbox');
        var galleryLink = $(galleryElement).colorbox();

        galleryLink.eq(0).click();
        return false;
      });
    }
  };

  Drupal.behaviors.masonry_grid = {
    attach: function (context, settings) {
      if ($(".masonry-grid").length > 0 ) {
        $(context).find('.masonry-grid').masonry({
          // set grid-itemSelector so .grid-sizer is not used in layout
          itemSelector: '.grid-item',
          // use element for option
          columnWidth: '.grid-sizer',
          percentPosition: true
        });
      }
    }
  };
  // Hack to prevent duplicate links on embedded images with links in CKeditor   if ($(window).width() < 768) {
  Drupal.behaviors.hide_empty_button_links = {
    attach: function (context, settings) {
      $(context).find('.image-buttons a').filter(function() {
        return !$.trim(this.innerHTML);
      }).remove();
      $(context).find('figure a:empty').remove();
    }
  };
  Drupal.behaviors.move_submenu_to_expandable_area = {
    attach: function (context, settings) {
      if ($(window).width() < 768) {
        $(context).find('#side-submenu').appendTo("#expandable-menu");
      }
    }
  };

  Drupal.behaviors.lc_footer = {
    attach: function (context, settings) {
      if ($(window).width() > 530) {
        if ($(".lc-footer").length > 0) {
          var a = function () {
            window.scrollY;
            for (var e, t, n = l.offsetTop + 100 + l.clientHeight - window.scrollY, s = 0, a = 0; a < i.length; a++)
              t = (e = i[a]).getAttribute("data-speed") / -100,
              n < c && n < o && (s = d ? (n - o) * t : (n - c) * t),
                e.style.transform = "translate(0px," + s + "px)"
          }
            , r = document.querySelector(".lc-footer")
            , i = document.querySelectorAll(".lc-footer-layer")
            , c = window.innerHeight
            , o = r.offsetTop
            , l = document.getElementById("main")
            , d = o < c;
          window.addEventListener("scroll", (function (e) {
              a()
            }
          )),
            a()
        }
      }
    }
  };
  /*
  Drupal.behaviors.fastClick = {
    attach: function (context, settings) {
      $(function() {
        FastClick.attach(document.body);
      });
    }
  };

  Drupal.behaviors.onPageLoad = {
    attach: function (context, settings) {
      $(window).bind("load", function() {
        if ($(window).width() > 767) {
          $(context).find('.main-menu-region nav.show-for-desktop-menu').each(function() {
            $( this ).css("display", "block !important");
          });
        }
      });
    }
  };
 */

  // fsNextButton4346495
})(jQuery, Drupal);


