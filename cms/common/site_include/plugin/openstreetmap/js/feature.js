// GeoJSON形式で複数個のマーカーを設定する
L.geoJson(features, {
	onEachFeature: function(features, layer) {
		if (features.properties && features.properties.name) {
			layer.bindPopup("<a href=\""+features.properties.url+"\">"+features.properties.name+"</a>");
			layer.on('click', function(e) {
				this.openPopup();
			});
			layer.on('tap', function(e) {
				this.openPopup();
			});
		}
	},

	// アイコンの指定があれば指定したアイコンを設置する
	pointToLayer: function(feature, latlng) {
		if(feature.properties.icon){
			return L.marker(latlng, {icon: feature.properties.icon});
		}else{
			return L.marker(latlng);
		}
	}
}).addTo(map);
