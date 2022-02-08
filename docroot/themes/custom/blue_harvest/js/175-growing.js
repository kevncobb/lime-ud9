/**
 * @file
 * Behaviors for the blue_harvest theme.
 */

(function($, _, Drupal) {

  Drupal.behaviors.one_seven_five_growing_banner = {
    attach: function(context, settings) {
      $(context).find('.pillars-custom').each(function() {

        if ($(window).width() < 980) {
          $("header.navbar").show();
        } else {
          $("header.navbar").hide();
        }
        var $homePillars = $(".js-home-pillars"),
          $homePillarNav,
          $homePillarContent,
          $pillarSection,
          $pillarTitleLink,
          $pageContent,
          scrollPos,
          opacityRate,
          opacityValue,
          transitioned,
          transitionPoint,
          viewportHeight;

        function init() {

          if ($homePillars.length) {

            $pillarSection = $(".js-pillar");
            $pillarTitleLink = $(".js-pillar-title-link");
            $homePillarContent = $(".js-pillar-content");
            $homePillarNav = $(".js-home-pillar-nav");
            $pageContent = $(".page_content");
            transitioned = false;

            updateVH();

            $pillarSection.hover(function() {
              setPillarPosition($(this));
              $pillarSection.removeClass("active");
              $(this).addClass("active");
            });

            $pillarTitleLink.click(function(event) {
              if ($(window).width() < 980) {

                event.preventDefault();

                $(".js-pillar").each(function() {
                  if ($(this).hasClass("open")) {
                    $(this).find(".home_pillar_inner_content").slideToggle("1500", function() {
                      $(this).parent().removeClass("open");
                    });
                  }
                });

                toggleMobilePillar($(this));
              }
            });


            $(window).on("scroll", function() {

              scrollPos = $(window).scrollTop();

              if ($(window).width() >= 980) {

                if (scrollPos <= ($homePillars.outerHeight() * 0.25)) {
                  $("header.navbar").hide();
                  if ($("body").hasClass("full-nav")) {
                    $("body").removeClass("full-nav");
                  }

                } else {

                  if (!$("body").hasClass("full-nav")) {
                    $("header.navbar").fadeIn();
                    $("body").addClass("full-nav");
                  }

                  if (transitioned === false) {
                    $("header.navbar").fadeIn();
                    transitionPoint = scrollPos;
                    opacityRate = 1 / ($(".home_pillars_logo").outerHeight() + $(".home_pillars_sections").outerHeight() + 100);
                    transitioned = true;
                  }

                  opacityValue = 1 - ((scrollPos - transitionPoint) * opacityRate);
                  $homePillarContent.css('opacity', opacityValue);
                }
              }

            });
          }

        }


        function setPillarPosition(pillar) {
          if (pillar.hasClass("pillar_section_1")) {
            $homePillars.attr("data-position", "one");
            //$(".pillars-custom").addClass("column-one-active").removeClass("column-two-active").removeClass("column-three-active");
          } else if (pillar.hasClass("pillar_section_2")) {
            $homePillars.attr("data-position", "two");
            //$(".pillars-custom").addClass("column-two-active").removeClass("column-one-active").removeClass("column-three-active");
          } else if (pillar.hasClass("pillar_section_3")) {
            $homePillars.attr("data-position", "three");
            //$(".pillars-custom").addClass("column-three-active").removeClass("column-two-active").removeClass("column-one-active");
          } else {
            $homePillars.attr("data-position", "one");
          }
        }

        function toggleMobilePillar(pillarLink) {
          var pillarParent = pillarLink.closest(".home_pillar_section");
          var pillarContent = pillarParent.find(".home_pillar_inner_content");

          if (!pillarParent.hasClass("open")) {
            pillarContent.slideToggle('1500', function() {
              pillarParent.addClass("open");
            });
          }
        }

        function updateVH() {
          if (!$("body").hasClass("page-node-3")) {
            viewportHeight = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', viewportHeight + 'px');
          }

        }

        init();
        //updateVH();

        return {};

      });
    }
  };


  Drupal.behaviors.one_seven_five_growing_bar_graph = {
    attach: function(context, settings) {
      $(context).find('.js-bar-graph').each(function() {

        if ($(".js-bar-graph").length) {

          $(".js-bar-graph").each(function(){
            var $barGraph = $(this);
            var barGraphFull = $(this).find(".js-bargraph-fullwidth");
            var valueLabel = $barGraph.find(".bar_graph_value_label").attr("data-value");
            var denominator = $barGraph.find(".bar_graph_denominator").attr("data-value");

            var valueNum = stripValues(valueLabel);
            var denominatorNum = stripValues(denominator);
            var barWidth = barGraphFull.width();
            var percent = valueNum / denominatorNum * 100;

            $(window).on("load", function(){
              applySmall($barGraph, percent, barWidth);
              if ($barGraph[0].getBoundingClientRect().top < $(window).innerHeight() / 1.5){
                applyWidth($barGraph, percent);
              }
            });

            $(window).on("resize", function(){
              applySmall($barGraph, percent, barWidth);
              if ($barGraph[0].getBoundingClientRect().top < $(window).innerHeight() / 1.5){
                applyWidth($barGraph, percent);
              }
            });

            $(window).on("scroll", function(){
              if ($barGraph[0].getBoundingClientRect().top < $(window).innerHeight() / 1.5){
                applyWidth($barGraph, percent);
              }
            });
          });

        }

        function stripValues(valueArg) {
          return parseInt(valueArg.replace(/\$|,/g, ""));
        }

        function applyWidth(element, percent) {
          if(percent > 100) {
            element.find(".bar_graph_value").addClass("evaluated").css("width", "100%");
          } else {
            element.find(".bar_graph_value").addClass("evaluated").css("width", percent + "%");
          }
        }

        function applySmall(element, percent, barWidth) {
          var calculatedWidth = (barWidth * (percent / 100));
          if( calculatedWidth < 190 ) {
            element.find(".bar_graph_value_label").addClass("small_value");
          } else {
            element.find(".bar_graph_value_label").removeClass("small_value");
          }
        }
      });
    }
  };

})(window.jQuery, window._, window.Drupal);
