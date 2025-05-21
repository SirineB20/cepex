/**
 * @file
 * cepex behaviors.
 */

(function ($, Drupal) {
  // Assurez-vous que cette fonction s'exécute après le chargement complet de la page
  Drupal.behaviors.cepexInit = {
    attach: function (context, settings) {
      // S'assurer que cette fonction ne s'exécute qu'une fois
      $('body', context).once('cepexInit').each(function () {
        console.log('Initialisation de la carte...');
        
        // Vérifier si les données des bureaux sont présentes dans les éléments HTML
        setTimeout(function() {
          var bureauCount = $('.hello-pays').length;
          console.log('Nombre de bureaux trouvés:', bureauCount);
          
          if (bureauCount > 0) {
            // Les éléments sont présents, on peut extraire les données
            var bureauData = [];
            $('.hello-pays').each(function() {
              var info = $(this).text().split('|');
              if (info.length === 3) {
                bureauData.push({
                  name: info[0].trim(),
                  longitude: parseFloat(info[1].trim()),
                  latitude: parseFloat(info[2].trim())
                });
              }
            });
            
            // Stocker les données dans drupalSettings pour que maps.js puisse les utiliser
            if (!drupalSettings.rctGlobe) {
              drupalSettings.rctGlobe = {};
            }
            drupalSettings.rctGlobe.data = bureauData;
            console.log('Données des bureaux ajoutées à drupalSettings:', bureauData);
          } else {
            console.log('Aucun bureau trouvé dans le HTML.');
          }
        }, 500); // Attendre un peu pour s'assurer que tout le DOM est chargé
      });
    }
  };
})(jQuery, Drupal);
