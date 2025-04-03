/**
 * @file
 * JavaScript pour améliorer l'expérience des filtres du marketplace.
 */
(function ($, Drupal) {
    'use strict';
  
    Drupal.behaviors.marketplaceFilters = {
      attach: function (context, settings) {
        // Améliorer l'accessibilité et l'interaction des filtres
        $('.bouton-filtre', context).once('marketplaceFilters').each(function () {
          $(this).on('click', function() {
            // Toggle active class
            $(this).toggleClass('active-filter');
            
            // Exemple: Si besoin de soumettre automatiquement le formulaire après sélection
            // $('#views-exposed-form-gestion-produit-par-exportateur-page-1').submit();
          });
        });
        
        // Animation focus sur le champ de recherche
        $('#edit-title', context).once('searchAnimation').each(function () {
          $(this).on('focus', function() {
            $(this).closest('.form--inline').addClass('form-focus');
          }).on('blur', function() {
            $(this).closest('.form--inline').removeClass('form-focus');
          });
        });
        
        // Améliorer la sélection des catégories avec select2 si disponible
        if ($.fn.select2) {
          $('#edit-term-node-tid-depth', context).once('select2').select2({
            placeholder: Drupal.t('Sélectionner une catégorie'),
            width: '100%',
            dropdownParent: $('.category-select-container')
          });
        }
      }
    };
  
  })(jQuery, Drupal);