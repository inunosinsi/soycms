AsyncReserveCalendar = {
	operationUrl : "",
	isInvalid : false,
	add : function(ele, scheduleId){
		console.log(ele);
		AsyncReserveCalendar.operationUrl = document.querySelector("#reserve_calendar_cart_url").value;
		
		if (AsyncReserveCalendar.isInvalid) {
			setTimeout(function(){
				AsyncReserveCalendar.isInvalid = false;
			}, 1000);
			return false;
		}
	
		//連打を禁止する
		AsyncReserveCalendar.isInvalid = true;
		
		//androidの標準ブラウザはリダイレクトさせる
		var ua = window.navigator.userAgent;
		if (/Android/.test(ua) && /Linux; U;/.test(ua) && !/Chrome/.test(ua)) {
			AsyncReserveCalendar.doGet(scheduleId);
		}else{
			
			//XMLHttpRequestが使用できない環境の場合はリダイレクト
			if (!window.XMLHttpRequest) {
				AsyncReserveCalendar.doGet(scheduleId);
			} else {
				xhr = new XMLHttpRequest();
				
				//addEventListenerが使用できない環境の場合はリダイレクト
				if(!xhr.addEventListener) {
					AsyncReserveCalendar.doGet(scheduleId);
				} else {
					
					xhr.open("GET", AsyncReserveCalendar.operationUrl + "?a=add&schId=" + scheduleId);
					xhr.send(null);
					
					xhr.addEventListener("load", function(){

						//HTTPステータスが200でカートに商品が入ったことを確認
						if(xhr.status == 200 && xhr.readyState == 4){
							ele.onclick="disabled=true";
							
							//クラスを追加
							ele.classList.add("added");
						} else if(xhr.status == 204){
						
							alert("失敗しました");
						} else {
							
							alert("失敗しました");
						}
						
						//処理が確実に終わったことを確認してからフラグを解除
						AsyncReserveCalendar.isInvalid = false;
					});
					
					//タイムアウトした時対策
					xhr.addEventListener("timeout", function(){
						AsyncReserveCalendar.doGet(scheduleId);
					});
				}
			}
		}
	},
	doGet : function(scheduleId){
		location.href = AsyncReserveCalendar.operationUrl + "?a=add&schId=" + scheduleId;
	},
};
