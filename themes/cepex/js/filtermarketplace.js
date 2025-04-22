/**
 * @file
 * JavaScript behaviors for dependent taxonomy filters.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.taxonomyFilterDependency = {
    attach: function (context, settings) {
      // Vérifier que nos paramètres existent
      if (!drupalSettings.cepex || !drupalSettings.cepex.taxonomyFilterDependency) {
        return;
      }
      
      var config = drupalSettings.cepex.taxonomyFilterDependency;
      var $parentFilter = $('#' + config.parentFilterId);
      var $childFilter = $('#' + config.childFilterId);
      
      // Fonction pour filtrer les options du second filtre
      function updateChildFilter() {
        console.log('Mise à jour du filtre enfant');
        var selectedParentId = $parentFilter.val();
        console.log('Parent sélectionné: ' + selectedParentId);
        
        // Si "All" est sélectionné, afficher toutes les options
        if (selectedParentId === 'All') {
          $childFilter.find('option').show();
          return;
        }
        
        // Sinon, filtrer les options
        $childFilter.find('option').each(function() {
          var $option = $(this);
          var optionValue = $option.val();
          
          // Toujours afficher l'option "All"
          if (optionValue === 'All') {
            $option.show();
            return;
          }
          
          // Vérifier si cette option a le parent sélectionné
          if (config.mapping[optionValue] == selectedParentId) {
            $option.show();
          } else {
            $option.hide();
            // Si l'option cachée était sélectionnée, réinitialiser la sélection
            if ($option.is(':selected')) {
              $childFilter.val('All');
            }
          }
        });
      }
      
      // Attacher l'événement au premier filtre
      $parentFilter.once('taxonomy-filter-dependency').on('change', updateChildFilter);
      
      // Exécuter au chargement de la page
      updateChildFilter();
    }
  };
})(jQuery, Drupal, drupalSettings);