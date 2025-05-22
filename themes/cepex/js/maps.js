(function (Drupal) {
  Drupal.behaviors.rctGlobe = {
    attach: function (context, settings) {
      const chartDivs = once('rctGlobe', '#chartdiv', context);
      chartDivs.forEach(function(chartDiv) {
        var root = am5.Root.new("chartdiv");
        var myTheme = am5.Theme.new(root);
        myTheme.rule("Label").setAll({ fontSize: "1em" });
        myTheme.rule("MapPolygonSeries").setAll({});
        root.setThemes([am5themes_Animated.new(root), am5themes_Responsive.new(root), myTheme]);
        
        var chart = root.container.children.push(am5map.MapChart.new(root, {
          panX: "rotateX", 
          panY: "rotateY", 
          projection: am5map.geoOrthographic(),
          minZoomLevel: 1, 
          maxZoomLevel: 1, 
          paddingBottom: 0, 
          paddingTop: 0
        }));
        
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
        
        var backgroundSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {}));
        backgroundSeries.mapPolygons.template.setAll({
          fill: am5.color(0xffffff), 
          fillOpacity: 1, 
          strokeOpacity: 0.5 
        });
        backgroundSeries.data.push({ 
          geometry: am5map.getGeoRectangle(90, 180, -90, -180) 
        });
        
        var oceanSeries = chart.series.push(am5map.MapPolygonSeries.new(root, { 
          geoJSON: am5geodata_worldLow, 
          exclude: ["AQ"] 
        }));
        oceanSeries.mapPolygons.template.setAll({
          fill: am5.color(0xcb043d), 
          fillOpacity: 1 
        });
        
        var previousPolygon;
        polygonSeries.mapPolygons.template.on("active", function(active, target) {
          if (previousPolygon && previousPolygon != target) {
            previousPolygon.set("active", false);
          }
          if (target.get("active")) {
            var centroid = target.geoCentroid();
            if (centroid) {
              chart.animate({ 
                key: "rotationX", 
                to: -centroid.longitude, 
                duration: 1500, 
                easing: am5.ease.inOut(am5.ease.cubic) 
              });
              chart.animate({ 
                key: "rotationY", 
                to: -centroid.latitude, 
                duration: 1500, 
                easing: am5.ease.inOut(am5.ease.cubic) 
              });
            }
          }
          previousPolygon = target;
        });
        
        // Collecte des données de points depuis les éléments hello-pays
        var pointDataArray = [];
        console.log("Recherche des éléments .hello-pays...");
        
        $('.hello-pays', context).each(function(index, element) {
          var divContent = $(element).text();
          console.log("Contenu trouvé:", divContent);
          
          var values = divContent.split('|');
          if (values.length === 3) {
            var name = values[0].trim();
            var longitude = parseFloat(values[1].trim());
            var latitude = parseFloat(values[2].trim());
            
            console.log("Données parsées:", { name, longitude, latitude });
            
            if (!isNaN(longitude) && !isNaN(latitude)) {
              var countryId = name.toLowerCase().replace(/\s+/g, '-');
              pointDataArray.push({
                name: name, 
                longitude: longitude,
                latitude: latitude, 
                countryId: countryId 
              });
              console.log("Point ajouté:", { name, countryId, longitude, latitude });
            } else {
              console.error("Coordonnées invalides pour:", name);
            }
          } else {
            console.error("Format de données incorrect:", divContent);
          }
        });
        
        console.log("Nombre total de points:", pointDataArray.length);
        console.log("Données complètes:", pointDataArray);
        
        // Création de la série de points
        var pointSeries = chart.series.push(am5map.MapPointSeries.new(root, {
          latitudeField: "latitude", 
          longitudeField: "longitude" 
        }));
        
        pointSeries.data.setAll(pointDataArray);
        
        // Configuration des marqueurs
        pointSeries.bullets.push(function() {
          var circle = am5.Circle.new(root, {
            radius: 7, 
            fill: am5.color(0xffffff),
            tooltipText: "{name}",
            strokeWidth: 2,
            stroke: am5.color(0xcb043d)
          });
          
          circle.events.on("click", function(ev) {
            var dataContext = ev.target.dataItem.dataContext;
            var countryId = dataContext.countryId;
            
            console.log("Marqueur cliqué:", dataContext.name);
            console.log("ID recherché:", 'details-' + countryId);
            
            var detailsElement = document.getElementById('details-' + countryId);
            if (detailsElement) {
              // Fermer tous les autres détails
              document.querySelectorAll('.maps-details').forEach(function(el) {
                el.classList.remove("contenu-pays-open");
              });
              // Ouvrir le détail sélectionné
              detailsElement.classList.add("contenu-pays-open");
              console.log("Détail ouvert pour:", countryId);
            } else {
              console.error("Aucun élément de détail trouvé pour:", countryId);
              // Debug: lister tous les éléments maps-details disponibles
              var allDetails = document.querySelectorAll('.maps-details');
              console.log("Éléments maps-details disponibles:");
              allDetails.forEach(function(el) {
                console.log("- ID:", el.id);
              });
            }
          });
          
          return am5.Bullet.new(root, { sprite: circle });
        });
        
        // Animation de rotation
        chart.animate({
          key: "rotationX",
          from: 0, 
          to: 360,
          duration: 60000,
          loops: Infinity 
        });
        
        chart.appear(1000, 100);
      });
    }
  };
})(Drupal);