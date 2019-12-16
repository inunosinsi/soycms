(function(){
	$("#itemorder_price_bulk_change_button").click(function(){
		var percentage = $("#itemorder_price_bulk_change").val();
		if(percentage.length > 0 && !isNaN(percentage)){
			var per = percentage / 100;

			var mode = $('input[name=itemorder_price_bulk_change]:checked').val();
			if(mode == "up"){	//増額
				var p = 1 + per;
			}else{				//減額
				var p = 1 - per;
			}

			$('input[name^="Item"]').each(function(){
				var $ipt = $(this);
				if($ipt.attr("name").indexOf("itemPrice") >= 0){
					var price = $ipt.val() * p;

					var method = $('input[name=itemorder_price_bulk_change_method]:checked').val();
					if(method.length > 0){
						switch(method){
							case "ceil":	//切り捨て
								price = parseInt(Math.ceil(price));
								break;
							case "floor": 	//切り上げ
								price = parseInt(Math.floor(price));
								break;
							case "round":	//四捨五入
								price = parseInt(Math.round(price));
								break;
						}
					}

					$ipt.val(price);
				}
			});
		}
	});
}());
