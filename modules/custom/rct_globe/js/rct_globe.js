(function (Drupal) {
  Drupal.behaviors.rctGlobe = {
    attach: function (context, settings) {
      // Création de l'élément racine
      var root = am5.Root.new("chartdiv");

      // Définition du thème personnalisé
      var myTheme = am5.Theme.new(root);
      myTheme.rule("Label").setAll({
        fontSize: "1em",
      });
      myTheme.rule("MapPolygonSeries").setAll({
        //fill: am5.color(0xedf7fa),
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
          src: "/dist/assets/images/location.png",
          tooltipText: "{name}",
          centerX: am5.percent(50),
          centerY: am5.percent(70),
        });
        image.events.on("click", function(ev) {
          var dataContext = ev.target.dataItem.dataContext;
          document.getElementById("contact-name").innerText = dataContext.name;
          document.getElementById("contact-address").innerText = dataContext.address;
          document.getElementById("contact-phone").innerText = dataContext.phone;
          document.getElementById("contact-email").innerText = dataContext.email;
        });
        return am5.Bullet.new(root, {
          sprite: image
        });
      });

      // Animation d'apparition du graphique
      chart.appear(1000, 100);
    }
  };
})(Drupal);