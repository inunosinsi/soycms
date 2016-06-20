AsyncCartButton = {
	operationUrl : "",
	isInvalid : false,
	addItem : function(itemId, price){
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
		
		xhr = new XMLHttpRequest();
		xhr.open("GET",url);
		xhr.send();
		
		xhr.addEventListener("load", function(){
			//HTTPステータスが200でカートに商品が入ったことを確認
			if(xhr.status == 200){
				//現在表示されているカートの商品合計を更新
				var countSpan = document.querySelector("#soyshop_cart_item_count");
				if(countSpan){
					countSpan.innerHTML = parseInt(countSpan.innerHTML) + cnt;
				}
				
				//現在表示されているカートの商品小計を更新
				var subTotalSpan = document.querySelector("#soyshop_cart_sub_total");
				if(subTotalSpan){
					var subTotal = subTotalSpan.innerHTML;
					if(subTotal.indexOf(",")){
						subTotal = subTotal.replace(",", "");
					}
					subTotal = parseInt(subTotal) + parseInt(price * cnt);
					subTotalSpan.innerHTML = AsyncCartButton.number_format(subTotal);
				}
				
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
