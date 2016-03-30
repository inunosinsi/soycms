<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class GoogleAnalyticsOnOutput extends SOYShopSiteOnOutputAction{

	const INSERT_INTO_THE_END_OF_HEAD = 2;
	const INSERT_INTO_THE_BEGINNING_OF_BODY = 1;
	const INSERT_INTO_THE_END_OF_BODY = 0;

	/**
	 * @return string
	 */
	function onOutput($html){

		SOY2::import("module.plugins.parts_google_analytics.util.GoogleAnalyticsUtil");
		$config = GoogleAnalyticsUtil::getConfig();
		$code = $config["tracking_code"];
		
		if(strlen($code) == 0){
			return $html;
		}
		
		//アナリティクスタグの挿入設定　カートとマイページは無条件で挿入
		if(defined("SOYSHOP_PAGE_ID")){
			$displayConfig = GoogleAnalyticsUtil::getPageDisplayConfig();
			if(isset($displayConfig[SOYSHOP_PAGE_ID]) && $displayConfig[SOYSHOP_PAGE_ID] == 0){
				return $html;
			}
		}
			

		//XHTMLではないXMLでは出力しない
		if(
			strpos($html, '<?xml version="1.0"') !== false
			&&
			strpos($html, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML') === false
		){
			return $html;
		}

		//RSS, Atomでは出力しない
		if(
			strpos($html, '<rss version="2.0">') !== false
			||
			strpos($html, '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">') !== false
		){
			return $html;
		}
		
		$completePage = false;	//カートの注文完了画面かどうかのフラグ
		
		//現在のページがカートページであるか？をチェック
		if(isset($_SERVER["REDIRECT_URL"]) && strpos(soyshop_get_cart_url(), $_SERVER["REDIRECT_URL"]) !== false){
			$query = "_gaq.push(['_trackPageview', '" . $_SERVER["REDIRECT_URL"] . "/complete/']);";
			if(strpos($html,$query) !== false){
				$completePage = true;	//注文完了画面ならばtrue
			}
		}
		
		//完了ページならば、eコマーストラッキングを挿入する
		if($completePage === true){
			$code = $this->convertTrackingCode($html, $code);
		}

		//</head>の直前
		if($config["insert_to_head"] == self::INSERT_INTO_THE_END_OF_HEAD){
			if(stripos($html,'</head>') !== false){
				$html = str_ireplace('</head>', $code . "\n" . '</head>', $html);
			}elseif(stripos($html, '<body>') !== false){
				$html = str_ireplace('<body>', '<body>' . "\n" . $code, $html);
			}elseif(preg_match('/<body\\s[^>]+>/', $html)){
				$html = preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $code, $html);
			}elseif(stripos($html, '<head>') !== false){
				$html = str_ireplace('<head>', '<head>' . "\n" . $code, $html);
			}elseif(stripos($html, '<html>') !== false){
				$html = str_ireplace('<html>', '<html>' . "\n" . $code, $html);
			}elseif(preg_match('/<html\\s[^>]+>/', $html)){
				$html = preg_replace('/(<html\\s[^>]+>)/', "\$0\n" . $code, $html);
			}

		//<body>の直後
		}elseif($config["insert_to_head"] == self::INSERT_INTO_THE_BEGINNING_OF_BODY){
			if(stripos($html, '<body>') !== false){
				$html = str_ireplace('<body>', '<body>' . "\n" . $code, $html);
			}elseif(preg_match('/<body\\s[^>]+>/', $html)){
				$html = preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $code, $html);
			}elseif(stripos($html, '</head>') !== false){
				$html = str_ireplace('</head>', $code . "\n" . '</head>', $html);
			}elseif(stripos($html, '<head>') !== false){
				$html = str_ireplace('<head>', '<head>' . "\n" . $code, $html);
			}elseif(stripos($html, '<html>') !== false){
				$html = str_ireplace('<html>', '<html>' . "\n" . $code, $html);
			}elseif(preg_match('/<html\\s[^>]+>/', $html)){
				$html = preg_replace('/(<html\\s[^>]+>)/', "\$0\n" . $code, $html);
			}

		//末尾
		}else{
			if(stripos($html, '</body>') !== false){
				$html = str_ireplace('</body>', $code . '</body>', $html);
			}elseif(stripos($html, '</html>') !== false){
				$html = str_ireplace('</html>', $code . '</html>', $html);
			}
		}

		return $html;
	}
	
	function convertTrackingCode($html, $code){
		/**
		 * 注文情報取得で良い方法が思いつかないので、htmlからトラッキングナンバーを取得する
		 */
		preg_match('/\d{1,7}-\d{4}-\d{4}/', $html, $tmp);
		
		//値を取得できたか？
		if(!isset($tmp[0])) return $code;
		
		//トラッキングコードが新しいバージョンかどうか？
		$query = "_gaq.push(['_trackPageview']);";
		if(strpos($code, $query) === false) return $code;
		
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		foreach($tmp as $trackingNumber){
			//市外局番は必ず0から始まるので、0から始まっている番号は除く
			if(strpos($trackingNumber, "0") === 0) continue;
			
			try{
				$order = $orderDao->getByTrackingNumber($trackingNumber);
			}catch(Exception $e){
				$order = new SOYShop_Order();
			}
			if(!is_null($order->getId())) break;
		}
		
		//注文情報を取得できたか？
		if(is_null($order->getId())) return $code;
		
		$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		try{
			$items = $itemOrderDao->getByOrderId($order->getId());
		}catch(Exception $e){
			$items = array();
		}
		
		//注文された商品が一つでもあったか？
		if(count($items) === 0) return $code;
		
		$insertCode = $this->buildInsertCode($order, $items);
		$changeCode = $query . "\n" . $insertCode;
		
		return str_replace($query, "$changeCode", $code);
	}
	
	function buildInsertCode($order, $items){
		$insertOrderCode = $this->buildInsertOrderCode($order);
		$insertItemCode = $this->buildInsertItemCode($items, $order->getTrackingNumber());
		
		return $insertOrderCode . "\n\n" . $insertItemCode . "\n\n" . "  _gaq.push(['_trackTrans']);";
	}
	
	//店舗情報と注文の総額
	function buildInsertOrderCode($order){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		
		$s = "  ";	//表示の調整用
		
		$html = array();
		
		$html[] = $s."_gaq.push(['_addTrans',";
		
		$html[] = $s . $s . "'" . $order->getTrackingNumber() . "',";		//トラッキングナンバー
		$html[] = $s . $s . "'" . $config->getShopName() . "',";			//店舗名
		$html[] = $s . $s . "'" . $order->getPrice() . "',";				//総額
		$html[] = $s . $s . "'0',";									//税額
		
		$deliveryPrice = 0;
		
		$modules = $order->getModuleList();
		foreach($modules as $key => $module){
			if(strpos($key, "delivery_") === 0){
				$deliveryPrice = $module->getPrice();
				break;
			}
		}
		
		$html[] = $s . $s . "'" . $deliveryPrice . "',";					//送料
		
		//郵便番号から住所情報を取得する
		$company = $config->getCompanyInformation();
		if(isset($company["address1"])){
			$zipcode = soyshop_cart_address_validate($company["address1"]);
			$addressSearchLogic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");
			$res = $addressSearchLogic->search($zipcode);
						
			$html[] = $s . $s . "'" . $res["address1"] . "',";			//市区町村
			$html[] = $s . $s . "'" . $res["prefecture"] . "',";			//県
		}else{
			$html[] = $s . $s . "'',";
			$html[] = $s . $s . "'',";
		}
		$html[] = $s . $s . "'日本'";									//国名
		
		$html[] = $s . "]);";
		
		return implode("\n", $html);
	}
	
	//各商品の情報
	function buildInsertItemCode($items, $trackingNumber){
		
		$s = "  ";	//表示の調整用
		
		$html = array();
				
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		
		foreach($items as $orderItem){
			try{
				$item = $itemDao->getById($orderItem->getItemId());
			}catch(Exception $e){
				continue;
			}
			
			$html[] = $s . "_gaq.push(['_addItem',";
			
			$html[] = $s . $s . "'" . $trackingNumber . "',";	//トラッキングナンバー
			$html[] = $s . $s . "'" . $item->getCode() . "',";
			$html[] = $s . $s . "'" . $item->getName() . "',";
			
			try{
				$category = $categoryDao->getById($item->getCategory());
			}catch(Exception $e){
				$category = new SOYShop_Category();
			}
			
			$html[] = $s . $s . "'" . $category->getName() . "',";			//カテゴリー
			$html[] = $s . $s . "'" . $orderItem->getItemPrice() . "',";	//商品単価
			$html[] = $s . $s . "'" . $orderItem->getItemCount() . "'";	//注文個数
			
			$html[] = $s . "]);";
		}
		
		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "parts_google_analytics", "GoogleAnalyticsOnOutput");
?>