

var map, marker, resultMarker, latForm, lngForm, geocoder;

function initMap() {
    latForm = document.querySelector("#lat");
    lngForm = document.querySelector("#lng");
    var position = {
        "lat": (latForm.value) ? parseFloat(latForm.value) : 35.039171,
        "lng": (lngForm.value) ? parseFloat(lngForm.value) : 135.773011
    };
    map = new google.maps.Map(document.getElementById('map'), {
        center: position,
        zoom: 14
    });

    marker = new google.maps.Marker({
        position: position,
        draggable: true // ドラッグ可能にする
    });
    marker.setMap(map);

    //geocoderの初期化
    geocoder = new google.maps.Geocoder();

    //マーカーの位置変更
    google.maps.event.addListener(marker, 'dragend', function(ev) {
        // イベントの引数evの、プロパティ.latLngが緯度経度。
        latForm.value = ev.latLng.lat();
        lngForm.value = ev.latLng.lng();
    });
}

//住所から検索
if (document.querySelector("#search_by_address")) {
    document.querySelector("#search_by_address").addEventListener("click", function() {
        geocodeAddress(geocoder, map);
    });
}

function geocodeAddress(geocoder, resultsMap) {
	var address = "";
	if(document.querySelector("#address") != null){
		address = document.querySelector("#address").value;
	}else{
		var areaSelectBox = document.querySelector("#area");
		var area = areaSelectBox.selectedIndex;
		if(area > 0){
			address += areaSelectBox[area].text;
		}
		address += document.querySelector("#address1").value;
	}
	
    geocoder.geocode({
        'address': address
    }, function(results, status) {
        if (status === 'OK') {

            //マーカーの削除
            if (marker) marker.setMap(null);
            if (resultMarker) resultMarker.setMap(null);

            resultsMap.setCenter(results[0].geometry.location);
            resultMarker = new google.maps.Marker({
                map: resultsMap,
                position: results[0].geometry.location,
                draggable: true // ドラッグ可能にする
            });

            //lat,lngに登録
            latForm.value = results[0].geometry.location.lat();
            lngForm.value = results[0].geometry.location.lng();

            google.maps.event.addListener(resultMarker, 'dragend', function(ev) {
                // イベントの引数evの、プロパティ.latLngが緯度経度。
                latForm.value = ev.latLng.lat();
                lngForm.value = ev.latLng.lng();
            });

        } else {
            alert("指定の住所の地図情報がありませんでした。");
        }
    });
}
