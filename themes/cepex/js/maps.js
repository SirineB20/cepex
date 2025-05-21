(function (Drupal) {
  Drupal.behaviors.rctGlobe = {
    attach: function (context, settings) {
      // S'assurer que l'élément existe et que nous ne l'initialisons qu'une fois
      var chartDiv = document.getElementById("chartdiv");
      if (!chartDiv || chartDiv.hasAttribute("data-amcharts-initialized")) {
        return;
      }
      
      // Marquer l'élément comme initialisé
      chartDiv.setAttribute("data-amcharts-initialized", "true");
      
      // Création de l'élément racine
      var root = am5.Root.new("chartdiv");

      // Définition du thème personnalisé
      var myTheme = am5.Theme.new(root);
      myTheme.rule("Label").setAll({
        fontSize: "1em",
      });

      // Application de tous les thèmes en un seul appel
      root.setThemes([
        am5themes_Animated.new(root),
        am5themes_Responsive.new(root),
        myTheme
      ]);

      // Création du graphique de carte
      var chart = root.container.children.push(am5map.MapChart.new(root, {
        panX: "rotateX",
        panY: "rotateY",
        projection: am5map.geoOrthographic(),
        minZoomLevel: 1,
        maxZoomLevel: 1,
        paddingBottom: 0,
        paddingTop: 0
      }));

      // Création des séries de polygones pour les pays
      var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
        geoJSON: am5geodata_worldLow
      }));
      polygonSeries.mapPolygons.template.setAll({
        tooltipText: "{name}",
        toggleKey: "active",
        interactive: true
      });
      polygonSeries.mapPolygons.template.states.create("hover", {
        fill: root.interfaceColors.get("primaryButtonHover")
      });
      polygonSeries.mapPolygons.template.states.create("active", {
        fill: root.interfaceColors.get("primaryButtonHover")
      });

      // Création de la série pour l'arrière-plan (blanc)
      var backgroundSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {}));
      backgroundSeries.mapPolygons.template.setAll({
        fill: am5.color(0xffffff),
        fillOpacity: 1,
        strokeOpacity: 0.5
      });
      backgroundSeries.data.push({
        geometry: am5map.getGeoRectangle(90, 180, -90, -180)
      });

      // Création de la série pour les océans (en rouge)
      var oceanSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
        geoJSON: am5geodata_worldLow,
        exclude: ["AQ"] // Exclure l'Antarctique
      }));
      oceanSeries.mapPolygons.template.setAll({
        fill: am5.color(0xcb043d), // Couleur pour la mer
        fillOpacity: 1,
      });

      // Gestion des événements pour la sélection des pays
      var previousPolygon;
      polygonSeries.mapPolygons.template.on("active", function (active, target) {
        if (previousPolygon && previousPolygon != target) {
          previousPolygon.set("active", false);
        }
        if (target.get("active")) {
          var centroid = target.geoCentroid();
          if (centroid) {
            chart.animate({ key: "rotationX", to: -centroid.longitude, duration: 1500, easing: am5.ease.inOut(am5.ease.cubic) });
            chart.animate({ key: "rotationY", to: -centroid.latitude, duration: 1500, easing: am5.ease.inOut(am5.ease.cubic) });
          }
        }
        previousPolygon = target;
      });

      // Collecter les données des RCT depuis les éléments DOM
      var rctData = [];
      var rctElements = context.querySelectorAll('.hello-pays');
      rctElements.forEach(function(element) {
        var data = element.textContent.split('|');
        if (data.length === 3) {
          var name = data[0].trim();
          var longitude = parseFloat(data[1].trim());
          var latitude = parseFloat(data[2].trim());
          
          // Vérifier la validité des coordonnées
          if (!isNaN(longitude) && !isNaN(latitude)) {
            var detailsElement = document.getElementById('details-' + name.toLowerCase().replace(/\s+/g, '-'));
            var address = detailsElement ? detailsElement.querySelector('.boxadr span').textContent : '';
            var phone = detailsElement ? detailsElement.querySelector('.boxadr:nth-child(3) span:last-child').textContent : '';
            var email = detailsElement ? detailsElement.querySelector('.boxadr:last-child span:last-child').textContent : '';
            
            rctData.push({
              name: name,
              longitude: longitude,
              latitude: latitude,
              address: address,
              phone: phone,
              email: email
            });
          }
        }
      });

      // Ajout des données au drupalSettings si présentes
      if (rctData.length > 0) {
        drupalSettings.rctGlobe = drupalSettings.rctGlobe || {};
        drupalSettings.rctGlobe.data = rctData;
      }

      // Création des points pour les marqueurs
      var pointSeries = chart.series.push(am5map.MapPointSeries.new(root, {
        latitudeField: "latitude",
        longitudeField: "longitude"
      }));
      
      if (drupalSettings.rctGlobe && drupalSettings.rctGlobe.data) {
        pointSeries.data.setAll(drupalSettings.rctGlobe.data);
      }

      pointSeries.bullets.push(function() {
        var image = am5.Picture.new(root, {
          width: 30,
          height: 30,
          src: "D:\PFE\Drupal\cepex\themes\cepex\css\images\image-pin.png",
          tooltipText: "{name}",
          centerX: am5.percent(50),
          centerY: am5.percent(70),
        });
        
        image.events.on("click", function(ev) {
          var dataContext = ev.target.dataItem.dataContext;
          var countryId = dataContext.name.toLowerCase().replace(/\s+/g, '-');
          
          // Masquer tous les détails et afficher celui qui correspond au point cliqué
          document.querySelectorAll('.maps-details').forEach(function(el) {
            el.style.display = 'none';
          });
          
          var detailsElement = document.getElementById('details-' + countryId);
          if (detailsElement) {
            detailsElement.style.display = 'block';
          }
        });
        
        return am5.Bullet.new(root, {
          sprite: image
        });
      });

      // Animation d'apparition du graphique
      chart.appear(1000, 100);

      // Fonction pour mettre à jour la carte lorsque de nouveaux RCT sont ajoutés
      Drupal.behaviors.rctGlobe.updateMap = function(newData) {
        if (pointSeries && newData) {
          pointSeries.data.clear();
          pointSeries.data.setAll(newData);
        }
      };
    }
  };
})(Drupal);