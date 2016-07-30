AsyncCartButton = {
	operationUrl : "",
	isInvalid : false,
	addItem : function(ele, itemId, price){
		if (AsyncCartButton.isInvalid) return false;
	
		//連打を禁止する
		AsyncCartButton.isInvalid = true;
		
		var cnt = 1;
		var cntSelect = document.querySelector("#soyshop_async_count_" + itemId);
		if(cntSelect){
			cnt = parseInt(cntSelect.options[cntSelect.selectedIndex].value);
			if (cnt === 0) cnt = 1;
		}
		
		var url = this.operationUrl + "?a=add&count=" + cnt + "&item=" + itemId;
		
		//XMLHttpRequestが使用できない環境の場合はリダイレクト
		if(!window.XMLHttpRequest) {
			location.href = url;
		}
		
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
				if(sels[i].parentElement.name.indexOf("Standard") === 0){
					if(param.length) param += "&";
					param += sels[i].parentElement.name + "=" + sels[i].innerHTML.trim();
				}
			}
		}
		
		xhr = new XMLHttpRequest();
		xhr.open("POST",url);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		xhr.send(param);
		
		xhr.addEventListener("load", function(){
			
			var text;
			
			//HTTPステータスが200でカートに商品が入ったことを確認
			if(xhr.status == 200){
				
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
			location.href = url;
		});
	},
	number_format : function(num){
  		return num.toString().replace(/([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,');
	}
};

//商品規格プラグインと併用している時
(function(){
	var ids = [];
	var hdns = document.querySelectorAll('input[type="hidden"]');
	for (var i = 0; i< hdns.length; i++){
		if(hdns[i].id.indexOf("standard_price_helper_") === 0){
			ids.push(parseInt(hdns[i].id.replace("standard_price_helper_", "")));
		}
	}
	
	if(ids.length > 0){
		ids.forEach(function(id){
			var priceHelper = document.querySelector("#standard_price_helper_" + id);
			if(priceHelper){
				var sels = document.querySelectorAll('select');
				if(sels.length){
					for (var j = 0; j < sels.length; j++){
						if(sels[j].name.indexOf("Standard") === 0){
							sels[j].addEventListener("change", function(){
								
								var param = "";
								var chks = document.querySelectorAll('select option:checked');
								if(chks.length){
									for (var k = 0; k < chks.length; k++){
										if(chks[k].parentElement.name.indexOf("Standard") === 0){
											if(param.length) param += "&";
											param += chks[k].parentElement.name + "=" + chks[k].innerHTML.trim();
										}
									}
								}
								
								xhr = new XMLHttpRequest();
								xhr.open("POST",location.href + "?async_cart_button=" + id);
								xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
								xhr.send(param);
				
								xhr.addEventListener("load", function(){
									var res = JSON.parse(xhr.response);
									if(res.price >= 0){
										priceHelper.value = res.price;
									}
								});
							});
						}		
					}
				}
			}
		});
	}
	
			
})();