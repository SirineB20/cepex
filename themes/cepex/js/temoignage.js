(function ($, Drupal) {

    // Comportement pour Fancybox et le tooltip.
    Drupal.behaviors.testimonialBehavior = {
      attach: function (context, settings) {
        // Initialisation de Fancybox sur les éléments de galerie.
        once('fancybox-init', '[data-fancybox="gallery"]', context).forEach(function (element) {
          Fancybox.bind('[data-fancybox="gallery"]', {
            video: {
              autoStart: true,
              format: 'video/mp4'
            }
          });
        });
  
        // Initialisation du tooltip sur les éléments de description.
        once('tooltip-init', '.desc-succes-storie', context).forEach(function (element) {
          let $desc = $(element);
          let lang = $('html').attr('lang');
  
          $desc.hover(function (event) {
            // Création ou mise à jour du tooltip.
            if ($('.tooltip-popup').length === 0) {
              $('<div class="tooltip-popup"></div>')
                .text($desc.text())
                .appendTo('body');
            }
            else {
              $('.tooltip-popup').text($desc.text());
            }
            let tooltip = $('.tooltip-popup');
            let offsetX = lang === 'ar' ? -200 : 10;
            tooltip.css({
              top: event.pageY + 10,
              left: event.pageX + offsetX
            }).fadeIn(200);
          }, function () {
            $('.tooltip-popup').fadeOut(200);
          });
  
          $desc.mousemove(function (event) {
            let tooltip = $('.tooltip-popup');
            let offsetX = lang === 'ar' ? -200 : 10;
            tooltip.css({
              top: event.pageY + 10,
              left: event.pageX + offsetX
            });
          });
        });
      }
    };
  
    // Comportement pour fixer la hauteur égale des titres et descriptions dans le carousel.
    Drupal.behaviors.equalHeightTestimonials = {
      attach: function (context, settings) {
        once('equal-height', '.carousel-actualite', context).forEach(function (carousel) {
          // Réinitialiser les hauteurs.
          $('.title-succes-storie', carousel).css('height', 'auto');
          $('.desc-succes-storie', carousel).css('height', 'auto');
  
          // Calcul de la hauteur maximale des titres.
          let maxTitleHeight = 0;
          $('.title-succes-storie', carousel).each(function () {
            let titleHeight = $(this).outerHeight();
            if (titleHeight > maxTitleHeight) {
              maxTitleHeight = titleHeight;
            }
          });
          $('.title-succes-storie', carousel).css('height', maxTitleHeight + 'px');
  
          // Calcul de la hauteur maximale des descriptions.
          let maxDescHeight = 0;
          $('.desc-succes-storie', carousel).each(function () {
            let descHeight = $(this).outerHeight();
            if (descHeight > maxDescHeight) {
              maxDescHeight = descHeight;
            }
          });
          $('.desc-succes-storie', carousel).css('height', maxDescHeight + 'px');
        });
  
        // Recalculer la hauteur lors du chargement complet et du redimensionnement.
        $(window).on('load resize', function () {
          Drupal.behaviors.equalHeightTestimonials.attach(context, settings);
        });
      }
    };
  
  })(jQuery, Drupal);
  