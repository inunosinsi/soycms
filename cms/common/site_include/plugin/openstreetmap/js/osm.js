//let lat, lng, zoom;

let query = location.search.substring(1);
if(query.length){
	let params = query.split('&');
	for (let i = 0; i < 2; i++ ) {
		if(params[i].indexOf("lat=") >= 0){
			lat = parseFloat(params[i].replace("lat=", ""));
		}else if(params[i].indexOf("lng=") >= 0){
			lng = parseFloat(params[i].replace("lng=", ""));
		}
	}
}

if(!lat) lat = 35.0387532;
if(!lng) lng = 135.7707928;

let map = L.map('map', {
	center: [lat,lng],
	zoom: zoom,
});
let tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	attribution: '© <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
});
tileLayer.addTo(map);
