AsyncCartButton = {
	operationUrl : "",
	isInvalid : false,
	addItem : function(ele, itemId, price){
		if (AsyncCartButton.isInvalid) {
			setTimeout(function(){
				AsyncCartButton.isInvalid = false;
			}, 1000);
			return false;
		}
	
		//連打を禁止する
		AsyncCartButton.isInvalid = true;
		
		//androidの標準ブラウザはリダイレクトさせる
		var ua = window.navigator.userAgent;
		if (/Android/.test(ua) && /Linux; U;/.test(ua) && !/Chrome/.test(ua)) {
			AsyncCartButton.doSubmit(itemId);
			
		}else{
			
			//XMLHttpRequestが使用できない環境の場合はリダイレクト
			if (!window.XMLHttpRequest) {
				AsyncCartButton.doSubmit(itemId);
			} else {
				xhr = new XMLHttpRequest();
				
				//addEventListenerが使用できない環境の場合はリダイレクト
				if(!xhr.addEventListener) {
					AsyncCartButton.doSubmit(itemId);
				} else {
					var cnt = 1;
					if(document.querySelector){
						var cntObject = document.querySelector("#soyshop_async_count_" + itemId);
						if(cntObject){
							switch(cntObject.tagName){
								case "INPUT":
									//文字列を入れた場合は処理を止める
									if(isNaN(cntObject.value) || cntObject.value == "") {
										AsyncCartButton.isInvalid = false;
										return false;
									}else{
										cnt = parseInt(cntObject.value);
									}
									break;
								case "SELECT":
									if(cntObject.options.length > 0 && cntObject.options[cntObject.selectedIndex]){
										cnt = parseInt(cntObject.options[cntObject.selectedIndex].value);
									}
									break;
							}
						}		
						
						if (cnt === 0) cnt = 1;
					}
					
					var url = AsyncCartButton.operationUrl + "?a=add&count=" + cnt + "&item=" + itemId + "&mode=ajax";
					
					//helperに値があればそれを取得する
					var helper = document.querySelector("#standard_price_helper_" + itemId);
					if(helper){
						price = parseInt(helper.value);
					}
					
					var param = "";
					
					//商品規格がある場合はPOSTの内容も送信したい
					var sels = document.querySelectorAll('select option:checked');
					if(sels.length){
						
						for (var i = 0; i < sels.length; i++){
							var parent = sels[i].parentElement;
							if(parent.name.indexOf("Standard") === 0 && parent.id.indexOf("item_standard_") === 0){
								var key = parent.name.replace("Standard[", "");
								key = key.replace("]", "");
								if(parent.id == "item_standard_" + key + "_" + itemId){
									if(param.length) param += "&";
									val = sels[i].innerHTML.trim();
									//POSTで+は許可されていないので、POST時に一度変換して、ここで再度戻す
									if(val.indexOf("+")) val = val.replace("+", "itm_std_plus");
									
									//POSTで&は許可されていないので、POST時に一度変換して、ここで再度戻す
									if(val.indexOf("&amp;")) val = val.replace("&amp;", "itm_std_and");
									if(val.indexOf("&")) val = val.replace("&", "itm_std_and");
									
									param += parent.name + "=" + val;
								}
							}
						}
					}
					
					xhr.open("POST",url);
					xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					
					xhr.send(param);
					
					xhr.addEventListener("load", function(){
						
						var text;
						
						//HTTPステータスが200でカートに商品が入ったことを確認
						if(xhr.status == 200 && xhr.readyState == 4){
							
							//現在表示されているカートの商品合計を更新
							var countSpan = document.querySelector("#soyshop_cart_item_count");
							var itemCnt = parseInt(countSpan.innerHTML) + cnt;
							if(countSpan){
								countSpan.innerHTML = itemCnt;
							}
							
							//現在表示されているカートの商品小計を更新				
							var subTotalSpan = document.querySelector("#soyshop_cart_sub_total");
							var subTotal = subTotalSpan.innerHTML;
							if(subTotal.indexOf(",")){
								subTotal = subTotal.replace(",", "");
							}
							subTotal = AsyncCartButton.number_format(parseInt(subTotal) + parseInt(price * cnt));
			
							if(subTotalSpan){
								subTotalSpan.innerHTML = subTotal;
							}
							
							text = "商品をカゴに入れました<br>";
							text += "商品件数: " + itemCnt + "点<br>";
							text += "小計: " + subTotal + "円";
							
						//在庫数なし
						} else if(xhr.status == 204){
							text = "在庫切れ商品です。";
						//カートに入れるエラー
						} else {
							text = "カートに入れる処理に失敗しました。";
						}
						
						if(text){
							
							var parent = ele.parentNode;
							parent.style.position = "relative";
							
							//吹き出しを表示
							var div = document.createElement("div");
							div.className = "async_tooltip";
							
							div.innerHTML = text;
							
							div.style.position = "absolute";
							div.style.textAlign = "center";
							
							//枠
							div.style.border = "1px solid #000000";
							div.style.borderRadius = "5px";
							div.style.padding = "3px";
							div.style.backgroundColor = "#FFFFFF";
							
							var px = ele.offsetLeft;
							var py = ele.offsetTop;
							
							if(ele.tagName == "A"){
								//子ノードの大きさを調べる
								children = ele.childNodes;
								for(var i = 0; i < children.length; i++){
									if(children[i].tagName == "IMG"){
										var childW = ele.childNodes[i].offsetWidth;
										if(childW - 130 > 0) {
											px += ((childW - 130) / 2);
										}
										py -= (children[i].offsetHeight + 55);
										break;
									}
								}	
							}else{
								py -= ele.offsetHeight;
							}
							
							//pyの調整
							if(xhr.status == 204) py += 35;
							
							div.style.left = px + "px";
							div.style.top = py + "px";
											
							//吹き出しの表示
							parent.insertBefore(div, ele);
							setTimeout(function(){
								parent.removeChild(div);
							}, 5000);
							
							//処理が確実に終わったことを確認してからフラグを解除
							AsyncCartButton.isInvalid = false;
						}
					});
					
					//タイムアウトした時対策
					xhr.addEventListener("timeout", function(){
						AsyncCartButton.doSubmit(itemId);
					});
				}
			}
		}	
	},
	number_format : function(num){
  		return num.toString().replace(/([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,');
	},
	doSubmit : function(itemId){
		document.getElementById("soyshop_async_cart_" + itemId).submit();
	},
};

//商品規格プラグインと併用している時
(function(){
	
	//querySelectorとXMLHttpRequestが使えない環境は強制的に止める
	if(!document.querySelector || !window.XMLHttpRequest) return;
	
	var ids = [];
	var hdns = document.querySelectorAll('input[type="hidden"]');
	for (var i = 0; i< hdns.length; i++){
		if(hdns[i].id.indexOf("standard_price_helper_") === 0){
			ids.push(parseInt(hdns[i].id.replace("standard_price_helper_", "")));
		}
	}
		
	var sels = document.querySelectorAll('select');
	if(sels.length && ids.length > 0){
		for (i = 0; i < ids.length; i++){
			var id = ids[i];
			var priceHelper = document.querySelector("#standard_price_helper_" + id);
			if(priceHelper){
				for (var j = 0; j < sels.length; j++){
					if(sels[j].id.search('item_standard_(.*)_' + id) === 0 && sels[j].name.indexOf("Standard") === 0){
						
						//処理を強制的に止める
						if(!sels[j].addEventListener) return;
						
						sels[j].addEventListener("change", function(){
							
							var param = "";
							var chks = document.querySelectorAll('select option:checked');
							if(chks.length){
								for (var k = 0; k < chks.length; k++){
									//一応再度IDチェック
									if(chks[k].parentElement.id.search('item_standard_(.*)_' + id) === 0 && chks[k].parentElement.name.indexOf("Standard") === 0){
										if(param.length) param += "&";
										param += chks[k].parentElement.name + "=" + chks[k].innerHTML.trim();
									}
								}
							}
							
							xhr = new XMLHttpRequest();
														
							xhr.open("POST",location.pathname + "?async_cart_button=" + id);
							xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
							xhr.send(param);
			
							xhr.addEventListener("load", function(){
								var resp = xhr.response;
								if(resp){
									var res = JSON.parse(resp);
									if(res.price >= 0){
										priceHelper.value = res.price;
									}
								}
							});
						});
					}		
				}
			}
		}
	}
})();